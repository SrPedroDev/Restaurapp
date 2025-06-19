<?php

namespace App\Command;

use App\Entity\Menu;
use App\Entity\DiaSemana;
use App\Entity\Categoria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:crear-datos-iniciales',description: 'Inserta menús, días de la semana y categorías iniciales.',)]
class CrearDatosInicialesCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // --- MENÚS ---
        $menus = [
            ['id' => 3, 'titulo' => 'Gestión Carta', 'enlace' => 'listar_categorias', 'orden' => 3],
            ['id' => 4, 'titulo' => 'Gestion Turnos', 'enlace' => 'inicio_turno', 'orden' => 2],
            ['id' => 5, 'titulo' => 'Gestion Sala', 'enlace' => 'gestion_sala_menu', 'orden' => 1],
            ['id' => 6, 'titulo' => 'Gestión Mesas', 'enlace' => 'gestion_mesas_index', 'orden' => 4],
            ['id' => 7, 'titulo' => 'Registros de reservas', 'enlace' => 'historico_reservas_calendario', 'orden' => 5],
        ];

        foreach ($menus as $data) {
            if (!$this->em->getRepository(Menu::class)->find($data['id'])) {
                $menu = new Menu();
                $menu->setId($data['id']);
                $menu->setTitulo($data['titulo']);
                $menu->setEnlace($data['enlace']);
                $menu->setOrden($data['orden']);
                $this->em->persist($menu);
            }
        }

        // --- DÍAS DE LA SEMANA ---
        $dias = [
            ['id' => 1, 'nombre' => 'Lunes', 'numero' => 1],
            ['id' => 2, 'nombre' => 'Martes', 'numero' => 2],
            ['id' => 3, 'nombre' => 'Miercoles', 'numero' => 3],
            ['id' => 4, 'nombre' => 'Jueves', 'numero' => 4],
            ['id' => 5, 'nombre' => 'Viernes', 'numero' => 5],
            ['id' => 6, 'nombre' => 'Sabado', 'numero' => 6],
            ['id' => 7, 'nombre' => 'Domingo', 'numero' => 7],
        ];

        foreach ($dias as $data) {
            if (!$this->em->getRepository(DiaSemana::class)->find($data['id'])) {
                $dia = new DiaSemana();
                $dia->setId($data['id']);
                $dia->setNombre($data['nombre']);
                $dia->setNumero($data['numero']);
                $this->em->persist($dia);
            }
        }

        // --- CATEGORÍAS ---
        $categorias = [
            ['id' => 1, 'nombre' => 'Primeros', 'icono' => 'primero.png'],
            ['id' => 2, 'nombre' => 'Segundos', 'icono' => 'segundo.png'],
            ['id' => 3, 'nombre' => 'Entrantes', 'icono' => 'entrante.png'],
            ['id' => 4, 'nombre' => 'Postres', 'icono' => 'postres.png'],
            ['id' => 5, 'nombre' => 'Bebidas', 'icono' => 'bebidas.png'],
        ];

        foreach ($categorias as $data) {
            if (!$this->em->getRepository(Categoria::class)->find($data['id'])) {
                $categoria = new Categoria();
                $categoria->setId($data['id']);
                $categoria->setNombre($data['nombre']);
                $categoria->setIcono($data['icono']);
                $this->em->persist($categoria);
            }
        }

        $this->em->flush();

        $output->writeln('<info>Datos iniciales creados correctamente.</info>');

        return Command::SUCCESS;
    }
}
