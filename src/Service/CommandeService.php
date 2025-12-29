<?php

namespace App\Service;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommandeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommandeRepository $commandeRepository
    ) {}

    /**
     * Change l'état d'une commande avec validation du workflow
     */
    public function changerEtat(Commande $commande, string $nouvelEtat): bool
    {
        $transitionsAutorisees = $this->getTransitionsAutorisees($commande->getEtat());
        
        if (!in_array($nouvelEtat, $transitionsAutorisees)) {
            return false;
        }

        $commande->setEtat($nouvelEtat);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Annule une commande si c'est possible
     */
    public function annulerCommande(Commande $commande): bool
    {
        if ($commande->getEtat() === Commande::ETAT_TERMINEE) {
            return false;
        }

        if ($commande->getEtat() === Commande::ETAT_ANNULEE) {
            return false;
        }

        $commande->setEtat(Commande::ETAT_ANNULEE);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Passe une commande en préparation
     */
    public function mettreEnPreparation(Commande $commande): bool
    {
        if ($commande->getEtat() !== Commande::ETAT_EN_ATTENTE) {
            return false;
        }

        $commande->setEtat(Commande::ETAT_EN_PREPARATION);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Marque une commande comme prête
     */
    public function marquerPrete(Commande $commande): bool
    {
        if ($commande->getEtat() !== Commande::ETAT_EN_PREPARATION) {
            return false;
        }

        $commande->setEtat(Commande::ETAT_PRETE);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Termine une commande
     */
    public function terminerCommande(Commande $commande): bool
    {
        if ($commande->getEtat() !== Commande::ETAT_PRETE) {
            return false;
        }

        $commande->setEtat(Commande::ETAT_TERMINEE);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Retourne les transitions autorisées pour un état donné
     */
    public function getTransitionsAutorisees(string $etatActuel): array
    {
        return match($etatActuel) {
            Commande::ETAT_EN_ATTENTE => [Commande::ETAT_EN_PREPARATION],
            Commande::ETAT_EN_PREPARATION => [Commande::ETAT_PRETE],
            Commande::ETAT_PRETE => [Commande::ETAT_TERMINEE],
            Commande::ETAT_TERMINEE => [],
            Commande::ETAT_ANNULEE => [],
            default => [],
        };
    }

    /**
     * Génère un export CSV des commandes
     */
    public function exporterCommandesCsv(array $commandes): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->setCallback(function () use ($commandes) {
            $handle = fopen('php://output', 'w+');
            
            // En-têtes UTF-8 BOM pour Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes du CSV
            fputcsv($handle, [
                'Numéro',
                'Date',
                'Heure',
                'Client',
                'Téléphone',
                'Type Service',
                'État',
                'Montant',
                'Payé',
                'Mode Paiement',
                'Zone',
                'Livreur'
            ], ';');
            
            // Données
            foreach ($commandes as $commande) {
                fputcsv($handle, [
                    $commande->getNumero(),
                    $commande->getDateCommande()->format('d/m/Y'),
                    $commande->getDateCommande()->format('H:i'),
                    $commande->getClient()->getNomComplet(),
                    $commande->getClient()->getTelephone(),
                    $commande->getTypeServiceLabel(),
                    $commande->getEtatLabel(),
                    $commande->getMontantTotal(),
                    $commande->isPaye() ? 'Oui' : 'Non',
                    $commande->getPaiement() ? $commande->getPaiement()->getModePaiementLabel() : '',
                    $commande->getZone() ? $commande->getZone()->getNom() : '',
                    $commande->getLivreur() ? $commande->getLivreur()->getNomComplet() : ''
                ], ';');
            }
            
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="commandes_' . date('Y-m-d_H-i') . '.csv"');

        return $response;
    }

    /**
     * Récupère les statistiques d'une commande
     */
    public function getCommandeStats(Commande $commande): array
    {
        $nbArticles = 0;
        $nbComplements = 0;
        
        foreach ($commande->getLignesCommande() as $ligne) {
            $nbArticles += $ligne->getQuantite();
            $nbComplements += count($ligne->getLigneComplements());
        }
        
        return [
            'nbArticles' => $nbArticles,
            'nbComplements' => $nbComplements,
            'nbLignes' => count($commande->getLignesCommande()),
        ];
    }
}
