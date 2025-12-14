package com.bresil;

import com.bresil.config.database.DatabaseConfig;
import com.bresil.views.MainView;
import com.bresil.views.components.ConsoleHelper;

/**
 * Classe principale de l'application
 * Point d'entrée du programme
 */
public class Main {
    
    public static void main(String[] args) {
        System.out.println("Démarrage de l'application Brasil Burger...\n");
        
        // Test de connexion à la base de données
        DatabaseConfig dbConfig = DatabaseConfig.getInstance();
        
        if (dbConfig.testConnection()) {
            ConsoleHelper.afficherSucces("Connexion à la base de données réussie !");
            System.out.println("Version: " + dbConfig.getProperty("app.version"));
            
            // Lancement de l'application
            MainView mainView = new MainView();
            mainView.afficherMenuPrincipal();
            
        } else {
            ConsoleHelper.afficherErreur("Impossible de se connecter à la base de données");
            ConsoleHelper.afficherAvertissement("Vérifiez vos paramètres dans application.properties");
            System.exit(1);
        }
    }
}
