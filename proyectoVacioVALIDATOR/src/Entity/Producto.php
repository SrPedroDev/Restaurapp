<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'tipo', type: 'string')]
#[ORM\DiscriminatorMap([
    'plato' => PlatoPrincipal::class,
    'entrante' => Entrante::class,
    'bebida' => Bebida::class,
    'postre' => Postre::class,
    'menu' => Menu::class,
])]
abstract class Producto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 100)]
    protected string $nombre;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    protected float $precio;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $descripcion = null;

    // Getters y setters comunes

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getNombre(): string
    {
        return $this->nombre;
    }
    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getPrecio(): float
    {
        return $this->precio;
    }
    public function setPrecio(float $precio): static
    {
        $this->precio = $precio;
        return $this;
    }
    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }
    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;
        return $this;
    }
    public function __toString(): string
    {
        return $this->nombre;
    }
    
}
