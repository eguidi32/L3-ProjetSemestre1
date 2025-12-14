package com.bresil.views.components;

import javax.swing.*;
import javax.swing.filechooser.FileNameExtensionFilter;
import java.awt.*;
import java.io.File;
import java.util.Scanner;

/**
 * Utilitaire console
 */
public class ConsoleHelper {
    
    static final Scanner scanner = new Scanner(System.in);
    
    /**
     * Lit une ligne de texte non vide
     */
    public static String lireTexte(String prompt) {
        String texte;
        do {
            System.out.print(prompt);
            texte = scanner.nextLine().trim();
            if (texte.isEmpty()) {
                System.out.println("Ce champ ne peut pas être vide. Veuillez réessayer.");
            }
        } while (texte.isEmpty());
        return texte;
    }
    
    /**
     * Lit un entier valide
     */
    public static int lireEntier(String prompt) {
        while (true) {
            try {
                System.out.print(prompt);
                String input = scanner.nextLine().trim();
                return Integer.parseInt(input);
            } catch (NumberFormatException e) {
                System.out.println("Veuillez entrer un nombre entier valide.");
            }
        }
    }
    
    /**
     * Lit un long valide
     */
    public static Long lireLong(String prompt) {
        while (true) {
            try {
                System.out.print(prompt);
                String input = scanner.nextLine().trim();
                return Long.parseLong(input);
            } catch (NumberFormatException e) {
                System.out.println("Veuillez entrer un nombre valide.");
            }
        }
    }
    
    /**
     * Lit un nombre décimal valide
     */
    public static String lireDecimal(String prompt) {
        while (true) {
            try {
                System.out.print(prompt);
                String input = scanner.nextLine().trim();
                // Validation du format décimal
                Double.parseDouble(input);
                return input;
            } catch (NumberFormatException e) {
                System.out.println("Veuillez entrer un nombre décimal valide.");
            }
        }
    }
    
    /**
     * Lit une confirmation (oui/non)
     */
    public static boolean lireConfirmation(String prompt) {
        System.out.print(prompt + " (o/n) : ");
        String reponse = scanner.nextLine().trim().toLowerCase();
        return reponse.equals("o") || reponse.equals("oui");
    }
    
    /**
     * Affiche un titre centré avec bordure
     */
    public static void afficherTitre(String titre) {
        int longueur = 60;
        System.out.println("\n" + "=".repeat(longueur));
        int padding = (longueur - titre.length()) / 2;
        System.out.println(" ".repeat(padding) + titre);
        System.out.println("=".repeat(longueur));
    }
    
    /**
     * Affiche un sous-titre
     */
    public static void afficherSousTitre(String sousTitre) {
        System.out.println("\n" + sousTitre);
        System.out.println("-".repeat(60));
    }
    
    /**
     * Pause et attend une action utilisateur
     */
    public static void pause() {
        System.out.println("\nAppuyez sur Entrée pour continuer...");
        scanner.nextLine();
    }
    
    /**
     * Nettoie l'écran (simulation)
     */
    public static void clearScreen() {
        for (int i = 0; i < 2; i++) {
            System.out.println();
        }
    }
    
    /**
     * Affiche un message de succès
     */
    public static void afficherSucces(String message) {
        System.out.println("Succes" + message);
    }
    
    /**
     * Affiche un message d'erreur
     */
    public static void afficherErreur(String message) {
        System.err.println("Erreur" + message);
    }
    
    /**
     * Affiche un message d'avertissement
     */
    public static void afficherAvertissement(String message) {
        System.out.println("Avertissement" + message);
    }
    
    /**
     * Ouvre une fenêtre de sélection de fichier image
     * @return le fichier sélectionné ou null si annulé
     */
    public static File selectionnerImage() {
        System.out.println("Ouverture du sélecteur de fichier...");
        
        try {
            // Forcer le look and feel natif du système
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
        } catch (Exception ignored) {}
        
        // Créer une frame invisible pour ancrer le dialogue
        JFrame frame = new JFrame();
        frame.setAlwaysOnTop(true);
        frame.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
        
        JFileChooser fileChooser = new JFileChooser();
        fileChooser.setDialogTitle("Sélectionner une image");
        
        // Filtre pour les images
        FileNameExtensionFilter filter = new FileNameExtensionFilter(
            "Images (JPG, PNG, GIF, WEBP)", "jpg", "jpeg", "png", "gif", "webp"
        );
        fileChooser.setFileFilter(filter);
        fileChooser.setAcceptAllFileFilterUsed(false);
        
        // Ouvrir dans le dossier utilisateur par défaut
        fileChooser.setCurrentDirectory(new File(System.getProperty("user.home")));
        
        int result = fileChooser.showOpenDialog(frame);
        
        // Fermer la frame invisible
        frame.dispose();
        
        if (result == JFileChooser.APPROVE_OPTION) {
            File selectedFile = fileChooser.getSelectedFile();
            System.out.println("Image sélectionnée : " + selectedFile.getName());
            return selectedFile;
        } else {
            System.out.println("Sélection annulée");
            return null;
        }
    }
}
