<?php

use App\Repository\BurgerRepository;
use App\Repository\MenuRepository;

#[Route('', name: 'app_gestionnaire_commandes')]
public function index(
    Request $request, 
    CommandeRepository $commandeRepository, 
    UtilisateurRepository $utilisateurRepository,
    BurgerRepository $burgerRepository,
    MenuRepository $menuRepository
): Response {
    // Recuperer les filtres
    $etat = $request->query->get('etat');
    $typeService = $request->query->get('type_service');
    $dateDebut = $request->query->get('date_debut') ? new \DateTime($request->query->get('date_debut')) : null;
    $dateFin = $request->query->get('date_fin') ? new \DateTime($request->query->get('date_fin')) : null;
    $clientId = $request->query->get('client') ? (int) $request->query->get('client') : null;
    $burgerId = $request->query->get('burger') ? (int) $request->query->get('burger') : null;
    $menuId = $request->query->get('menu') ? (int) $request->query->get('menu') : null;

    // Appliquer les filtres
    $commandes = $commandeRepository->findWithFilters($etat, $typeService, $dateDebut, $dateFin, $clientId, $burgerId, $menuId);

    // Recuperer les listes pour les filtres
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
        ],
    ]);
}