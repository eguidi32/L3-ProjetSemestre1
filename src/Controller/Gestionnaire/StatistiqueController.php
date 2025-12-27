<?php

namespace App\Controller\Gestionnaire;

use App\Service\StatistiqueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestionnaire/statistiques')]
#[IsGranted('ROLE_GESTIONNAIRE')]
class StatistiqueController extends AbstractController
{
    public function __construct(
        private StatistiqueService $statistiqueService
    ) {}

    #[Route('', name: 'app_gestionnaire_statistiques')]
    public function index(Request $request): Response
    {
        // Date par défaut : aujourd'hui
        $dateStr = $request->query->get('date', date('Y-m-d'));
        
        try {
            $date = new \DateTime($dateStr);
        } catch (\Exception $e) {
            $date = new \DateTime();
            $this->addFlash('warning', 'Date invalide, affichage de la date du jour.');
        }
        
        $dateFin = (clone $date)->setTime(23, 59, 59);
        $dateDebut = (clone $date)->setTime(0, 0, 0);

        // Utilisation du service pour récupérer les statistiques
        $stats = $this->statistiqueService->getStatistiquesJour($dateDebut, $dateFin);
        $produitsVendus = $this->statistiqueService->getProduitsPlusVendus($dateDebut, $dateFin);
        $commandesParType = $this->statistiqueService->getCommandesParType($dateDebut, $dateFin);
        $evolution = $this->statistiqueService->getEvolutionSemaine($date);

        return $this->render('gestionnaire/statistique/index.html.twig', [
            'date' => $date,
            'stats' => $stats,
            'produitsVendus' => $produitsVendus,
            'commandesParType' => $commandesParType,
            'evolution' => $evolution,
        ]);
    }
}