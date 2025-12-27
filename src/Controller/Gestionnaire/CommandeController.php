<?php

namespace App\Controller\Gestionnaire;

use App\Entity\Commande;
use App\Repository\BurgerRepository;
use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use App\Repository\UtilisateurRepository;
use App\Service\CommandeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestionnaire/commandes')]
#[IsGranted('ROLE_GESTIONNAIRE')]
class CommandeController extends AbstractController
{
    private const ITEMS_PER_PAGE = 20;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_gestionnaire_commandes')]
    public function index(
        Request $request,
        CommandeRepository $commandeRepository,
        UtilisateurRepository $utilisateurRepository,
        BurgerRepository $burgerRepository,
        MenuRepository $menuRepository
    ): Response {
        // Récupérer les filtres
        $etat = $request->query->get('etat');
        $typeService = $request->query->get('type_service');
        $dateDebut = $request->query->get('date_debut') ? new \DateTime($request->query->get('date_debut')) : null;
        $dateFin = $request->query->get('date_fin') ? new \DateTime($request->query->get('date_fin') . ' 23:59:59') : null;
        $clientId = $request->query->get('client') ? (int) $request->query->get('client') : null;
        $burgerId = $request->query->get('burger') ? (int) $request->query->get('burger') : null;
        $menuId = $request->query->get('menu') ? (int) $request->query->get('menu') : null;
        $recherche = $request->query->get('recherche');
        
        // Pagination
        $page = max(1, $request->query->getInt('page', 1));

        // Appliquer les filtres avec pagination
        $commandes = $commandeRepository->findWithFilters(
            $etat, 
            $typeService, 
            $dateDebut, 
            $dateFin, 
            $clientId, 
            $burgerId, 
            $menuId,
            $recherche,
            $page,
            self::ITEMS_PER_PAGE
        );

        // Compter le total pour la pagination
        $totalCommandes = $commandeRepository->countWithFilters(
            $etat,
            $typeService,
            $dateDebut,
            $dateFin,
            $clientId,
            $burgerId,
            $menuId,
            $recherche
        );

        $totalPages = (int) ceil($totalCommandes / self::ITEMS_PER_PAGE);

        // Récupérer les listes pour les filtres
        $clients = $utilisateurRepository->findClients();
        $burgers = $burgerRepository->findActifs();
        $menus = $menuRepository->findActifs();

        return $this->render('gestionnaire/commande/index.html.twig', [
            'commandes' => $commandes,
            'clients' => $clients,
            'burgers' => $burgers,
            'menus' => $menus,
            'filtres' => [
                'etat' => $etat,
                'type_service' => $typeService,
                'date_debut' => $request->query->get('date_debut'),
                'date_fin' => $request->query->get('date_fin'),
                'client' => $clientId,
                'burger' => $burgerId,
                'menu' => $menuId,
                'recherche' => $recherche,
            ],
            'pagination' => [
                'page' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalCommandes,
                'itemsPerPage' => self::ITEMS_PER_PAGE,
            ],
        ]);
    }

    #[Route('/{id}', name: 'app_gestionnaire_commande_show', requirements: ['id' => '\d+'])]
    public function show(int $id, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('gestionnaire/commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/changer-etat', name: 'app_gestionnaire_commande_changer_etat', methods: ['POST'])]
    public function changerEtat(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository
    ): Response {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('changer_etat_' . $id, $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        $nouvelEtat = $request->request->get('etat');
        $etatsValides = [
            Commande::ETAT_EN_ATTENTE,
            Commande::ETAT_EN_PREPARATION,
            Commande::ETAT_PRETE,
            Commande::ETAT_TERMINEE,
        ];

        if (!in_array($nouvelEtat, $etatsValides)) {
            $this->addFlash('danger', 'État invalide.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        // Vérifier le workflow des états
        $etatActuel = $commande->getEtat();
        $transitionsAutorisees = $this->getTransitionsAutorisees($etatActuel);

        if (!in_array($nouvelEtat, $transitionsAutorisees)) {
            $this->addFlash('danger', 'Transition d\'état non autorisée.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        $commande->setEtat($nouvelEtat);
        $this->entityManager->flush();

        $this->addFlash('success', 'État de la commande mis à jour : ' . $this->getEtatLabel($nouvelEtat));

        return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
    }

    #[Route('/{id}/annuler', name: 'app_gestionnaire_commande_annuler', methods: ['POST'])]
    public function annuler(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository
    ): Response {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('annuler_' . $id, $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        // Vérifier que la commande n'est pas déjà terminée ou annulée
        if ($commande->getEtat() === Commande::ETAT_TERMINEE) {
            $this->addFlash('danger', 'Impossible d\'annuler une commande terminée.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        if ($commande->getEtat() === Commande::ETAT_ANNULEE) {
            $this->addFlash('warning', 'Cette commande est déjà annulée.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        $commande->setEtat(Commande::ETAT_ANNULEE);
        $this->entityManager->flush();

        $this->addFlash('success', 'Commande #' . $commande->getNumero() . ' annulée avec succès.');

        return $this->redirectToRoute('app_gestionnaire_commandes');
    }

    #[Route('/{id}/terminer', name: 'app_gestionnaire_commande_terminer', methods: ['POST'])]
    public function terminer(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository
    ): Response {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('terminer_' . $id, $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        // Vérifier que la commande est prête
        if ($commande->getEtat() !== Commande::ETAT_PRETE) {
            $this->addFlash('danger', 'Seules les commandes prêtes peuvent être marquées comme terminées.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        $commande->setEtat(Commande::ETAT_TERMINEE);
        $this->entityManager->flush();

        $this->addFlash('success', 'Commande #' . $commande->getNumero() . ' marquée comme terminée.');

        return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
    }

    #[Route('/{id}/preparer', name: 'app_gestionnaire_commande_preparer', methods: ['POST'])]
    public function preparer(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository
    ): Response {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('preparer_' . $id, $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        if ($commande->getEtat() !== Commande::ETAT_EN_ATTENTE) {
            $this->addFlash('danger', 'Seules les commandes en attente peuvent passer en préparation.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        $commande->setEtat(Commande::ETAT_EN_PREPARATION);
        $this->entityManager->flush();

        $this->addFlash('success', 'Commande #' . $commande->getNumero() . ' en préparation.');

        return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
    }

    #[Route('/{id}/prete', name: 'app_gestionnaire_commande_prete', methods: ['POST'])]
    public function marquerPrete(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository
    ): Response {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('prete_' . $id, $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        if ($commande->getEtat() !== Commande::ETAT_EN_PREPARATION) {
            $this->addFlash('danger', 'Seules les commandes en préparation peuvent être marquées comme prêtes.');
            return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
        }

        $commande->setEtat(Commande::ETAT_PRETE);
        $this->entityManager->flush();

        $this->addFlash('success', 'Commande #' . $commande->getNumero() . ' prête !');

        return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $id]);
    }

    private function getTransitionsAutorisees(string $etatActuel): array
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

    private function getEtatLabel(string $etat): string
    {
        return match($etat) {
            Commande::ETAT_EN_ATTENTE => 'En attente',
            Commande::ETAT_EN_PREPARATION => 'En préparation',
            Commande::ETAT_PRETE => 'Prête',
            Commande::ETAT_TERMINEE => 'Terminée',
            Commande::ETAT_ANNULEE => 'Annulée',
            default => $etat,
        };
    }

    #[Route('/export', name: 'app_gestionnaire_commandes_export')]
    public function export(
        Request $request,
        CommandeRepository $commandeRepository,
        CommandeService $commandeService
    ): Response {
        // Récupérer les filtres
        $etat = $request->query->get('etat');
        $typeService = $request->query->get('type_service');
        $dateDebut = $request->query->get('date_debut') ? new \DateTime($request->query->get('date_debut')) : null;
        $dateFin = $request->query->get('date_fin') ? new \DateTime($request->query->get('date_fin') . ' 23:59:59') : null;
        $clientId = $request->query->get('client') ? (int) $request->query->get('client') : null;
        $recherche = $request->query->get('recherche');

        // Récupérer toutes les commandes (sans pagination pour l'export)
        $commandes = $commandeRepository->findWithFilters(
            $etat,
            $typeService,
            $dateDebut,
            $dateFin,
            $clientId,
            null,
            null,
            $recherche,
            1,
            10000 // Max pour l'export
        );

        return $commandeService->exporterCommandesCsv($commandes);
    }
}
