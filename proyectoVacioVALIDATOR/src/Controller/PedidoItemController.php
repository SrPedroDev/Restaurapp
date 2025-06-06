<?php

namespace App\Controller;

use App\Entity\PedidoItem;
use App\Form\PedidoItemType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pedido/item')]
final class PedidoItemController extends AbstractController{
    #[Route(name: 'app_pedido_item_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $pedidoItems = $entityManager
            ->getRepository(PedidoItem::class)
            ->findAll();

        return $this->render('pedido_item/index.html.twig', [
            'pedido_items' => $pedidoItems,
        ]);
    }

    #[Route('/new', name: 'app_pedido_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pedidoItem = new PedidoItem();
        $form = $this->createForm(PedidoItemType::class, $pedidoItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pedidoItem);
            $entityManager->flush();

            return $this->redirectToRoute('app_pedido_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pedido_item/new.html.twig', [
            'pedido_item' => $pedidoItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pedido_item_show', methods: ['GET'])]
    public function show(PedidoItem $pedidoItem): Response
    {
        return $this->render('pedido_item/show.html.twig', [
            'pedido_item' => $pedidoItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pedido_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PedidoItem $pedidoItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PedidoItemType::class, $pedidoItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_pedido_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pedido_item/edit.html.twig', [
            'pedido_item' => $pedidoItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pedido_item_delete', methods: ['POST'])]
    public function delete(Request $request, PedidoItem $pedidoItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pedidoItem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pedidoItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pedido_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
