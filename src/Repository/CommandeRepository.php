<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findCommandesDuJour(): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('c')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCommandesEnCoursDuJour(): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('c')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat IN (:etats)')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('etats', [Commande::ETAT_EN_ATTENTE, Commande::ETAT_EN_PREPARATION, Commande::ETAT_PRETE])
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCommandesValideesDuJour(): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('c')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat IN (:etats)')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('etats', [Commande::ETAT_PRETE, Commande::ETAT_TERMINEE])
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCommandesAnnuleesDuJour(): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('c')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat = :etat')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('etat', Commande::ETAT_ANNULEE)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getRecetteJournaliere(): float
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.montantTotal) as total')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat != :annulee')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('annulee', Commande::ETAT_ANNULEE)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function findCommandesLivraisonParZone(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.typeService = :type')
            ->andWhere('c.etat IN (:etats)')
            ->andWhere('c.livreur IS NULL')
            ->setParameter('type', Commande::TYPE_LIVRAISON)
            ->setParameter('etats', [Commande::ETAT_PRETE])
            ->orderBy('c.zone', 'ASC')
            ->addOrderBy('c.dateCommande', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(?string $etat, ?string $typeService, ?\DateTime $dateDebut, ?\DateTime $dateFin, ?int $clientId): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.dateCommande', 'DESC');

        if ($etat) {
            $qb->andWhere('c.etat = :etat')->setParameter('etat', $etat);
        }

        if ($typeService) {
            $qb->andWhere('c.typeService = :typeService')->setParameter('typeService', $typeService);
        }

        if ($dateDebut) {
            $qb->andWhere('c.dateCommande >= :dateDebut')->setParameter('dateDebut', $dateDebut);
        }

        if ($dateFin) {
            $dateFin->setTime(23, 59, 59);
            $qb->andWhere('c.dateCommande <= :dateFin')->setParameter('dateFin', $dateFin);
        }

        if ($clientId) {
            $qb->andWhere('c.client = :clientId')->setParameter('clientId', $clientId);
        }

        return $qb->getQuery()->getResult();
    }
}