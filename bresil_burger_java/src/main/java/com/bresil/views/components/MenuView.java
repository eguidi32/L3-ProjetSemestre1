package com.bresil.views.components;

import com.bresil.config.factory.ServiceFactory;
import com.bresil.entity.Burger;
import com.bresil.entity.Complement;
import com.bresil.service.BurgerService;
import com.bresil.service.ComplementService;
import com.bresil.service.MenuService;

import java.io.File;
import java.util.List;

/**
 * Vue pour la gestion des Menus
 * Un menu est composé de : nom, image, burger, boisson, frites
 * Le prix est calculé automatiquement (somme des composants)
 */
public class MenuView {
    
    private final MenuService menuService;
    private final BurgerService burgerService;
    private final ComplementService complementService;
    
    public MenuView() {
        this.menuService = ServiceFactory.getInstance().getMenuService();
        this.burgerService = ServiceFactory.getInstance().getBurgerService();
        this.complementService = ServiceFactory.getInstance().getComplementService();
    }
    
    public void afficherMenu() {
        boolean continuer = true;
        
        while (continuer) {
            ConsoleHelper.afficherTitre("GESTION DES MENUS");
            System.out.println("1. Créer un menu");
            System.out.println("2. Lister les menus");
            System.out.println("3. Modifier un menu");
            System.out.println("4. Supprimer un menu");
            System.out.println("0. Retour au menu principal");
            System.out.println("-".repeat(60));
            
            int choix = ConsoleHelper.lireEntier("Votre choix : ");
            
            try {
                switch (choix) {
                    case 1:
                        creerMenu();
                        break;
                    case 2:
                        listerMenus();
                        break;
                    case 3:
                        modifierMenu();
                        break;
                    case 4:
                        supprimerMenu();
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
    
    private void creerMenu() {
        ConsoleHelper.afficherSousTitre("CRÉER UN MENU");
        System.out.println("Un menu = Burger + Boisson + Frites");
        System.out.println("Le prix sera calculé automatiquement.\n");
        
        try {
            // 1. Nom du menu
            String nom = ConsoleHelper.lireTexte("Nom du menu : ");
            
            // 2. Image via sélecteur de fichier
            File imageFile = ConsoleHelper.selectionnerImage();
            if (imageFile == null) {
                ConsoleHelper.afficherErreur("Une image est requise pour créer un menu");
                return;
            }
            
            // 3. Sélection du Burger
            System.out.println("\n--- SÉLECTION DU BURGER ---");
            List<Burger> burgers = burgerService.listerBurgers();
            if (burgers.isEmpty()) {
                ConsoleHelper.afficherErreur("Aucun burger disponible. Créez d'abord un burger.");
                return;
            }
            afficherBurgersDisponibles(burgers);
            Long idBurger = ConsoleHelper.lireLong("ID du burger : ");
            
            // 4. Sélection de la Boisson
            System.out.println("\n--- SÉLECTION DE LA BOISSON ---");
            List<Complement> boissons = complementService.listerComplementsParCategorie("BOISSON");
            if (boissons.isEmpty()) {
                ConsoleHelper.afficherErreur("Aucune boisson disponible. Créez d'abord une boisson.");
                return;
            }
            afficherComplementsDisponibles(boissons, "BOISSONS");
            Long idBoisson = ConsoleHelper.lireLong("ID de la boisson : ");
            
            // 5. Sélection des Frites
            System.out.println("\n--- SÉLECTION DES FRITES ---");
            List<Complement> frites = complementService.listerComplementsParCategorie("FRITES");
            if (frites.isEmpty()) {
                ConsoleHelper.afficherErreur("Aucune frites disponible. Créez d'abord des frites.");
                return;
            }
            afficherComplementsDisponibles(frites, "FRITES");
            Long idFrites = ConsoleHelper.lireLong("ID des frites : ");
            
            // Création du menu
            Long id = menuService.creerMenuAvecComposition(nom, imageFile, imageFile.getAbsolutePath(), idBurger, idBoisson, idFrites);
            ConsoleHelper.afficherSucces("Menu créé avec succès ! ID: " + id);
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la création: " + e.getMessage());
        }
    }
    
    private void afficherBurgersDisponibles(List<Burger> burgers) {
        System.out.println(String.format("%-6s %-25s %s", "ID", "Nom", "Prix (FCFA)"));
        System.out.println("-".repeat(50));
        for (Burger b : burgers) {
            System.out.println(String.format("%-6d %-25s %s", 
                b.getIdBurger(), 
                b.getNom().length() > 25 ? b.getNom().substring(0, 22) + "..." : b.getNom(),
                b.getPrix()));
        }
    }
    
    private void afficherComplementsDisponibles(List<Complement> complements, String titre) {
        System.out.println(String.format("%-6s %-25s %s", "ID", "Nom", "Prix (FCFA)"));
        System.out.println("-".repeat(50));
        for (Complement c : complements) {
            System.out.println(String.format("%-6d %-25s %s", 
                c.getIdComplement(), 
                c.getNom().length() > 25 ? c.getNom().substring(0, 22) + "..." : c.getNom(),
                c.getPrix()));
        }
    }
    
    private void listerMenus() {
        ConsoleHelper.afficherSousTitre("LISTE DES MENUS");
        
        try {
            menuService.afficherMenus();
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors du listage: " + e.getMessage());
        }
    }
    
    private void modifierMenu() {
        ConsoleHelper.afficherSousTitre("MODIFIER UN MENU");
        
        try {
            menuService.afficherMenus();
            
            System.out.println();
            Long id = ConsoleHelper.lireLong("ID du menu à modifier : ");
            
            // Nouveau nom
            String nom = ConsoleHelper.lireTexte("Nouveau nom : ");
            
            // Sélection du nouveau Burger
            System.out.println("\n--- SÉLECTION DU BURGER ---");
            List<Burger> burgers = burgerService.listerBurgers();
            if (burgers.isEmpty()) {
                ConsoleHelper.afficherErreur("Aucun burger disponible.");
                return;
            }
            afficherBurgersDisponibles(burgers);
            Long idBurger = ConsoleHelper.lireLong("ID du burger : ");
            
            // Sélection de la nouvelle Boisson
            System.out.println("\n--- SÉLECTION DE LA BOISSON ---");
            List<Complement> boissons = complementService.listerComplementsParCategorie("BOISSON");
            if (boissons.isEmpty()) {
                ConsoleHelper.afficherErreur("Aucune boisson disponible.");
                return;
            }
            afficherComplementsDisponibles(boissons, "BOISSONS");
            Long idBoisson = ConsoleHelper.lireLong("ID de la boisson : ");
            
            // Sélection des nouvelles Frites
            System.out.println("\n--- SÉLECTION DES FRITES ---");
            List<Complement> frites = complementService.listerComplementsParCategorie("FRITES");
            if (frites.isEmpty()) {
                ConsoleHelper.afficherErreur("Aucune frites disponible.");
                return;
            }
            afficherComplementsDisponibles(frites, "FRITES");
            Long idFrites = ConsoleHelper.lireLong("ID des frites : ");
            
            // Image (optionnelle)
            boolean changerImage = ConsoleHelper.lireConfirmation("Changer l'image ?");
            File imageFile = null;
            String cheminOuUrl = null;
            if (changerImage) {
                imageFile = ConsoleHelper.selectionnerImage();
                if (imageFile != null) {
                    cheminOuUrl = imageFile.getAbsolutePath();
                }
            }
            
            menuService.modifierMenu(id, nom, idBurger, idBoisson, idFrites, imageFile, cheminOuUrl);
            ConsoleHelper.afficherSucces("Menu modifié avec succès !");
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la modification: " + e.getMessage());
        }
    }
    
    private void supprimerMenu() {
        ConsoleHelper.afficherSousTitre("SUPPRIMER UN MENU");
        
        try {
            menuService.afficherMenus();
            
            System.out.println();
            Long id = ConsoleHelper.lireLong("ID du menu à supprimer : ");
            
            boolean confirmation = ConsoleHelper.lireConfirmation(
                "\nÊtes-vous sûr de vouloir archiver ce menu ?"
            );
            
            if (confirmation) {
                menuService.supprimerMenu(id);
                ConsoleHelper.afficherSucces("Menu archivé avec succès !");
            } else {
                ConsoleHelper.afficherAvertissement("Suppression annulée");
            }
            
        } catch (Exception e) {
            ConsoleHelper.afficherErreur("Erreur lors de la suppression: " + e.getMessage());
        }
    }
}
