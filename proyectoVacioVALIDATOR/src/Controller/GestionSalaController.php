<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Turno;
use App\Entity\TurnoGeneral;
use App\Entity\Mesa;
use App\Entity\MomentoReserva;
use App\Entity\Reserva;
use App\Repository\ReservaRepository;
use App\Repository\TurnoRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\GestionSalaService;
use Symfony\Component\HttpFoundation\Request;

final class GestionSalaController extends AbstractController
{
    private EntityManagerInterface $em;
    private GestionSalaService $gestionSalaService;

    public function __construct(EntityManagerInterface $em, GestionSalaService $gestionSalaService)
    {
        $this->em = $em;
        $this->gestionSalaService = $gestionSalaService;
    }


    #[Route('/Gestion/Sala', name: 'gestion_sala_menu')]
    public function menuGestionSala(): Response
    {
        $fechaHoy = new \DateTime(); // Fecha actual
        return $this->render('gestion_sala/menu.html.twig', [
            'fechaHoy' => $fechaHoy->format('Y-m-d')
        ]);
    }



    #[Route('/gestion/sala/estado', name: 'gestion_sala_estado')]
    public function index(Request $request, TurnoRepository $turnoRepo): Response
    {
        // Fecha de inicio: primer día del mes actual
        $hoy = new \DateTime('first day of this month');

        // Fecha fin: último día del tercer mes (mes actual + 2)
        $fin = (clone $hoy)->modify('+1 month')->modify('last day of this month');

        // Repositorio de Turnos (usamos $this->em en vez de $em)
        $repoTurno = $this->em->getRepository(Turno::class);

        // Obtenemos todos los turnos desde $hoy hasta $fin
        $turnos = $repoTurno->createQueryBuilder('t')
            ->where('t.fecha BETWEEN :inicio AND :fin')
            ->setParameter('inicio', $hoy->format('Y-m-d'))
            ->setParameter('fin', $fin->format('Y-m-d'))
            ->orderBy('t.fecha', 'ASC')
            ->getQuery()
            ->getResult();

        // Organizamos los turnos por fecha
        $turnosPorFecha = [];
        foreach ($turnos as $turno) {
            $fechaStr = $turno->getFecha()->format('Y-m-d');
            $turnosPorFecha[$fechaStr][] = $turno;
        }

        // Construimos el calendario
        $calendarios = [];
        $periodoInicio = clone $hoy;

        for ($i = 0; $i < 2; $i++) {
            $primerDiaMes = (clone $periodoInicio)->modify('first day of this month');
            $ultimoDiaMes = (clone $primerDiaMes)->modify('last day of this month');
            $semanas = [];

            $diaActual = clone $primerDiaMes;
            $primerDiaSemana = (int) $primerDiaMes->format('N');
            $semana = array_fill(0, 7, null);

            for ($j = $primerDiaSemana - 1; $j < 7; $j++) {
                $fechaStr = $diaActual->format('Y-m-d');
                $semana[$j] = [
                    'fecha' => clone $diaActual,
                    'turnos' => $turnosPorFecha[$fechaStr] ?? [],
                ];
                $diaActual->modify('+1 day');
                if ($diaActual > $ultimoDiaMes) break;
            }
            $semanas[] = $semana;

            while ($diaActual <= $ultimoDiaMes) {
                $semana = [];
                for ($d = 0; $d < 7; $d++) {
                    if ($diaActual <= $ultimoDiaMes) {
                        $fechaStr = $diaActual->format('Y-m-d');
                        $semana[] = [
                            'fecha' => clone $diaActual,
                            'turnos' => $turnosPorFecha[$fechaStr] ?? [],
                        ];
                        $diaActual->modify('+1 day');
                    } else {
                        $semana[] = null;
                    }
                }
                $semanas[] = $semana;
            }

            $calendarios[] = [
                'anyoMes' => $primerDiaMes->format('Y-m'),
                'nombreMes' => $primerDiaMes->format('F Y'),
                'semanas' => $semanas,
            ];

            $periodoInicio->modify('+1 month');
        }

        return $this->render('gestion_sala/index.html.twig', [
            'calendarios' => $calendarios,
        ]);
    }


    #[Route('/Gestion/Seleccionar-Turno', name: 'gestion_mostrar_turnos')]
    public function gestionMostrarDia(Request $request, EntityManagerInterface $em): Response
    {
        $fechaString = $request->query->get('fecha');

        if (!$fechaString) {
            throw $this->createNotFoundException('No se ha proporcionado la fecha.');
        }

        $fecha = new \DateTime($fechaString);
        $repoTurno = $em->getRepository(Turno::class);

        $turnos = [
            'COMIDAS' => $repoTurno->findOneBy(['fecha' => $fecha, 'tipo' => 'COMIDAS']),
            'CENAS' => $repoTurno->findOneBy(['fecha' => $fecha, 'tipo' => 'CENAS']),
        ];

        return $this->render('gestion_sala/ver_dia.html.twig', [
            'fecha' => $fecha,
            'turnos' => $turnos,
        ]);
    }



    #[Route('/sala/disponibilidad/{id}', name: 'sala_disponibilidad')]
    public function verDisponibilidad(int $id): Response
    {
        $turno = $this->em->getRepository(Turno::class)->find($id);
        if (!$turno) {
            throw $this->createNotFoundException('Turno no encontrado');
        }

        $momentos = $this->gestionSalaService->getMomentosReservaPorTurno($turno);

        $horaActual = new \DateTime();

        return $this->render('gestion_sala/disponibilidad.html.twig', [
            'turno' => $turno,
            'momentos' => $momentos,
            'horaActual' => $horaActual,
        ]);
    }



    #[Route('/reserva/nueva/{id}', name: 'reserva_opciones')]
    public function seleccionarTipoReserva(int $id, EntityManagerInterface $em): Response
    {
        $momento = $em->getRepository(MomentoReserva::class)->find($id);

        if (!$momento || $momento->getReserva()) {
            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento?->getTurno()->getId()]);
        }

        return $this->render('gestion_sala/reserva_opciones.html.twig', [
            'momento' => $momento
        ]);
    }


    #[Route('/reserva/asignar/{id}', name: 'reserva_asignar')]
    public function asignarMesa(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $momento = $em->getRepository(MomentoReserva::class)->find($id);

        if (!$momento || $momento->getReserva()) {
            $this->addFlash('danger', 'Este momento no está disponible.');
            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento?->getTurno()->getId()]);
        }

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');

            $reserva = new Reserva();
            $reserva->setNombreCliente($nombre ?: 'Cliente Local');
            $reserva->setNumeroComensales(0); // Número de comensales por defecto
            $reserva->setFechaHora(new \DateTime($momento->getFecha()->format('Y-m-d') . ' ' . $momento->getHoraInicio()->format('H:i:s')));
            $reserva->setMesa($momento->getMesa());
            $reserva->setMomentoReserva($momento);
            $reserva->setTelefono('999999999'); // Teléfono por defecto
            $reserva->setEmail('fakeEmail@gmail.com'); // Email por defecto

            $em->persist($reserva);
            $em->flush();

            $this->addFlash('success', 'Mesa asignada correctamente.');
            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento->getTurno()->getId()]);
        }

        return $this->render('gestion_sala/asignar_mesa.html.twig', [
            'momento' => $momento
        ]);
    }

    #[Route('/reserva/crear/{id}', name: 'reserva_crear_formulario')]
    public function crearFormulario(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $momento = $em->getRepository(MomentoReserva::class)->find($id);

        if (!$momento || $momento->getReserva()) {
            $this->addFlash('danger', 'Este momento no está disponible.');
            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento?->getTurno()->getId()]);
        }

        if ($request->isMethod('POST')) {
            $reserva = new Reserva();
            $reserva->setNombreCliente($request->request->get('nombre'));
            $reserva->setTelefono($request->request->get('telefono'));
            $reserva->setEmail($request->request->get('email'));
            $reserva->setNumeroComensales($request->request->get('comensales'));
            $reserva->setFechaHora(new \DateTime($momento->getFecha()->format('Y-m-d') . ' ' . $momento->getHoraInicio()->format('H:i:s')));
            $reserva->setMesa($momento->getMesa());
            $reserva->setMomentoReserva($momento);

            $em->persist($reserva);
            $em->flush();

            $this->addFlash('success', 'Reserva creada correctamente.');
            return $this->redirectToRoute('sala_disponibilidad', ['id' => $momento->getTurno()->getId()]);
        }

        return $this->render('gestion_sala/crear_reserva.html.twig', [
            'momento' => $momento
        ]);
    }



    
    #[Route('/Gestion/Reserva/Eliminar/{id}', name: 'gestion_reserva_eliminar')]
    public function eliminarReserva(Reserva $reserva, EntityManagerInterface $em, Request $request): Response
    {
        // Confirmación por método POST
        if ($request->isMethod('POST')) {
            // Desvincula del MomentoReserva si existe
            $momento = $reserva->getMomentoReserva();
            if ($momento) {
                $momento->setReserva(null);
            }

            $em->remove($reserva);
            $em->flush();

            $this->addFlash('success', 'Reserva eliminada correctamente.');
            return $this->redirectToRoute('gestion_sala_estado'); 
        }

        return $this->render('gestion_sala/confirmar_eliminacion.html.twig', [
            'reserva' => $reserva,
        ]);
    }

    #[Route('/Gestion/Reservas', name: 'gestion_reservas_listado')]
    public function listarReservasFuturas(ReservaRepository $reservaRepository): Response
    {
        $hoy = new \DateTime('today');

        // Obtenemos las reservas futuras ordenadas por fecha
        $reservas = $reservaRepository->createQueryBuilder('r')
            ->where('r.fechaHora >= :hoy')
            ->setParameter('hoy', $hoy)
            ->orderBy('r.fechaHora', 'ASC')
            ->getQuery()
            ->getResult();

        // Agrupar por fecha (formateada como Y-m-d)
        $reservasAgrupadas = [];
        foreach ($reservas as $reserva) {
            $fecha = $reserva->getFechaHora()->format('Y-m-d');
            $reservasAgrupadas[$fecha][] = $reserva;
        }

        return $this->render('gestion_sala/listado_reservas.html.twig', [
            'reservasPorDia' => $reservasAgrupadas,
        ]);
    }


    #[Route('/Gestion/Reserva/Detalle/{id}', name: 'gestion_reserva_detalle')]
    public function detalleReserva(Reserva $reserva, Request $request): Response
    {
        $urlAnterior = $request->headers->get('referer');

        return $this->render('gestion_sala/detalle_reserva.html.twig', [
            'reserva' => $reserva,
            'volverA' => $urlAnterior,
        ]);
    }







}
