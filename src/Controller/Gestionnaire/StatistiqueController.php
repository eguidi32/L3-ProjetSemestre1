<?php

namespace App\Controller\Gestionnaire;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\BurgerRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_gestionnaire_statistiques')]
    public function index(
        Request $request,
        CommandeRepository $commandeRepository,
        LigneCommandeRepository $ligneCommandeRepository,
        BurgerRepository $burgerRepository,
        MenuRepository $menuRepository
    ): Response {
        // Date par defaut : aujourd'hui
        $dateStr = $request->query->get('date', date('Y-m-d'));
        $date = new \DateTime($dateStr);
        $dateFin = (clone $date)->setTime(23, 59, 59);
        $dateDebut = (clone $date)->setTime(0, 0, 0);

        // Statistiques du jour selectionne
        $stats = $this->getStatistiquesJour($dateDebut, $dateFin);

        // Produits les plus vendus du jour
        $produitsVendus = $this->getProduitsPlusVendus($dateDebut, $dateFin);

        // Commandes par type de service
        $commandesParType = $this->getCommandesParType($dateDebut, $dateFin);

        // Evolution sur les 7 derniers jours
        $evolution = $this->getEvolutionSemaine($date);

        return $this->render('gestionnaire/statistique/index.html.twig', [
            'date' => $date,
            'stats' => $stats,
            'produitsVendus' => $produitsVendus,
            'commandesParType' => $commandesParType,
            'evolution' => $evolution,
        ]);
    }

    private function getStatistiquesJour(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        $conn = $this->entityManager->getConnection();

        // Commandes en cours
        $sqlEnCours = "SELECT COUNT(*) as total FROM commande 
                       WHERE date_commande >= :debut AND date_commande <= :fin 
                       AND etat IN ('EN_ATTENTE', 'EN_PREPARATION', 'PRETE')";
        $commandesEnCours = $conn->executeQuery($sqlEnCours, [
            'debut' => $dateDebut->format('Y-m-d H:i:s'),
            'fin' => $dateFin->format('Y-m-d H:i:s'),
        ])->fetchOne();

        // Commandes validees (terminees)
        $sqlValidees = "SELECT COUNT(*) as total FROM commande 
                        WHERE date_commande >= :debut AND date_commande <= :fin 
                        AND etat IN ('PRETE', 'TERMINEE')";
        $commandesValidees = $conn->executeQuery($sqlValidees, [
            'debut' => $dateDebut->format('Y-m-d H:i:s'),
            'fin' => $dateFin->format('Y-m-d H:i:s'),
        ])->fetchOne();

        // Recette journaliere
        $sqlRecette = "SELECT COALESCE(SUM(montant_total), 0) as total FROM commande 
                       WHERE date_commande >= :debut AND date_commande <= :fin 
                       AND etat != 'ANNULEE'";
        $recette = $conn->executeQuery($sqlRecette, [
            'debut' => $dateDebut->format('Y-m-d H:i:s'),
            'fin' => $dateFin->format('Y-m-d H:i:s'),
        ])->fetchOne();

        // Commandes annulees
        $sqlAnnulees = "SELECT COUNT(*) as total FROM commande 
                        WHERE date_commande >= :debut AND date_commande <= :fin 
                        AND etat = 'ANNULEE'";
        $commandesAnnulees = $conn->executeQuery($sqlAnnulees, [
            'debut' => $dateDebut->format('Y-m-d H:i:s'),
            'fin' => $dateFin->format('Y-m-d H:i:s'),
        ])->fetchOne();

        // Total commandes
        $sqlTotal = "SELECT COUNT(*) as total FROM commande 
                     WHERE date_commande >= :debut AND date_commande <= :fin";
        $totalCommandes = $conn->executeQuery($sqlTotal, [
            'debut' => $dateDebut->format('Y-m-d H:i:s'),
            'fin' => $dateFin->format('Y-m-d H:i:s'),
        ])->fetchOne();

        // Panier moyen
        $panierMoyen = $totalCommandes > 0 ? $recette / $totalCommandes : 0;

        return [
            'commandesEnCours' => (int) $commandesEnCours,
            'commandesValidees' => (int) $commandesValidees,
            'recette' => (float) $recette,
            'commandesAnnulees' => (int) $commandesAnnulees,
            'totalCommandes' => (int) $totalCommandes,
            'panierMoyen' => $panierMoyen,
        ];
    }

    private function getProduitsPlusVendus(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        $conn = $this->entityManager->getConnection();

        // Burgers les plus vendus
        $sqlBurgers = "SELECT b.nom, b.image, SUM(lc.quantite) as quantite, SUM(lc.sous_total) as chiffre
                       FROM ligne_commande lc
                       INNER JOIN commande c ON lc.id_commande = c.id_commande
                       INNER JOIN burger b ON lc.id_burger = b.id_burger
                       WHERE c.date_commande >= :debut AND c.date_commande <= :fin
                       AND c.etat != 'ANNULEE'
                       AND lc.type_produit = 'BURGER'
                       GROUP BY b.id_burger, b.nom, b.image
                       ORDER BY quantite DESC
                       LIMIT 5";
        $burgers = $conn->executeQuery($sqlBurgers, [
            'debut' => $dateDebut->format('Y-m-d H:i:s'),
            'fin' => $dateFin->format('Y-m-d H:i:s'),
        ])->fetchAllAssociative();

        // Menus les plus vendus
        $sqlMenus = "SELECT m.nom, m.image, SUM(lc.quantite) as quantite, SUM(lc.sous_total) as chiffre
                     FROM ligne_commande lc
                     INNER JOIN commande c ON lc.id_commande = c.id_commande
                     INNER JOIN menu m ON lc.id_menu = m.id_menu
                     WHERE c.date_commande >= :debut AND c.date_commande <= :fin
                     AND c.etat != 'ANNULEE'
                     AND lc.type_produit = 'MENU'
                     GROUP BY m.id_menu, m.nom, m.image
                     ORDER BY quantite DESC
                     LIMIT 5";
        $menus = $conn->executeQuery($sqlMenus, [
            'debut' => $dateDebut->format('Y-m-d H:i:s'),
            'fin' => $dateFin->format('Y-m-d H:i:s'),
        ])->fetchAllAssociative();

        return [
            'burgers' => $burgers,
            'menus' => $menus,
        ];
    }

    private function getCommandesParType(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        $conn = $this->entityManager->getConnection();

        $sql = "SELECT type_service, COUNT(*) as total, SUM(montant_total) as montant
                FROM commande
                WHERE date_commande >= :debut AND date_commande <= :fin
                AND etat != 'ANNULEE'
                GROUP BY type_service";

        $result = $conn->executeQuery($sql, [
            'debut' => $dateDebut->format('Y-m-d H:i:s'),
            'fin' => $dateFin->format('Y-m-d H:i:s'),
        ])->fetchAllAssociative();

        $data = [
            'SUR_PLACE' => ['total' => 0, 'montant' => 0, 'label' => 'Sur place'],
            'A_EMPORTER' => ['total' => 0, 'montant' => 0, 'label' => 'A emporter'],
            'LIVRAISON' => ['total' => 0, 'montant' => 0, 'label' => 'Livraison'],
        ];

        foreach ($result as $row) {
            if (isset($data[$row['type_service']])) {
                $data[$row['type_service']]['total'] = (int) $row['total'];
                $data[$row['type_service']]['montant'] = (float) $row['montant'];
            }
        }

        return $data;
    }

    private function getEvolutionSemaine(\DateTime $date): array
    {
        $conn = $this->entityManager->getConnection();
        $evolution = [];

        for ($i = 6; $i >= 0; $i--) {
            $jour = (clone $date)->modify("-{$i} days");
            $debut = (clone $jour)->setTime(0, 0, 0);
            $fin = (clone $jour)->setTime(23, 59, 59);

            $sql = "SELECT COUNT(*) as commandes, COALESCE(SUM(montant_total), 0) as recette
                    FROM commande
                    WHERE date_commande >= :debut AND date_commande <= :fin
                    AND etat != 'ANNULEE'";

            $result = $conn->executeQuery($sql, [
                'debut' => $debut->format('Y-m-d H:i:s'),
                'fin' => $fin->format('Y-m-d H:i:s'),
            ])->fetchAssociative();

            $evolution[] = [
                'date' => $jour->format('d/m'),
                'jour' => $jour->format('D'),
                'commandes' => (int) $result['commandes'],
                'recette' => (float) $result['recette'],
            ];
        }

        return $evolution;
    }
}