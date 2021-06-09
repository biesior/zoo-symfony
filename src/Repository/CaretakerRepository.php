<?php

namespace App\Repository;

use App\Entity\Caretaker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Caretaker|null find($id, $lockMode = null, $lockVersion = null)
 * @method Caretaker|null findOneBy(array $criteria, array $orderBy = null)
 * @method Caretaker[]    findAll()
 * @method Caretaker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaretakerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Caretaker::class);
    }

    // /**
    //  * @return Caretaker[] Returns an array of Caretaker objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Caretaker
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
