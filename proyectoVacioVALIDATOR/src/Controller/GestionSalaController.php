<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Turno;
use App\Entity\TurnoGeneral;
use App\Entity\Mesa;
use App\Entity\MomentoReserva;
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

        return $this->render('gestion_sala/disponibilidad.html.twig', [
            'turno' => $turno,
            'momentos' => $momentos,
        ]);
    }



}
