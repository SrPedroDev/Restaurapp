<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PedidoItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Pedido::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private Pedido $pedido;

    #[ORM\ManyToOne(targetEntity: Producto::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Producto $producto;

    #[ORM\Column(type: 'smallint')]
    private int $cantidad;

    public function getSubtotal(): float
    {
        return $this->producto->getPrecio() * $this->cantidad;
    }

    // Getters y setters...
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getPedido(): Pedido
    {
        return $this->pedido;
    }
    public function setPedido(Pedido $pedido): static
    {
        $this->pedido = $pedido;
        return $this;
    }
    public function getProducto(): Producto
    {
        return $this->producto;
    }
    public function setProducto(Producto $producto): static
    {
        $this->producto = $producto;
        return $this;
    }
    public function getCantidad(): int
    {
        return $this->cantidad;
    }
    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;
        return $this;
    }
    
}
