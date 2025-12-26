<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findWithFilters(
        ?string $etat = null,
        ?string $typeService = null,
        ?\DateTime $dateDebut = null,
        ?\DateTime $dateFin = null,
        ?int $clientId = null,
        ?int $burgerId = null,
        ?int $menuId = null,
        ?string $recherche = null
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'cl')
            ->leftJoin('c.lignesCommande', 'lc')
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
            $qb->andWhere('c.dateCommande <= :dateFin')->setParameter('dateFin', $dateFin);
        }

        if ($clientId) {
            $qb->andWhere('c.client = :clientId')->setParameter('clientId', $clientId);
        }

        if ($burgerId) {
            $qb->andWhere('lc.typeProduit = :typeBurger')
               ->andWhere('lc.burger = :burgerId')
               ->setParameter('typeBurger', 'BURGER')
               ->setParameter('burgerId', $burgerId);
        }

        if ($menuId) {
            $qb->andWhere('lc.typeProduit = :typeMenu')
               ->andWhere('lc.menu = :menuId')
               ->setParameter('typeMenu', 'MENU')
               ->setParameter('menuId', $menuId);
        }

        if ($recherche) {
            $qb->andWhere('(c.numero LIKE :recherche OR cl.nom LIKE :recherche OR cl.prenom LIKE :recherche OR cl.telephone LIKE :recherche)')
               ->setParameter('recherche', '%' . $recherche . '%');
        }

        return $qb->distinct()->getQuery()->getResult();
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
            ->andWhere('c.etat = :etat')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('etat', Commande::ETAT_TERMINEE)
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

    public function findLivraisonsAAffecter(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.typeService = :type')
            ->andWhere('c.etat = :etat')
            ->andWhere('c.livreur IS NULL')
            ->setParameter('type', Commande::TYPE_LIVRAISON)
            ->setParameter('etat', Commande::ETAT_PRETE)
            ->orderBy('c.zone', 'ASC')
            ->addOrderBy('c.dateCommande', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLivraisonsEnCours(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.typeService = :type')
            ->andWhere('c.etat IN (:etats)')
            ->andWhere('c.livreur IS NOT NULL')
            ->setParameter('type', Commande::TYPE_LIVRAISON)
            ->setParameter('etats', [Commande::ETAT_PRETE, Commande::ETAT_EN_PREPARATION])
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
