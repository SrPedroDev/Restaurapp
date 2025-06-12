<?php

namespace App\Repository;

use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Producto>
 */
class ProductoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Producto::class);
    }





    public function findRandomByCategoria(string $nombre, int $limite): array
{
    $productos = $this->createQueryBuilder('p')     //Se toman todos los productos de X categoría , se mezcla el array
        ->join('p.categoria', 'c')                 //Y se devuelven las 6 (o las que se ponga) primeras entradas que se encuentren en el array mezclado (random)
        ->where('c.nombre = :nombre')
        ->setParameter('nombre', $nombre)
        ->getQuery()
        ->getResult();

    shuffle($productos);

    return array_slice($productos, 0, $limite);
}

//    /**
//     * @return Producto[] Returns an array of Producto objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Producto
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
