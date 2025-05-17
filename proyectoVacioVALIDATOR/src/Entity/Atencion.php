<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Atencion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $inicio;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $fin = null;

    #[ORM\Column(type: 'smallint')]
    private int $comensales;

    #[ORM\ManyToOne(targetEntity: Mesa::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Mesa $mesa;

    #[ORM\ManyToOne(targetEntity: Reserva::class, inversedBy: 'atenciones')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Reserva $reserva = null;

    #[ORM\OneToMany(mappedBy: 'atencion', targetEntity: Pedido::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $pedidos;

    public function __construct()
    {
        $this->inicio = new \DateTimeImmutable();
        $this->pedidos = new ArrayCollection();
    }

    // Getters y setters...
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getInicio(): \DateTimeInterface
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
    public function setFin(?\DateTimeInterface $fin): static
    {
        $this->fin = $fin;
        return $this;
    }
    public function getComensales(): int
    {
        return $this->comensales;
    }
    public function setComensales(int $comensales): static
    {
        $this->comensales = $comensales;
        return $this;
    }
    public function getMesa(): Mesa
    {
        return $this->mesa;
    }
    public function setMesa(Mesa $mesa): static
    {
        $this->mesa = $mesa;
        return $this;
    }
    public function getReserva(): ?Reserva
    {
        return $this->reserva;
    }
    public function setReserva(?Reserva $reserva): static
    {
        $this->reserva = $reserva;
        return $this;
    }
    public function getPedidos(): Collection
    {
        return $this->pedidos;
    }
    public function addPedido(Pedido $pedido): static
    {
        if (!$this->pedidos->contains($pedido)) {
            $this->pedidos[] = $pedido;
            $pedido->setAtencion($this);
        }
        return $this;
    }
    public function removePedido(Pedido $pedido): static
    {
        if ($this->pedidos->removeElement($pedido)) {
            // set the owning side to null (unless already changed)
            if ($pedido->getAtencion() === $this) {
                $pedido->setAtencion(null);
            }
        }
        return $this;
    }
    
}
