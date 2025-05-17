<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Pedido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fechaHora;

    #[ORM\ManyToOne(targetEntity: Mesa::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Mesa $mesa;

    #[ORM\OneToMany(mappedBy: 'pedido', targetEntity: PedidoItem::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\ManyToOne(targetEntity: Atencion::class, inversedBy: 'pedidos')]
    #[ORM\JoinColumn(nullable: false)]
    private Atencion $atencion;


    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->fechaHora = new \DateTimeImmutable();
    }

    public function calcularTotal(): float
    {
        return array_reduce($this->items->toArray(), fn($acc, $item) => $acc + $item->getSubtotal(), 0.0);
    }

    // Getters y setters...

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getFechaHora(): \DateTimeInterface
    {
        return $this->fechaHora;
    }
    public function setFechaHora(\DateTimeInterface $fechaHora): static
    {
        $this->fechaHora = $fechaHora;
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
    public function getItems(): Collection
    {
        return $this->items;
    }
    public function addItem(PedidoItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setPedido($this);
        }
        return $this;
    }
    public function removeItem(PedidoItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getPedido() === $this) {
                $item->setPedido(null);
            }
        }
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
    
}
