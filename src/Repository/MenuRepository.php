<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function findActifs(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.archive = false')
            ->orderBy('m.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}