package com.bresil.views.components;

import com.bresil.config.factory.ServiceFactory;
import com.bresil.service.BurgerService;

import java.io.File;
import java.math.BigDecimal;

/**
 * Vue Burger
 */
public class BurgerView {
    
    private final BurgerService burgerService;
    
    public BurgerView() {
        this.burgerService = ServiceFactory.getInstance().getBurgerService();
    }
    
    /**
     * Affiche le menu principal des burgers
     */
    public void afficherMenu() {
        boolean continuer = true;
        
        while (continuer) {
            ConsoleHelper.afficherTitre("GESTION DES BURGERS");
            System.out.println("1. Créer un burger");
            System.out.println("2. Lister les burgers");
            System.out.println("3. Modifier un burger");
            System.out.println("4. Supprimer un burger");
            System.out.println("0. Retour au menu principal");
            System.out.println("-".repeat(60));
            
            int choix = ConsoleHelper.lireEntier("Votre choix : ");
            
            try {
                switch (choix) {
                    case 1:
                        creerBurger();
                        break;
                    case 2:
                        listerBurgers();
                        break;
                    case 3:
                        modifierBurger();
                        break;
                    case 4:
                        supprimerBurger();
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
    
    /**
     * Création d'un burger
     */
    private void creerBurger() {
        ConsoleHelper.afficherSousTitre("CRÉER UN BURGER");
        
        try {
            String nom = ConsoleHelper.lireTexte("Nom du burger : ");
            String prixStr = ConsoleHelper.lireDecimal("Prix (FCFA) : ");
            BigDecimal prix = new BigDecimal(prixStr);
            String description = ConsoleHelper.lireTexte("Description : ");
            String ingredients = ConsoleHelper.lireTexte("Ingrédients (séparés par des virgules) : ");
            
            // Sélection de l'image via fenêtre de dialogue
            File imageFile = ConsoleHelper.selectionnerImage();
            if (imageFile == null) {
                ConsoleHelper.afficherErreur("Une image est requise pour créer un burger");
                return;
            }
            
            Long id = burgerService.creerBurger(nom, prix, description, ingredients, imageFile, imageFile.getAbsolutePath());
            ConsoleHelper.afficherSucces("Burger créé avec succès ! ID: " + id);
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la création: " + e.getMessage());
        }
    }
    
    /**
     * Listage des burgers
     */
    private void listerBurgers() {
        ConsoleHelper.afficherSousTitre("LISTE DES BURGERS");
        
        try {
            burgerService.afficherBurgers();
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors du listage: " + e.getMessage());
        }
    }
    
    /**
     * Modification d'un burger
     */
    private void modifierBurger() {
        ConsoleHelper.afficherSousTitre("MODIFIER UN BURGER");
        
        try {
            // Afficher la liste d'abord
            burgerService.afficherBurgers();
            
            System.out.println();
            Long id = ConsoleHelper.lireLong("ID du burger à modifier : ");
            
            String nom = ConsoleHelper.lireTexte("Nouveau nom : ");
            String prixStr = ConsoleHelper.lireDecimal("Nouveau prix (FCFA) : ");
            BigDecimal prix = new BigDecimal(prixStr);
            String description = ConsoleHelper.lireTexte("Nouvelle description : ");
            String ingredients = ConsoleHelper.lireTexte("Nouveaux ingrédients : ");
            
            // Demander si nouvelle image
            boolean changerImage = ConsoleHelper.lireConfirmation("Changer l'image ?");
            File imageFile = null;
            if (changerImage) {
                imageFile = ConsoleHelper.selectionnerImage();
            }
            
            burgerService.modifierBurger(id, nom, prix, description, ingredients, imageFile);
            ConsoleHelper.afficherSucces("Burger modifié avec succès !");
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la modification: " + e.getMessage());
        }
    }
    
    /**
     * Suppression d'un burger
     */
    private void supprimerBurger() {
        ConsoleHelper.afficherSousTitre("SUPPRIMER UN BURGER");
        
        try {
            // Afficher la liste d'abord
            burgerService.afficherBurgers();
            
            System.out.println();
            Long id = ConsoleHelper.lireLong("ID du burger à supprimer : ");
            
            boolean confirmation = ConsoleHelper.lireConfirmation(
                "\nÊtes-vous sûr de vouloir archiver ce burger ?"
            );
            
            if (confirmation) {
                burgerService.supprimerBurger(id);
                ConsoleHelper.afficherSucces("Burger archivé avec succès !");
            } else {
                ConsoleHelper.afficherAvertissement("Suppression annulée");
            }
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la suppression: " + e.getMessage());
        }
    }
}
