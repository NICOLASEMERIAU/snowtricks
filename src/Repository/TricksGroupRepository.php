<?php

namespace App\Repository;

use App\Entity\TricksGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TricksGroup>
 *
 * @method TricksGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method TricksGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method TricksGroup[]    findAll()
 * @method TricksGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TricksGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TricksGroup::class);
    }

//    /**
//     * @return TricksGroup[] Returns an array of TricksGroup objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TricksGroup
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
