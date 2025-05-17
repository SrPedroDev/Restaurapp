<?php

// src/Controller/MovilController.php

namespace App\Controller;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\MenuLogeadoRepository;
use App\Repository\MenuRepository;




// tipos form
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

// clase
use App\Entity\Demandante;
use App\Entity\Bolsa;
use App\Entity\Puesto;
use App\Validator\Dni;
use App\Validator\TelefonoMovil;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Validator\Constraints\Email;

class BolsaController extends AbstractController
{   
  


    #[Route('/bolsas_mostrar', name:'bolsas_mostrar')]
    public function mostrar_bolsas( EntityManagerInterface $entityManager,Request $request )
    {
        $fechaActual = new \DateTime("now"); // Obtener la fecha actual

        $query = $entityManager->createQuery(
            'SELECT b FROM App\Entity\Bolsa b WHERE b.fecha_fin > :fechaActual'
        )->setParameter('fechaActual', $fechaActual);
    
        $bolsasActivas = $query->getResult();
    
        // Si no hay bolsas activas, mostrar mensaje
        if (empty($bolsasActivas)) {
            return $this->render('bolsa/no_bolsa.html.twig');
        }


            return $this->render('bolsa/bolsas.html.twig', array('bolsas' => $bolsasActivas));
    }





    #[Route('/bolsa/{id}', name:'bolsa_cuestionario')]
    public function bolsa(int $id, EntityManagerInterface $entityManager, Request $request )
    {
       

            // Buscar la bolsa por ID
    $bolsa_comprobar = $entityManager->getRepository(Bolsa::class)->find($id);
    
    // Si no se encuentra la bolsa, mostrar error o redirigir
    if (!$bolsa_comprobar) 
    {
        return $this->render('bolsa/bolsa_cerrada.html.twig');
    }
    
    // Obtener la fecha actual
    $fechaActual = new \DateTime();

    // Verificar si la bolsa está cerrada (fecha de cierre pasada)              //comoser require symfony/validator
    if ($fechaActual > $bolsa_comprobar->getFechaFin()) {
        return $this->render('bolsa/bolsa_cerrada.html.twig', ['bolsa' => $bolsa_comprobar]);   //Si la bolsa está cerrada...
    }

        $puestosBolsa =  $entityManager->getRepository(Puesto::class)
        ->findBy(['bolsa' => $id]);

        $bolsa =  $entityManager->getRepository(Bolsa::class)
        ->findOneBy(['id' => $id]);
    

        // Si no hay bolsas activas, mostrar mensaje
        if (empty($puestosBolsa)) {
            return $this->render('bolsa/no_puestos.html.twig', array('bolsa' => $bolsa));
        }




		$form = $this->createFormBuilder();
        $form->add('puesto', EntityType::class, 
        [
            'class' => Puesto::class,
            'choices' => $puestosBolsa, // Solo bolsas activas
            'choice_label' => 'nombre'
        ]);
        $form->add('dni', TextType::class, ['constraints' => [new Dni()]]);                     //FORMA DE VALIDAR
        $form->add('nombre', TextType::class);
        $form->add('apellidos', TextType::class);
        $form->add('direccion', TextType::class);
        $form->add('movil', TextType::class, ['constraints' => [new TelefonoMovil()]]);
        $form->add('email', TextType::class, ['constraints' => [new Email(['message' => 'Por favor ingresa un correo electrónico válido'])]]);
        

        $form->add('Save', SubmitType::class);


		$form = $form->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $data = $form->getData();
            $dni = $data['dni'];
            $bolsa_id = $id;
            $puesto = $data['puesto'];

            $existingDemandante = $entityManager->getRepository(Demandante::class)
                ->findOneBy(['dni' => $dni, 'bolsa' => $bolsa_id, 'puesto' => $puesto]);

            if ($existingDemandante) {
                // Si el demandante ya está registrado en esta bolsa
                return $this->render('bolsa/error.html.twig', array("data"=>$data));
            }


            $demandante = new Demandante();
			
			$demandante->setNombre( $data[ 'nombre']);
            $demandante->setApellidos( $data[ 'apellidos']);
            $demandante->setDni( $data[ 'dni']);
            $demandante->setDireccion( $data[ 'direccion']);
            $demandante->setEmail( $data[ 'email']);
            $demandante->setMovil( $data[ 'movil']);
            $demandante->setBolsa( $bolsa);
            $demandante->setPuesto( $data[ 'puesto']);

			$entityManager->persist($demandante);
            $entityManager->flush(); 
          
             return $this->render('bolsa/registrado.html.twig', array("data"=>$data));
        }
        else
            return $this->render('form.html.twig', array('form' => $form->createView(),'bolsa'=>$bolsa));
    }
     
}
