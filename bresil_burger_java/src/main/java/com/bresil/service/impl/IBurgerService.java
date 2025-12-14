package com.bresil.service.impl;

import com.bresil.config.database.CloudinaryConfig;
import com.bresil.config.factory.RepositoryFactory;
import com.bresil.entity.Burger;
import com.bresil.repository.BurgerRepository;
import com.bresil.service.BurgerService;

import java.io.File;
import java.math.BigDecimal;
import java.util.List;
import java.util.Optional;

/**
 * Service Burger
 */
public class IBurgerService implements BurgerService {
    
    private final BurgerRepository burgerRepository;
    private final CloudinaryConfig cloudinaryConfig;
    
    public IBurgerService() {
        this.burgerRepository = RepositoryFactory.getInstance().getBurgerRepository();
        this.cloudinaryConfig = CloudinaryConfig.getInstance();
    }
    
    @Override
    public Long creerBurger(String nom, BigDecimal prix, String description, 
                            String ingredients, File imageFile, String imageSource) {
        try {
            // Validation des données
            validateBurgerData(nom, prix, description, ingredients, imageFile, imageSource);
            
            // Upload de l'image sur Cloudinary (fichier local ou URL)
            String imageUrl;
            if (imageFile != null) {
                imageUrl = cloudinaryConfig.uploadImage(imageFile, "brasil-burger/burgers");
            } else {
                imageUrl = cloudinaryConfig.uploadImageFromUrl(imageSource, "brasil-burger/burgers");
            }
            
            // Création de l'entité Burger
            Burger burger = new Burger(nom, prix, description, ingredients, imageUrl);
            
            // Sauvegarde en base de données
            Long id = burgerRepository.create(burger);
            
            System.out.println("Burger créé avec succès ! ID: " + id);
            return id;
            
        } catch (Exception e) {
            throw new RuntimeException("Erreur lors de la création du burger: " + e.getMessage(), e);
        }
    }
    
    @Override
    public Burger getBurgerById(Long id) {
        if (id == null || id <= 0) {
            throw new IllegalArgumentException("L'ID du burger doit être valide");
        }
        
        Optional<Burger> burger = burgerRepository.findById(id);
        
        if (burger.isEmpty()) {
            throw new RuntimeException("Burger non trouvé avec l'ID: " + id);
        }
        
        return burger.get();
    }
    
    @Override
    public List<Burger> listerBurgers() {
        return burgerRepository.findAll();
    }
    
    @Override
    public void afficherBurgers() {
        List<Burger> burgers = listerBurgers();
        
        if (burgers.isEmpty()) {
            System.out.println("\n Aucun burger disponible");
            return;
        }
        
        System.out.println("\n LISTE DES BURGERS");
        System.out.println("=".repeat(120));
        System.out.printf("%-5s %-25s %-12s %-40s %-30s%n", 
            "ID", "Nom", "Prix (FCFA)", "Description", "Ingrédients");
        System.out.println("-".repeat(120));
        
        for (Burger burger : burgers) {
            String description = truncate(burger.getDescription(), 37);
            String ingredients = truncate(burger.getIngredients(), 27);
            
            System.out.printf("%-5d %-25s %-12s %-40s %-30s%n",
                burger.getIdBurger(),
                burger.getNom(),
                burger.getPrix(),
                description,
                ingredients);
        }
        
        System.out.println("=".repeat(120));
        System.out.println("Total: " + burgers.size() + " burger(s)");
    }
    
    @Override
    public void modifierBurger(Long id, String nom, BigDecimal prix, String description,
                               String ingredients, File nouvelleImage) {
        try {
            // Récupération du burger existant
            Burger burger = getBurgerById(id);
            
            // Validation des nouvelles données (image optionnelle en modification)
            validateBurgerData(nom, prix, description, ingredients, nouvelleImage, burger.getImage());
            
            String imageUrl = burger.getImage();
            
            // Si une nouvelle image est fournie
            if (nouvelleImage != null && nouvelleImage.exists()) {
                // Suppression de l'ancienne image
                String oldPublicId = cloudinaryConfig.extractPublicId(imageUrl);
                if (oldPublicId != null) {
                    cloudinaryConfig.deleteImage(oldPublicId);
                }
                
                // Upload de la nouvelle image
                imageUrl = cloudinaryConfig.uploadImage(nouvelleImage, "brasil-burger/burgers");
            }
            
            // Mise à jour des données
            burger.setNom(nom);
            burger.setPrix(prix);
            burger.setDescription(description);
            burger.setIngredients(ingredients);
            burger.setImage(imageUrl);
            
            // Sauvegarde
            boolean success = burgerRepository.update(burger);
            
            if (success) {
                System.out.println("Burger modifié avec succès !");
            } else {
                throw new RuntimeException("Échec de la modification du burger");
            }
            
        } catch (Exception e) {
            throw new RuntimeException("Erreur lors de la modification: " + e.getMessage(), e);
        }
    }
    
    @Override
    public void supprimerBurger(Long id) {
        // Vérification de l'existence
        getBurgerById(id);
        
        // Suppression (soft delete)
        boolean success = burgerRepository.delete(id);
        
        if (success) {
            System.out.println("Burger archivé avec succès !");
        } else {
            throw new RuntimeException("Échec de la suppression du burger");
        }
    }
    
    /**
     * Validation des données du burger
     * Principe: Validation centralisée pour éviter la duplication
     */
    private void validateBurgerData(String nom, BigDecimal prix, String description,
                                    String ingredients, File imageFile, String imageSource) {
        if (nom == null || nom.trim().isEmpty()) {
            throw new IllegalArgumentException("Le nom du burger est obligatoire");
        }
        
        if (prix == null || prix.compareTo(BigDecimal.ZERO) <= 0) {
            throw new IllegalArgumentException("Le prix doit être supérieur à zéro");
        }
        
        if (description == null || description.trim().isEmpty()) {
            throw new IllegalArgumentException("La description est obligatoire");
        }
        
        if (ingredients == null || ingredients.trim().isEmpty()) {
            throw new IllegalArgumentException("Les ingrédients sont obligatoires");
        }
        
        // Vérifier qu'on a soit un fichier soit une URL
        if (imageFile == null && (imageSource == null || imageSource.trim().isEmpty())) {
            throw new IllegalArgumentException("Une image est obligatoire (fichier ou URL)");
        }
        
        if (imageFile != null && !imageFile.exists()) {
            throw new IllegalArgumentException("Le fichier image n'existe pas");
        }
    }
    
    /**
     * Tronque un texte à une longueur maximale
     */
    private String truncate(String text, int maxLength) {
        if (text == null) return "";
        return text.length() > maxLength ? text.substring(0, maxLength) + "..." : text;
    }
}
