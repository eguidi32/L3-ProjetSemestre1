package com.bresil.views.components;

import com.bresil.config.factory.ServiceFactory;
import com.bresil.service.ComplementService;

import java.io.File;
import java.math.BigDecimal;

/**
 * Vue pour la gestion des Compléments
 */
public class ComplementView {
    
    private final ComplementService complementService;
    
    public ComplementView() {
        this.complementService = ServiceFactory.getInstance().getComplementService();
    }
    
    public void afficherMenu() {
        boolean continuer = true;
        
        while (continuer) {
            ConsoleHelper.afficherTitre("GESTION DES COMPLÉMENTS");
            System.out.println("1. Créer un complément");
            System.out.println("2. Lister les compléments");
            System.out.println("3. Modifier un complément");
            System.out.println("4. Supprimer un complément");
            System.out.println("0. Retour au menu principal");
            System.out.println("-".repeat(60));
            
            int choix = ConsoleHelper.lireEntier("Votre choix : ");
            
            try {
                switch (choix) {
                    case 1:
                        creerComplement();
                        break;
                    case 2:
                        listerComplements();
                        break;
                    case 3:
                        modifierComplement();
                        break;
                    case 4:
                        supprimerComplement();
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
    
    private void creerComplement() {
        ConsoleHelper.afficherSousTitre("CRÉER UN COMPLÉMENT");
        
        try {
            String nom = ConsoleHelper.lireTexte("Nom du complément : ");
            String prixStr = ConsoleHelper.lireDecimal("Prix (FCFA) : ");
            BigDecimal prix = new BigDecimal(prixStr);
            
            System.out.println("\nCatégories disponibles:");
            System.out.println("1. FRITES");
            System.out.println("2. BOISSON");
            int choixCategorie = ConsoleHelper.lireEntier("Choisir une catégorie (1-2) : ");
            
            String categorie;
            switch (choixCategorie) {
                case 1:
                    categorie = "FRITES";
                    break;
                case 2:
                    categorie = "BOISSON";
                    break;
                default:
                    ConsoleHelper.afficherErreur("Catégorie invalide. Seules FRITES et BOISSON sont acceptées.");
                    return;
            }
            
            // Sélection de l'image via fenêtre de dialogue
            File imageFile = ConsoleHelper.selectionnerImage();
            if (imageFile == null) {
                ConsoleHelper.afficherErreur("Une image est requise pour créer un complément");
                return;
            }
            
            Long id = complementService.creerComplement(nom, prix, categorie, imageFile, imageFile.getAbsolutePath());
            ConsoleHelper.afficherSucces("Complément créé avec succès ! ID: " + id);
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la création: " + e.getMessage());
        }
    }
    
    private void listerComplements() {
        ConsoleHelper.afficherSousTitre("LISTE DES COMPLÉMENTS");
        
        try {
            complementService.afficherComplements();
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors du listage: " + e.getMessage());
        }
    }
    
    private void modifierComplement() {
        ConsoleHelper.afficherSousTitre("MODIFIER UN COMPLÉMENT");
        
        try {
            complementService.afficherComplements();
            
            System.out.println();
            Long id = ConsoleHelper.lireLong("ID du complément à modifier : ");
            
            String nom = ConsoleHelper.lireTexte("Nouveau nom : ");
            String prixStr = ConsoleHelper.lireDecimal("Nouveau prix (FCFA) : ");
            BigDecimal prix = new BigDecimal(prixStr);
            
            System.out.println("\nCatégories disponibles:");
            System.out.println("1. FRITES");
            System.out.println("2. BOISSON");
            int choixCategorie = ConsoleHelper.lireEntier("Choisir une catégorie (1-2) : ");
            
            String categorie;
            switch (choixCategorie) {
                case 1:
                    categorie = "FRITES";
                    break;
                case 2:
                    categorie = "BOISSON";
                    break;
                default:
                    ConsoleHelper.afficherErreur("Catégorie invalide");
                    return;
            }
            
            // Demander si nouvelle image
            boolean changerImage = ConsoleHelper.lireConfirmation("Changer l'image ?");
            File imageFile = null;
            if (changerImage) {
                imageFile = ConsoleHelper.selectionnerImage();
            }
            
            complementService.modifierComplement(id, nom, prix, categorie, imageFile);
            ConsoleHelper.afficherSucces("Complément modifié avec succès !");
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la modification: " + e.getMessage());
        }
    }
    
    private void supprimerComplement() {
        ConsoleHelper.afficherSousTitre("SUPPRIMER UN COMPLÉMENT");
        
        try {
            complementService.afficherComplements();
            
            System.out.println();
            Long id = ConsoleHelper.lireLong("ID du complément à supprimer : ");
            
            boolean confirmation = ConsoleHelper.lireConfirmation(
                "\nÊtes-vous sûr de vouloir archiver ce complément ?"
            );
            
            if (confirmation) {
                complementService.supprimerComplement(id);
                ConsoleHelper.afficherSucces("Complément archivé avec succès !");
            } else {
                ConsoleHelper.afficherAvertissement("Suppression annulée");
            }
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la suppression: " + e.getMessage());
        }
    }
}
