<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


use App\Entity\Menu;
use App\Repository\MenuRepository;
//use App\Entity\MenuLogeado;
//use App\Repository\MenuLogeadoRepository;





 class MenuController extends AbstractController
{
    private $menuRepository;
   // private $menuLogeadoRepository;


    public function  __construct ( MenuRepository $menuRepository,/* MenuLogeadoRepository $menuLogeadoRepository*/){
        $this->menuRepository = $menuRepository;
        //$this->menuLogeadoRepository = $menuLogeadoRepository;
    }


    #[Route(name: 'render_menuLateral')]
    public function menuLateral(): Response
    {
            $menus = $this->menuRepository->findMenu();

        return $this->render('menus/menu_lateral.html.twig', [
            'menus' => $menus
        ]);
    }

        #[Route(name: 'render_menuDesplegable')]
    public function menuDesplegable(): Response
    {
            $menus = $this->menuRepository->findMenu();

        return $this->render('menus/menu_desplegable.html.twig', [
            'menus' => $menus
        ]);
    }






    #[Route('/inicio', name: 'inicio')]
    public function inicio(): Response
    {
       
            return $this->render('base.html.twig');
        
    }



  /*  #[Route('/private/log', name: 'log')]
    public function log(): Response
    {
        $user = $this->getUser();
        
        if ($user) 
        {
            $menus = $this->menuLogeadoRepository->findMenu();
            return $this->render('menu/menu_dinamicamente.html.twig', array("menus"=>$menus));
        } 
        else 
        {
            $menus = $this->menuRepository->findMenu();
            return $this->render('menu/menu_dinamicamente.html.twig', array("menus"=>$menus));
        }
    }

*/




    #[Route('/carta', name: 'carta')]
    public function carta(): Response
    {
        return $this->redirectToRoute('listar_categorias');
    }


    #[Route('/servicios', name: 'servicios')]
    public function servicios(): Response
    {
        return $this->redirectToRoute('listar_servicios');
    }


    


    #[Route('/citas', name: 'citas')]
    public function citas(): Response
    {

        return $this->redirectToRoute('cita_entrada');
    }


    #[Route('/bolsa', name: 'bolsa')]
    public function bolsa(): Response
    {
        return $this->redirectToRoute('bolsas_mostrar');
    }


    #[Route('/private/gestión_medicos', name: 'gestion_medicos')]
    public function gestión_medicos(): Response
    {
        return $this->redirectToRoute('listar_medics');
    }


}
