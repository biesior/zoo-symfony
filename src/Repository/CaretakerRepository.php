<?php

namespace App\Repository;

use App\Entity\Caretaker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Caretaker|null find($id, $lockMode = null, $lockVersion = null)
 * @method Caretaker|null findOneBy(array $criteria, array $orderBy = null)
 * @method Caretaker[]    findAll()
 * @method Caretaker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaretakerRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Caretaker::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Caretaker) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Counts all entities containing given slug, if $id is given, checkin this row will be skipped
     *
     * @param $slug
     * @param $id
     *
     * @return int|mixed|string
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countSlugsWithoutId($slug, $id = null)
    {
        $qb = $this->createQueryBuilder('t');
        if (!is_null($id)) {
            $qb->select('count(t.slug)')
                ->where('t.slug = :slug AND t.id != :id')
                ->setParameter('slug', $slug)
                ->setParameter('id', $id);
        } else {
            $qb->select('count(t.slug)')
                ->where('t.slug = :slug')
                ->setParameter('slug', $slug);
        }
        $query = $qb->getQuery();

        return $query->getSingleScalarResult();

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
