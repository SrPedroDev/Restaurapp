<?php

namespace App\Controller;

use App\Entity\Mesa;
use App\Form\MesaType;
use App\Repository\MesaRepository;
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
    public function nueva(Request $request, EntityManagerInterface $em): Response
    {
        $mesa = new Mesa();
        $form = $this->createForm(MesaType::class, $mesa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($mesa);
            $em->flush();
            $this->addFlash('success', 'Mesa creada correctamente.');
            return $this->redirectToRoute('gestion_mesas_index');
        }

        return $this->render('gestion_mesas/formulario.html.twig', [
            'form' => $form->createView(),
            'editar' => false,
        ]);
    }

    #[Route('/editar/{id}', name: 'editar')]
    public function editar(Mesa $mesa, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MesaType::class, $mesa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Mesa actualizada correctamente.');
            return $this->redirectToRoute('gestion_mesas_index');
        }

        return $this->render('gestion_mesas/formulario.html.twig', [
            'form' => $form->createView(),
            'editar' => true,
        ]);
    }

    #[Route('/eliminar/{id}', name: 'eliminar')]
    public function eliminar(Mesa $mesa, EntityManagerInterface $em): Response
    {
        if (count($mesa->getReservas()) > 0) {
            $this->addFlash('danger', 'No se puede eliminar la mesa porque tiene reservas asociadas.');
            return $this->redirectToRoute('gestion_mesas_index');
        }

        $em->remove($mesa);
        $em->flush();

        $this->addFlash('success', 'Mesa eliminada correctamente.');
        return $this->redirectToRoute('gestion_mesas_index');
    }
}
