<?php

namespace App\Controller;

use App\Entity\Mesa;
use App\Form\MesaType;
use App\Repository\MesaRepository;
use App\Service\MesaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/Gestion/Mesas', name: 'gestion_mesas_')]
class MesaController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(MesaRepository $mesaRepository): Response
    {
        $mesas = $mesaRepository->findAll();

        return $this->render('gestion_mesas/index.html.twig', [
            'mesas' => $mesas,
        ]);
    }

    #[Route('/nueva', name: 'nueva')]
    public function nueva(Request $request, EntityManagerInterface $em, MesaService $mesaService): Response
    {
        $mesa = new Mesa();
        $form = $this->createForm(MesaType::class, $mesa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $em->persist($mesa);
            $em->flush();
           
            //Llamamos al servicio para generar momentos reserva
            $mesaService->regenerarMomentosParaMesa($mesa);

            $this->addFlash('success', 'Mesa creada correctamente.');
            return $this->redirectToRoute('gestion_mesas_index');
        }

        return $this->render('gestion_mesas/formulario.html.twig', [
            'form' => $form->createView(),
            'editar' => false,
        ]);
    }

    
    #[Route('/editar/{id}', name: 'editar')]
    public function editar(Mesa $mesa, Request $request, MesaService $mesaService): Response
    {
        // Guardamos el valor original ANTES de handleRequest
        $originalOperativa = $mesa->isOperativa();
        $originalCapacidad = $mesa->getCapacidad();
        $originalIdentificador = $mesa->getIdentificador();

        $form = $this->createForm(MesaType::class, $mesa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cambios = [];

            if ($originalOperativa !== $mesa->isOperativa()) {
                $cambios['operativa'] = $mesa->isOperativa();
            }
            if ($originalCapacidad !== $mesa->getCapacidad()) {
                $cambios['capacidad'] = $mesa->getCapacidad();
            }
            if ($originalIdentificador !== $mesa->getIdentificador()) {
                $cambios['identificador'] = $mesa->getIdentificador();
            }

            // PASAMOS el valor original para que el servicio compare correctamente
            $mesaService->editarMesa($mesa, $cambios, $originalOperativa);

            $this->addFlash('success', 'Mesa actualizada correctamente.');
            return $this->redirectToRoute('gestion_mesas_index');
        }

        return $this->render('gestion_mesas/formulario.html.twig', [
            'form' => $form->createView(),
            'editar' => true,
        ]);
    }




    #[Route('/eliminar/{id}', name: 'eliminar')]
    public function eliminar(Mesa $mesa,  MesaService $mesaService): Response
    {
       $mesaService->eliminarMesa($mesa);

        $this->addFlash('success', 'Mesa eliminada correctamente con sus reservas futuras.');
        return $this->redirectToRoute('gestion_mesas_index');
    }
}
