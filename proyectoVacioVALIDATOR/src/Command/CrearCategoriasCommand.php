<?php

namespace App\Command;

use App\Entity\Categoria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:crear-categorias',
    description: 'Crea las categorías iniciales del restaurante.',
)]
class CrearCategoriasCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $datos = [
            ['Primeros', 'primero.png'],
            ['Segundos', 'segundo.png'],
            ['Entrantes', 'entrante.png'],
            ['Postres', 'postres.png'],
            ['Bebidas', 'bebidas.png'],
        ];

        foreach ($datos as [$nombre, $icono]) {
            $categoria = new Categoria();
            $categoria->setNombre($nombre);
            $categoria->setIcono($icono);
            $this->em->persist($categoria);
        }

        $this->em->flush();
        $output->writeln('<info>Categorías creadas correctamente.</info>');

        return Command::SUCCESS;
    }
}
