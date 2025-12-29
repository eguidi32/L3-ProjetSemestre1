<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;

class LivraisonService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommandeRepository $commandeRepository
    ) {}

    /**
     * Affecte un livreur à une ou plusieurs commandes
     */
    public function affecterLivreur(array $commandeIds, Utilisateur $livreur): int
    {
        $count = 0;
        
        foreach ($commandeIds as $commandeId) {
            $commande = $this->commandeRepository->find($commandeId);
            
            if ($commande && $commande->isLivraison() && $commande->getLivreur() === null) {
                $commande->setLivreur($livreur);
                $count++;
            }
        }
        
        $this->entityManager->flush();
        
        return $count;
    }

    /**
     * Retire le livreur d'une commande
     */
    public function retirerLivreur(Commande $commande): bool
    {
        if (!$commande->isLivraison()) {
            return false;
        }

        $commande->setLivreur(null);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Termine une livraison
     */
    public function terminerLivraison(Commande $commande): bool
    {
        if (!$commande->isLivraison() || $commande->getLivreur() === null) {
            return false;
        }

        $commande->setEtat(Commande::ETAT_TERMINEE);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Récupère les commandes à affecter regroupées par zone
     */
    public function getCommandesParZone(): array
    {
        $commandesNonAffectees = $this->commandeRepository->findLivraisonsAAffecter();
        
        $commandesParZone = [];
        foreach ($commandesNonAffectees as $commande) {
            $zoneNom = $commande->getZone() ? $commande->getZone()->getNom() : 'Zone non définie';
            $zoneId = $commande->getZone() ? $commande->getZone()->getId() : 0;
            
            if (!isset($commandesParZone[$zoneId])) {
                $commandesParZone[$zoneId] = [
                    'zone' => $commande->getZone(),
                    'nom' => $zoneNom,
                    'commandes' => [],
                ];
            }
            $commandesParZone[$zoneId]['commandes'][] = $commande;
        }
        
        return $commandesParZone;
    }

    /**
     * Calcule le montant total des livraisons pour un livreur
     */
    public function getMontantLivraisonsLivreur(Utilisateur $livreur, ?\DateTime $date = null): float
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('SUM(c.montantTotal)')
            ->from(Commande::class, 'c')
            ->where('c.livreur = :livreur')
            ->andWhere('c.typeService = :type')
            ->andWhere('c.etat = :etat')
            ->setParameter('livreur', $livreur)
            ->setParameter('type', Commande::TYPE_LIVRAISON)
            ->setParameter('etat', Commande::ETAT_TERMINEE);

        if ($date) {
            $debut = (clone $date)->setTime(0, 0, 0);
            $fin = (clone $date)->setTime(23, 59, 59);
            $qb->andWhere('c.dateCommande >= :debut AND c.dateCommande <= :fin')
               ->setParameter('debut', $debut)
               ->setParameter('fin', $fin);
        }

        return (float) ($qb->getQuery()->getSingleScalarResult() ?? 0);
    }

    /**
     * Compte les livraisons d'un livreur
     */
    public function getNombreLivraisonsLivreur(Utilisateur $livreur, ?\DateTime $date = null): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from(Commande::class, 'c')
            ->where('c.livreur = :livreur')
            ->andWhere('c.typeService = :type')
            ->andWhere('c.etat = :etat')
            ->setParameter('livreur', $livreur)
            ->setParameter('type', Commande::TYPE_LIVRAISON)
            ->setParameter('etat', Commande::ETAT_TERMINEE);

        if ($date) {
            $debut = (clone $date)->setTime(0, 0, 0);
            $fin = (clone $date)->setTime(23, 59, 59);
            $qb->andWhere('c.dateCommande >= :debut AND c.dateCommande <= :fin')
               ->setParameter('debut', $debut)
               ->setParameter('fin', $fin);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
