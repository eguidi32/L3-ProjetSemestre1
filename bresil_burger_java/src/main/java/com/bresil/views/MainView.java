package com.bresil.views;

import com.bresil.views.components.BurgerView;
import com.bresil.views.components.ComplementView;
import com.bresil.views.components.ConsoleHelper;

/**
 * Vue principale de l'application
 * Point d'entrée de l'interface utilisateur
 */
public class MainView {
    
    private final BurgerView burgerView;
    private final ComplementView complementView;

    public MainView() {
        this.burgerView = new BurgerView();
        this.complementView = new ComplementView();
    }
    
    /**
     * Affiche le menu principal
     */
    public void afficherMenuPrincipal() {
        boolean continuer = true;
        
        while (continuer) {
            ConsoleHelper.afficherTitre("BRASIL BURGER - GESTION RESSOURCES");
            System.out.println("1. Gérer les Burgers");
            System.out.println("2. Gérer les Menus");
            System.out.println("3. Gérer les Compléments");
            System.out.println("4. Gérer les Zones de Livraison");
            System.out.println("0. Quitter");
            System.out.println("-".repeat(60));
            
            int choix = ConsoleHelper.lireEntier("Votre choix : ");
            
            switch (choix) {
                case 1:
                    burgerView.afficherMenu();
                    break;
                case 2:
                    break;
                case 3:
                    complementView.afficherMenu();
                    break;
                case 4:
                    break;
                case 0:
                    continuer = false;
                    System.out.println("\nAu revoir !");
                    break;
                default:
                    ConsoleHelper.afficherErreur("Choix invalide");
                    ConsoleHelper.pause();
            }
        }
    }
}
