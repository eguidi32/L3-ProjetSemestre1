<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\BurgerRepository;
use App\Repository\MenuRepository;
use App\Repository\ComplementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestionnaire')]
#[IsGranted('ROLE_GESTIONNAIRE')]
class GestionnaireController extends AbstractController
{
    #[Route('', name: 'app_gestionnaire_dashboard')]
    public function dashboard(
        CommandeRepository $commandeRepository,
        BurgerRepository $burgerRepository,
        MenuRepository $menuRepository,
        ComplementRepository $complementRepository
    ): Response {
        // Statistiques du jour
        $commandesEnCours = $commandeRepository->findCommandesEnCoursDuJour();
        $commandesValidees = $commandeRepository->findCommandesValideesDuJour();
        $commandesAnnulees = $commandeRepository->findCommandesAnnuleesDuJour();
        $recetteJournaliere = $commandeRepository->getRecetteJournaliere();

        // Compteurs
        $totalBurgers = count($burgerRepository->findActifs());
        $totalMenus = count($menuRepository->findActifs());
        $totalComplements = count($complementRepository->findActifs());

        return $this->render('gestionnaire/dashboard.html.twig', [
            'commandesEnCours' => $commandesEnCours,
            'commandesValidees' => $commandesValidees,
            'commandesAnnulees' => $commandesAnnulees,
            'recetteJournaliere' => $recetteJournaliere,
            'totalBurgers' => $totalBurgers,
            'totalMenus' => $totalMenus,
            'totalComplements' => $totalComplements,
            'nbCommandesEnCours' => count($commandesEnCours),
            'nbCommandesValidees' => count($commandesValidees),
            'nbCommandesAnnulees' => count($commandesAnnulees),
        ]);
    }
}