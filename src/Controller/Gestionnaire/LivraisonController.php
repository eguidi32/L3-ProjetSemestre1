<?php

namespace App\Controller\Gestionnaire;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestionnaire/livraisons')]
#[IsGranted('ROLE_GESTIONNAIRE')]
class LivraisonController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_gestionnaire_livraisons')]
    public function index(
        CommandeRepository $commandeRepository,
        ZoneRepository $zoneRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        set_time_limit(300);
        
        // Recuperer les commandes en livraison pretes (non affectees)
        $commandesNonAffectees = $commandeRepository->createQueryBuilder('c')
            ->where('c.typeService = :type')
            ->andWhere('c.etat = :etat')
            ->andWhere('c.livreur IS NULL')
            ->setParameter('type', Commande::TYPE_LIVRAISON)
            ->setParameter('etat', Commande::ETAT_PRETE)
            ->orderBy('c.zone', 'ASC')
            ->addOrderBy('c.dateCommande', 'ASC')
            ->getQuery()
            ->getResult();

        // Grouper par zone
        $commandesParZone = [];
        foreach ($commandesNonAffectees as $commande) {
            $zoneNom = $commande->getZone() ? $commande->getZone()->getNom() : 'Zone non definie';
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

        // Commandes en cours de livraison (affectees a un livreur)
        $commandesEnLivraison = $commandeRepository->createQueryBuilder('c')
            ->where('c.typeService = :type')
            ->andWhere('c.etat IN (:etats)')
            ->andWhere('c.livreur IS NOT NULL')
            ->setParameter('type', Commande::TYPE_LIVRAISON)
            ->setParameter('etats', [Commande::ETAT_PRETE, Commande::ETAT_EN_PREPARATION])
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();

        // Livreurs disponibles
        $livreurs = $utilisateurRepository->findLivreursDisponibles();

        return $this->render('gestionnaire/livraison/index.html.twig', [
            'commandesParZone' => $commandesParZone,
            'commandesEnLivraison' => $commandesEnLivraison,
            'livreurs' => $livreurs,
            'totalNonAffectees' => count($commandesNonAffectees),
        ]);
    }

    #[Route('/affecter', name: 'app_gestionnaire_livraison_affecter', methods: ['POST'])]
    public function affecter(
        Request $request,
        CommandeRepository $commandeRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        $commandeIds = $request->request->all('commandes');
        $livreurId = $request->request->get('livreur');
        $token = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('affecter_livreur', $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_gestionnaire_livraisons');
        }

        if (empty($commandeIds)) {
            $this->addFlash('warning', 'Veuillez selectionner au moins une commande.');
            return $this->redirectToRoute('app_gestionnaire_livraisons');
        }

        if (!$livreurId) {
            $this->addFlash('warning', 'Veuillez selectionner un livreur.');
            return $this->redirectToRoute('app_gestionnaire_livraisons');
        }

        $livreur = $utilisateurRepository->find($livreurId);
        if (!$livreur || $livreur->getTypeUtilisateur() !== 'LIVREUR') {
            $this->addFlash('danger', 'Livreur invalide.');
            return $this->redirectToRoute('app_gestionnaire_livraisons');
        }

        $count = 0;
        foreach ($commandeIds as $commandeId) {
            $commande = $commandeRepository->find($commandeId);
            if ($commande && $commande->isLivraison() && $commande->getLivreur() === null) {
                $commande->setLivreur($livreur);
                $count++;
            }
        }

        $this->entityManager->flush();

        $this->addFlash('success', $count . ' commande(s) affectee(s) a ' . $livreur->getNomComplet() . '.');
        return $this->redirectToRoute('app_gestionnaire_livraisons');
    }

    #[Route('/{id}/retirer-livreur', name: 'app_gestionnaire_livraison_retirer', methods: ['POST'])]
    public function retirerLivreur(Request $request, Commande $commande): Response
    {
        if ($this->isCsrfTokenValid('retirer' . $commande->getId(), $request->request->get('_token'))) {
            $commande->setLivreur(null);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le livreur a ete retire de la commande ' . $commande->getNumero() . '.');
        }

        return $this->redirectToRoute('app_gestionnaire_livraisons');
    }

    #[Route('/{id}/terminer', name: 'app_gestionnaire_livraison_terminer', methods: ['POST'])]
    public function terminer(Request $request, Commande $commande): Response
    {
        if ($this->isCsrfTokenValid('terminer_livraison' . $commande->getId(), $request->request->get('_token'))) {
            $commande->setEtat(Commande::ETAT_TERMINEE);
            $this->entityManager->flush();

            $this->addFlash('success', 'La livraison de la commande ' . $commande->getNumero() . ' est terminee.');
        }

        return $this->redirectToRoute('app_gestionnaire_livraisons');
    }

    #[Route('/zones', name: 'app_gestionnaire_zones')]
    public function zones(ZoneRepository $zoneRepository): Response
    {
        $zones = $zoneRepository->findAll();

        return $this->render('gestionnaire/livraison/zones.html.twig', [
            'zones' => $zones
        ]);
    }

    #[Route('/zones/{id}', name: 'app_gestionnaire_zone_show', methods: ['GET'])]
    public function showZone(\App\Entity\Zone $zone, CommandeRepository $commandeRepository): Response
    {
        // Compter les commandes livrées dans cette zone
        $commandesLivrees = $commandeRepository->count([
            'zone' => $zone,
            'etat' => Commande::ETAT_TERMINEE
        ]);

        // Compter les commandes en cours dans cette zone
        $commandesEnCours = $commandeRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.zone = :zone')
            ->andWhere('c.etat NOT IN (:etats)')
            ->setParameter('zone', $zone)
            ->setParameter('etats', [Commande::ETAT_TERMINEE, Commande::ETAT_ANNULEE])
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('gestionnaire/livraison/zone_show.html.twig', [
            'zone' => $zone,
            'commandesLivrees' => $commandesLivrees,
            'commandesEnCours' => $commandesEnCours
        ]);
    }

    #[Route('/zones/{id}/modifier', name: 'app_gestionnaire_zone_edit', methods: ['GET', 'POST'])]
    public function editZone(Request $request, \App\Entity\Zone $zone): Response
    {
        if ($request->isMethod('POST')) {
            $token = $request->request->get('_token');
            
            if ($this->isCsrfTokenValid('edit_zone_' . $zone->getId(), $token)) {
                $zone->setNom($request->request->get('nom'));
                $zone->setPrixLivraison($request->request->get('prix_livraison'));
                $zone->setQuartiers($request->request->get('quartiers'));
                
                $this->entityManager->flush();
                
                $this->addFlash('success', 'La zone "' . $zone->getNom() . '" a été modifiée avec succès.');
                return $this->redirectToRoute('app_gestionnaire_zones');
            }
        }

        return $this->render('gestionnaire/livraison/zone_edit.html.twig', [
            'zone' => $zone
        ]);
    }
}