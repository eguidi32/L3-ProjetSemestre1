package com.bresil.views.components;

import com.bresil.config.factory.ServiceFactory;
import com.bresil.service.ZoneService;

import java.math.BigDecimal;

/**
 * Vue pour la gestion des Zones de Livraison
 */
public class ZoneView {
    
    private final ZoneService zoneService;
    
    public ZoneView() {
        this.zoneService = ServiceFactory.getInstance().getZoneService();
    }
    
    public void afficherMenu() {
        boolean continuer = true;
        
        while (continuer) {
            ConsoleHelper.afficherTitre("GESTION DES ZONES DE LIVRAISON");
            System.out.println("1. Créer une zone");
            System.out.println("2. Lister les zones");
            System.out.println("3. Modifier une zone");
            System.out.println("4. Supprimer une zone");
            System.out.println("0. Retour au menu principal");
            System.out.println("-".repeat(60));
            
            int choix = ConsoleHelper.lireEntier("Votre choix : ");
            
            try {
                switch (choix) {
                    case 1:
                        creerZone();
                        break;
                    case 2:
                        listerZones();
                        break;
                    case 3:
                        modifierZone();
                        break;
                    case 4:
                        supprimerZone();
                        break;
                    case 0:
                        continuer = false;
                        break;
                    default:
                        ConsoleHelper.afficherErreur("Choix invalide");
                }
            } catch (Exception e) {
                ConsoleHelper.afficherErreur("Une erreur est survenue: " + e.getMessage());
            }
            
            if (continuer) {
                ConsoleHelper.pause();
            }
        }
    }
    
    private void creerZone() {
        ConsoleHelper.afficherSousTitre("CRÉER UNE ZONE DE LIVRAISON");
        
        try {
            String nom = ConsoleHelper.lireTexte("Nom de la zone : ");
            String fraisStr = ConsoleHelper.lireDecimal("Frais de livraison (FCFA) : ");
            BigDecimal fraisLivraison = new BigDecimal(fraisStr);
            String quartiers = ConsoleHelper.lireTexte("Quartiers couverts (séparés par des virgules) : ");
            
            Long id = zoneService.creerZone(nom, fraisLivraison, quartiers);
            ConsoleHelper.afficherSucces("Zone créée avec succès ! ID: " + id);
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la création: " + e.getMessage());
        }
    }
    
    private void listerZones() {
        ConsoleHelper.afficherSousTitre("LISTE DES ZONES");
        
        try {
            zoneService.afficherZones();
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors du listage: " + e.getMessage());
        }
    }
    
    private void modifierZone() {
        ConsoleHelper.afficherSousTitre("MODIFIER UNE ZONE");
        
        try {
            zoneService.afficherZones();
            
            System.out.println();
            Long id = ConsoleHelper.lireLong("ID de la zone à modifier : ");
            
            String nom = ConsoleHelper.lireTexte("Nouveau nom : ");
            String fraisStr = ConsoleHelper.lireDecimal("Nouveaux frais de livraison (FCFA) : ");
            BigDecimal fraisLivraison = new BigDecimal(fraisStr);
            String quartiers = ConsoleHelper.lireTexte("Nouveaux quartiers couverts (séparés par des virgules) : ");
            
            zoneService.modifierZone(id, nom, fraisLivraison, quartiers);
            ConsoleHelper.afficherSucces("Zone modifiée avec succès !");
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la modification: " + e.getMessage());
        }
    }
    
    private void supprimerZone() {
        ConsoleHelper.afficherSousTitre("SUPPRIMER UNE ZONE");
        
        try {
            zoneService.afficherZones();
            
            System.out.println();
            Long id = ConsoleHelper.lireLong("ID de la zone à supprimer : ");
            
            boolean confirmation = ConsoleHelper.lireConfirmation(
                "\nÊtes-vous sûr de vouloir archiver cette zone ?"
            );
            
            if (confirmation) {
                zoneService.supprimerZone(id);
                ConsoleHelper.afficherSucces("Zone archivée avec succès !");
            } else {
                ConsoleHelper.afficherAvertissement("Suppression annulée");
            }
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la suppression: " + e.getMessage());
        }
    }
}
