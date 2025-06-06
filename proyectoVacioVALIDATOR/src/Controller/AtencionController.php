<?php

namespace App\Controller;

use App\Entity\Atencion;
use App\Form\AtencionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atencion')]
final class AtencionController extends AbstractController{
    #[Route(name: 'app_atencion_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $atencions = $entityManager
            ->getRepository(Atencion::class)
            ->findAll();

        return $this->render('atencion/index.html.twig', [
            'atencions' => $atencions,
        ]);
    }

    #[Route('/new', name: 'app_atencion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $atencion = new Atencion();
        $form = $this->createForm(AtencionType::class, $atencion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($atencion);
            $entityManager->flush();

            return $this->redirectToRoute('app_atencion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('atencion/new.html.twig', [
            'atencion' => $atencion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_atencion_show', methods: ['GET'])]
    public function show(Atencion $atencion): Response
    {
        return $this->render('atencion/show.html.twig', [
            'atencion' => $atencion,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_atencion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Atencion $atencion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AtencionType::class, $atencion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_atencion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('atencion/edit.html.twig', [
            'atencion' => $atencion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_atencion_delete', methods: ['POST'])]
    public function delete(Request $request, Atencion $atencion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$atencion->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($atencion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_atencion_index', [], Response::HTTP_SEE_OTHER);
    }
}
