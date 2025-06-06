<?php

namespace App\Entity;

use App\Repository\MomentoReservaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MomentoReservaRepository::class)]
class MomentoReserva
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'momentoReserva')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Mesa $mesa = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $inicio = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fin = null;

    #[ORM\Column]
    private ?bool $disponibilidad = true;

    #[ORM\OneToOne(inversedBy: 'momentoReserva', cascade: ['persist', 'remove'])]
    private ?Reserva $reserva = null;

    #[ORM\ManyToOne(inversedBy: 'momentoReservas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Turno $turno = null;

    public function __construct()
    {
        $this->disponibilidad = true;
    }
    



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMesa(): ?Mesa
    {
        return $this->mesa;
    }

    public function setMesa(?Mesa $mesa): static
    {
        $this->mesa = $mesa;

        return $this;
    }

    public function getInicio(): ?\DateTimeInterface
    {
        return $this->inicio;
    }

    public function setInicio(\DateTimeInterface $inicio): static
    {
        $this->inicio = $inicio;

        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(\DateTimeInterface $fin): static
    {
        $this->fin = $fin;

        return $this;
    }

    public function isDisponibilidad(): ?bool
    {
        return $this->disponibilidad;
    }

    public function setDisponibilidad(bool $disponibilidad): static
    {
        $this->disponibilidad = $disponibilidad;

        return $this;
    }

    public function getReserva(): ?Reserva
    {
        return $this->reserva;
    }

    public function setReserva(?Reserva $reserva): static
    {
        $this->reserva = $reserva;
        if ($reserva) {
            $this->disponibilidad = false;
        }
        return $this;
    }

    public function getTurno(): ?turno
    {
        return $this->turno;
    }

    public function setTurno(?turno $turno): static
    {
        $this->turno = $turno;

        return $this;
    }
}