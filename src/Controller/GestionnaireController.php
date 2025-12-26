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
        // Statistiques du jour
        $commandesEnCours = $commandeRepository->findCommandesEnCoursDuJour();
        $commandesValidees = $commandeRepository->findCommandesValideesDuJour();
        $commandesAnnulees = $commandeRepository->findCommandesAnnuleesDuJour();
        $recetteJournaliere = $commandeRepository->getRecetteJournaliere();

        return $this->render('gestionnaire/dashboard.html.twig', [
            'commandesEnCours' => $commandesEnCours,
            'commandesValidees' => $commandesValidees,
            'commandesAnnulees' => $commandesAnnulees,
            'recetteJournaliere' => $recetteJournaliere,
            'nbCommandesEnCours' => count($commandesEnCours),
            'nbCommandesValidees' => count($commandesValidees),
            'nbCommandesAnnulees' => count($commandesAnnulees),
        ]);
    }
}