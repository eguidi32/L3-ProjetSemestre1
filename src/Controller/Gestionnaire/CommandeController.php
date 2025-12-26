<?php

namespace App\Controller\Gestionnaire;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Repository\UtilisateurRepository;
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
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_gestionnaire_commandes')]
    public function index(Request $request, CommandeRepository $commandeRepository, UtilisateurRepository $utilisateurRepository): Response
    {
        // Récupérer les filtres
        $etat = $request->query->get('etat');
        $typeService = $request->query->get('type_service');
        $dateDebut = $request->query->get('date_debut') ? new \DateTime($request->query->get('date_debut')) : null;
        $dateFin = $request->query->get('date_fin') ? new \DateTime($request->query->get('date_fin')) : null;
        $clientId = $request->query->get('client') ? (int) $request->query->get('client') : null;

        // Appliquer les filtres
        $commandes = $commandeRepository->findWithFilters($etat, $typeService, $dateDebut, $dateFin, $clientId);

        // Récupérer la liste des clients pour le filtre
        $clients = $utilisateurRepository->findClients();

        return $this->render('gestionnaire/commande/index.html.twig', [
            'commandes' => $commandes,
            'clients' => $clients,
            'filtres' => [
                'etat' => $etat,
                'type_service' => $typeService,
                'date_debut' => $request->query->get('date_debut'),
                'date_fin' => $request->query->get('date_fin'),
                'client' => $clientId,
            ],
        ]);
    }

    #[Route('/{id}', name: 'app_gestionnaire_commande_show', requirements: ['id' => '\d+'])]
    public function show(Commande $commande): Response
    {
        return $this->render('gestionnaire/commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/changer-etat', name: 'app_gestionnaire_commande_changer_etat', methods: ['POST'])]
    public function changerEtat(Request $request, Commande $commande): Response
    {
        $nouvelEtat = $request->request->get('etat');
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('changer_etat' . $commande->getId(), $token)) {
            $etatsValides = [
                Commande::ETAT_EN_ATTENTE,
                Commande::ETAT_EN_PREPARATION,
                Commande::ETAT_PRETE,
                Commande::ETAT_TERMINEE,
                Commande::ETAT_ANNULEE,
            ];

            if (in_array($nouvelEtat, $etatsValides)) {
                $commande->setEtat($nouvelEtat);
                $this->entityManager->flush();

                $this->addFlash('success', 'L\'etat de la commande ' . $commande->getNumero() . ' a ete mis a jour.');
            }
        }

        return $this->redirectToRoute('app_gestionnaire_commande_show', ['id' => $commande->getId()]);
    }

    #[Route('/{id}/annuler', name: 'app_gestionnaire_commande_annuler', methods: ['POST'])]
    public function annuler(Request $request, Commande $commande): Response
    {
        if ($this->isCsrfTokenValid('annuler' . $commande->getId(), $request->request->get('_token'))) {
            if ($commande->getEtat() !== Commande::ETAT_TERMINEE) {
                $commande->setEtat(Commande::ETAT_ANNULEE);
                $this->entityManager->flush();

                $this->addFlash('success', 'La commande ' . $commande->getNumero() . ' a ete annulee.');
            } else {
                $this->addFlash('danger', 'Impossible d\'annuler une commande terminee.');
            }
        }

        return $this->redirectToRoute('app_gestionnaire_commandes');
    }

    #[Route('/{id}/terminer', name: 'app_gestionnaire_commande_terminer', methods: ['POST'])]
    public function terminer(Request $request, Commande $commande): Response
    {
        if ($this->isCsrfTokenValid('terminer' . $commande->getId(), $request->request->get('_token'))) {
            if ($commande->getEtat() === Commande::ETAT_PRETE) {
                $commande->setEtat(Commande::ETAT_TERMINEE);
                $this->entityManager->flush();

                $this->addFlash('success', 'La commande ' . $commande->getNumero() . ' a ete marquee comme terminee.');
            } else {
                $this->addFlash('warning', 'La commande doit etre prete avant d\'etre terminee.');
            }
        }

        return $this->redirectToRoute('app_gestionnaire_commandes');
    }
}