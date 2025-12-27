<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestionnaire')]
#[IsGranted('ROLE_GESTIONNAIRE')]
class GestionnaireController extends AbstractController
{
    #[Route('', name: 'app_gestionnaire_dashboard')]
    public function dashboard(CommandeRepository $commandeRepository): Response
    {
        $now = new \DateTime();
        $today = new \DateTime('today');
        $yesterday = new \DateTime('yesterday');
        $thisMonth = new \DateTime('first day of this month');
        
        // Statistiques du jour (compteurs uniquement, pas de chargement des entités complètes)
        $nbCommandesEnCours = $commandeRepository->countCommandesEnCoursDuJour();
        $nbCommandesValidees = $commandeRepository->countCommandesValideesDuJour();
        $nbCommandesAnnulees = $commandeRepository->countCommandesAnnuleesDuJour();
        $recetteJournaliere = $commandeRepository->getRecetteJournaliere();
        
        // Charger uniquement les 10 dernières commandes en cours avec leurs relations
        $commandesEnCours = $commandeRepository->findRecentCommandesEnCours(10);
        
        // Statistiques comparatives (sans charger les entités)
        $recetteHier = $commandeRepository->getRecetteByDate($yesterday);
        $recetteMois = $commandeRepository->getRecetteByPeriod($thisMonth, $now);
        $nbCommandesHier = $commandeRepository->countCommandesByDate($yesterday);
        $nbCommandesMois = $commandeRepository->countCommandesByPeriod($thisMonth, $now);
        
        // Compter les commandes urgentes (plus de 30 min)
        $nbCommandesUrgentes = $commandeRepository->countCommandesUrgentes();
        
        // Calcul des tendances
        $evolutionRecette = $recetteHier > 0 
            ? (($recetteJournaliere - $recetteHier) / $recetteHier) * 100 
            : 0;
        
        $evolutionCommandes = $nbCommandesHier > 0
            ? ((($nbCommandesValidees + $nbCommandesEnCours) - $nbCommandesHier) / $nbCommandesHier) * 100
            : 0;

        return $this->render('gestionnaire/dashboard.html.twig', [
            'commandesEnCours' => $commandesEnCours,
            'recetteJournaliere' => $recetteJournaliere,
            'recetteMois' => $recetteMois,
            'nbCommandesEnCours' => $nbCommandesEnCours,
            'nbCommandesValidees' => $nbCommandesValidees,
            'nbCommandesAnnulees' => $nbCommandesAnnulees,
            'nbCommandesUrgentes' => $nbCommandesUrgentes,
            'nbCommandesMois' => $nbCommandesMois,
            'evolutionRecette' => round($evolutionRecette, 1),
            'evolutionCommandes' => round($evolutionCommandes, 1),
        ]);
    }
}