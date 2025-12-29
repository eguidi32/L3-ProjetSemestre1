<?php

namespace App\Repository;

use App\Entity\Complement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ComplementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Complement::class);
    }

    public function findActifs(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.archive = false')
            ->orderBy('c.typeComplement', 'ASC')
            ->addOrderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBoissons(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.archive = false')
            ->andWhere('c.typeComplement = :type')
            ->setParameter('type', 'BOISSON')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFrites(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.archive = false')
            ->andWhere('c.typeComplement = :type')
            ->setParameter('type', 'FRITES')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}