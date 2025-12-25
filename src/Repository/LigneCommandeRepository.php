<?php

namespace App\Repository;

use App\Entity\LigneCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LigneCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneCommande::class);
    }

    public function findProduitsPlusVendusDuJour(int $limit = 5): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('lc')
            ->select('lc.typeProduit, SUM(lc.quantite) as totalVendu, SUM(lc.sousTotal) as chiffreAffaire')
            ->join('lc.commande', 'c')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat != :annulee')
            ->groupBy('lc.typeProduit')
            ->orderBy('totalVendu', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('annulee', 'ANNULEE')
            ->getQuery()
            ->getResult();
    }
}