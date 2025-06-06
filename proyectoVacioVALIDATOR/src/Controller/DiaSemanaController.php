<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiaSemanaController extends AbstractController{
    #[Route('/dia/semana', name: 'app_dia_semana')]
    public function index(): Response
    {
        return $this->render('dia_semana/index.html.twig', [
            'controller_name' => 'DiaSemanaController',
        ]);
    }
}
