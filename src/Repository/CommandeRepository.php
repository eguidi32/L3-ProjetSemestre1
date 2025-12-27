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

    /**
     * Recherche les commandes avec filtres et pagination
     */
    public function findWithFilters(
        ?string $etat = null,
        ?string $typeService = null,
        ?\DateTime $dateDebut = null,
        ?\DateTime $dateFin = null,
        ?int $clientId = null,
        ?int $burgerId = null,
        ?int $menuId = null,
        ?string $recherche = null,
        int $page = 1,
        int $limit = 20
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

        // Pagination
        $offset = ($page - 1) * $limit;
        $qb->distinct()
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte le nombre total de commandes avec filtres (pour la pagination)
     */
    public function countWithFilters(
        ?string $etat = null,
        ?string $typeService = null,
        ?\DateTime $dateDebut = null,
        ?\DateTime $dateFin = null,
        ?int $clientId = null,
        ?int $burgerId = null,
        ?int $menuId = null,
        ?string $recherche = null
    ): int {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->leftJoin('c.client', 'cl')
            ->leftJoin('c.lignesCommande', 'lc');

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

        return (int) $qb->getQuery()->getSingleScalarResult();
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

    public function countCommandesEnCoursDuJour(): int
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat IN (:etats)')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('etats', [Commande::ETAT_EN_ATTENTE, Commande::ETAT_EN_PREPARATION, Commande::ETAT_PRETE])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCommandesValideesDuJour(): int
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat = :etat')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('etat', Commande::ETAT_TERMINEE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCommandesAnnuleesDuJour(): int
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat = :etat')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('etat', Commande::ETAT_ANNULEE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecentCommandesEnCours(int $limit = 10): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'client')
            ->leftJoin('c.paiement', 'p')
            ->addSelect('client', 'p')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat IN (:etats)')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('etats', [Commande::ETAT_EN_ATTENTE, Commande::ETAT_EN_PREPARATION, Commande::ETAT_PRETE])
            ->orderBy('c.dateCommande', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countCommandesUrgentes(): int
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');
        $thirtyMinutesAgo = new \DateTime('-30 minutes');

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.dateCommande <= :thirtyMinutesAgo')
            ->andWhere('c.etat IN (:etats)')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('thirtyMinutesAgo', $thirtyMinutesAgo)
            ->setParameter('etats', [Commande::ETAT_EN_ATTENTE, Commande::ETAT_EN_PREPARATION])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCommandesByDate(\DateTime $date): int
    {
        $startOfDay = (clone $date)->setTime(0, 0, 0);
        $endOfDay = (clone $date)->setTime(23, 59, 59);

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCommande >= :start')
            ->andWhere('c.dateCommande <= :end')
            ->andWhere('c.etat != :annulee')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('annulee', Commande::ETAT_ANNULEE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCommandesByPeriod(\DateTime $start, \DateTime $end): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCommande >= :start')
            ->andWhere('c.dateCommande <= :end')
            ->andWhere('c.etat != :annulee')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('annulee', Commande::ETAT_ANNULEE)
            ->getQuery()
            ->getSingleScalarResult();
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
            ->leftJoin('c.paiement', 'p')
            ->where('c.dateCommande >= :today')
            ->andWhere('c.dateCommande < :tomorrow')
            ->andWhere('c.etat != :annulee')
            ->andWhere('p.id IS NOT NULL')
            ->andWhere('p.statut = :statut')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('annulee', Commande::ETAT_ANNULEE)
            ->setParameter('statut', 'VALIDE')
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

    public function getRecetteByDate(\DateTime $date): float
    {
        $startOfDay = (clone $date)->setTime(0, 0, 0);
        $endOfDay = (clone $date)->setTime(23, 59, 59);

        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.montantTotal) as total')
            ->leftJoin('c.paiement', 'p')
            ->where('c.dateCommande >= :start')
            ->andWhere('c.dateCommande <= :end')
            ->andWhere('c.etat != :annulee')
            ->andWhere('p.id IS NOT NULL')
            ->andWhere('p.statut = :statut')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('annulee', Commande::ETAT_ANNULEE)
            ->setParameter('statut', 'VALIDE')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function getRecetteByPeriod(\DateTime $start, \DateTime $end): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.montantTotal) as total')
            ->leftJoin('c.paiement', 'p')
            ->where('c.dateCommande >= :start')
            ->andWhere('c.dateCommande <= :end')
            ->andWhere('c.etat != :annulee')
            ->andWhere('p.id IS NOT NULL')
            ->andWhere('p.statut = :statut')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('annulee', Commande::ETAT_ANNULEE)
            ->setParameter('statut', 'VALIDE')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function findCommandesByDate(\DateTime $date): array
    {
        $startOfDay = (clone $date)->setTime(0, 0, 0);
        $endOfDay = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            ->where('c.dateCommande >= :start')
            ->andWhere('c.dateCommande <= :end')
            ->andWhere('c.etat != :annulee')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('annulee', Commande::ETAT_ANNULEE)
            ->setMaxResults(100) // Limite pour éviter les timeout
            ->getQuery()
            ->getResult();
    }

    public function findCommandesByPeriod(\DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.dateCommande >= :start')
            ->andWhere('c.dateCommande <= :end')
            ->andWhere('c.etat != :annulee')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('annulee', Commande::ETAT_ANNULEE)
            ->orderBy('c.dateCommande', 'DESC')
            ->setMaxResults(100) // Limite pour éviter les timeout
            ->getQuery()
            ->getResult();
    }
}
