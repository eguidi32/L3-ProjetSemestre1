package com.bresil.service.impl;

import com.bresil.config.database.CloudinaryConfig;
import com.bresil.config.factory.RepositoryFactory;
import com.bresil.entity.Burger;
import com.bresil.entity.Complement;
import com.bresil.entity.Menu;
import com.bresil.repository.BurgerRepository;
import com.bresil.repository.ComplementRepository;
import com.bresil.repository.MenuRepository;
import com.bresil.service.MenuService;

import java.io.File;
import java.math.BigDecimal;
import java.util.List;
import java.util.Optional;

/**
 * Implémentation du service Menu
 * Un menu est composé de : nom, image, burger, boisson, frites
 * Le prix est calculé automatiquement (somme des composants)
 */
public class IMenuService implements MenuService {
    
    private final MenuRepository menuRepository;
    private final BurgerRepository burgerRepository;
    private final ComplementRepository complementRepository;
    private final CloudinaryConfig cloudinaryConfig;
    
    public IMenuService() {
        this.menuRepository = RepositoryFactory.getInstance().getMenuRepository();
        this.burgerRepository = RepositoryFactory.getInstance().getBurgerRepository();
        this.complementRepository = RepositoryFactory.getInstance().getComplementRepository();
        this.cloudinaryConfig = CloudinaryConfig.getInstance();
    }
    
    @Override
    public Long creerMenuAvecComposition(String nom, File imageFile, String imageUrl, Long idBurger, Long idBoisson, Long idFrites) {
        try {
            // Validation du nom
            if (nom == null || nom.trim().isEmpty()) {
                throw new IllegalArgumentException("Le nom du menu est obligatoire");
            }
            
            // Validation des IDs
            if (idBurger == null || idBurger <= 0) {
                throw new IllegalArgumentException("ID du burger invalide");
            }
            if (idBoisson == null || idBoisson <= 0) {
                throw new IllegalArgumentException("ID de la boisson invalide");
            }
            if (idFrites == null || idFrites <= 0) {
                throw new IllegalArgumentException("ID des frites invalide");
            }
            
            // Récupération des entités
            Optional<Burger> burgerOpt = burgerRepository.findById(idBurger);
            if (burgerOpt.isEmpty()) {
                throw new RuntimeException("Burger non trouvé avec l'ID: " + idBurger);
            }
            
            Optional<Complement> boissonOpt = complementRepository.findById(idBoisson);
            if (boissonOpt.isEmpty()) {
                throw new RuntimeException("Boisson non trouvée avec l'ID: " + idBoisson);
            }
            
            Optional<Complement> fritesOpt = complementRepository.findById(idFrites);
            if (fritesOpt.isEmpty()) {
                throw new RuntimeException("Frites non trouvées avec l'ID: " + idFrites);
            }
            
            // Vérification des catégories
            Complement boisson = boissonOpt.get();
            Complement frites = fritesOpt.get();
            
            if (!"BOISSON".equals(boisson.getCategorie())) {
                throw new IllegalArgumentException("Le complément avec l'ID " + idBoisson + " n'est pas une boisson");
            }
            
            if (!"FRITES".equals(frites.getCategorie())) {
                throw new IllegalArgumentException("Le complément avec l'ID " + idFrites + " n'est pas des frites");
            }
            
            // Calcul du prix total (burger + boisson + frites)
            Burger burger = burgerOpt.get();
            BigDecimal prixTotal = burger.getPrix()
                .add(boisson.getPrix())
                .add(frites.getPrix());
            
            // Upload de l'image (fichier local ou URL)
            String uploadedImageUrl;
            if (imageFile != null && imageFile.exists()) {
                uploadedImageUrl = cloudinaryConfig.uploadImage(imageFile, "brasil-burger/menus");
            } else if (imageUrl != null && (imageUrl.startsWith("http://") || imageUrl.startsWith("https://"))) {
                uploadedImageUrl = cloudinaryConfig.uploadImageFromUrl(imageUrl, "brasil-burger/menus");
            } else {
                throw new IllegalArgumentException("Une image valide est obligatoire (fichier ou URL)");
            }
            
            // Création du menu - description générée automatiquement
            String description = burger.getNom() + " + " + boisson.getNom() + " + " + frites.getNom();
            Menu menu = new Menu(nom, prixTotal, description, uploadedImageUrl);
            menu.setIdBurger(idBurger);
            menu.setIdBoisson(idBoisson);
            menu.setIdFrites(idFrites);
            
            Long id = menuRepository.create(menu);
            
            System.out.println("\nMenu créé avec succès !");
            System.out.println("ID: " + id);
            System.out.println("Composition: " + description);
            System.out.println("Prix total: " + prixTotal + " FCFA");
            
            return id;
            
        } catch (Exception e) {
            throw new RuntimeException("Erreur lors de la création du menu: " + e.getMessage(), e);
        }
    }
    
    @Override
    public Menu getMenuById(Long id) {
        if (id == null || id <= 0) {
            throw new IllegalArgumentException("L'ID du menu doit être valide");
        }
        
        Optional<Menu> menu = menuRepository.findById(id);
        if (menu.isEmpty()) {
            throw new RuntimeException("Menu non trouvé avec l'ID: " + id);
        }
        
        return menu.get();
    }
    
    @Override
    public List<Menu> listerMenus() {
        return menuRepository.findAll();
    }
    
    @Override
    public void afficherMenus() {
        List<Menu> menus = listerMenus();
        
        if (menus.isEmpty()) {
            System.out.println("\nAucun menu disponible");
            return;
        }
        
        System.out.println("\nLISTE DES MENUS");
        System.out.println("=".repeat(90));
        System.out.printf("%-5s %-25s %-15s %-40s%n", "ID", "Nom", "Prix (FCFA)", "Composition");
        System.out.println("-".repeat(90));
        
        for (Menu menu : menus) {
            String composition = getCompositionString(menu);
            System.out.printf("%-5d %-25s %-15s %-40s%n",
                menu.getIdMenu(),
                truncate(menu.getNom(), 23),
                menu.getPrix(),
                truncate(composition, 38));
        }
        
        System.out.println("=".repeat(90));
        System.out.println("Total: " + menus.size() + " menu(s)");
    }
    
    @Override
    public void modifierMenu(Long id, String nom, Long idBurger, Long idBoisson, Long idFrites, File nouvelleImage, String imageUrl) {
        try {
            Menu menu = getMenuById(id);
            
            // Validation du nom
            if (nom == null || nom.trim().isEmpty()) {
                throw new IllegalArgumentException("Le nom du menu est obligatoire");
            }
            
            // Récupération et validation des composants
            Optional<Burger> burgerOpt = burgerRepository.findById(idBurger);
            if (burgerOpt.isEmpty()) {
                throw new RuntimeException("Burger non trouvé avec l'ID: " + idBurger);
            }
            
            Optional<Complement> boissonOpt = complementRepository.findById(idBoisson);
            if (boissonOpt.isEmpty()) {
                throw new RuntimeException("Boisson non trouvée avec l'ID: " + idBoisson);
            }
            
            Optional<Complement> fritesOpt = complementRepository.findById(idFrites);
            if (fritesOpt.isEmpty()) {
                throw new RuntimeException("Frites non trouvées avec l'ID: " + idFrites);
            }
            
            Burger burger = burgerOpt.get();
            Complement boisson = boissonOpt.get();
            Complement frites = fritesOpt.get();
            
            // Vérification des catégories
            if (!"BOISSON".equals(boisson.getCategorie())) {
                throw new IllegalArgumentException("Le complément avec l'ID " + idBoisson + " n'est pas une boisson");
            }
            if (!"FRITES".equals(frites.getCategorie())) {
                throw new IllegalArgumentException("Le complément avec l'ID " + idFrites + " n'est pas des frites");
            }
            
            // Recalcul du prix
            BigDecimal prixTotal = burger.getPrix()
                .add(boisson.getPrix())
                .add(frites.getPrix());
            
            // Gestion de l'image
            String finalImageUrl = menu.getImage();
            if (nouvelleImage != null && nouvelleImage.exists()) {
                // Supprimer l'ancienne image
                String oldPublicId = cloudinaryConfig.extractPublicId(finalImageUrl);
                if (oldPublicId != null) {
                    cloudinaryConfig.deleteImage(oldPublicId);
                }
                finalImageUrl = cloudinaryConfig.uploadImage(nouvelleImage, "brasil-burger/menus");
            } else if (imageUrl != null && (imageUrl.startsWith("http://") || imageUrl.startsWith("https://"))) {
                String oldPublicId = cloudinaryConfig.extractPublicId(finalImageUrl);
                if (oldPublicId != null) {
                    cloudinaryConfig.deleteImage(oldPublicId);
                }
                finalImageUrl = cloudinaryConfig.uploadImageFromUrl(imageUrl, "brasil-burger/menus");
            }
            
            // Mise à jour du menu
            menu.setNom(nom);
            menu.setPrix(prixTotal);
            menu.setDescription(burger.getNom() + " + " + boisson.getNom() + " + " + frites.getNom());
            menu.setImage(finalImageUrl);
            menu.setIdBurger(idBurger);
            menu.setIdBoisson(idBoisson);
            menu.setIdFrites(idFrites);
            
            boolean success = menuRepository.update(menu);
            if (success) {
                System.out.println("Menu modifié avec succès !");
                System.out.println("Nouveau prix: " + prixTotal + " FCFA");
            } else {
                throw new RuntimeException("Échec de la modification du menu");
            }
            
        } catch (Exception e) {
            throw new RuntimeException("Erreur lors de la modification: " + e.getMessage(), e);
        }
    }
    
    @Override
    public void supprimerMenu(Long id) {
        getMenuById(id);
        boolean success = menuRepository.delete(id);
        
        if (success) {
            System.out.println("Menu archivé avec succès !");
        } else {
            throw new RuntimeException("Échec de la suppression du menu");
        }
    }
    
    /**
     * Génère une chaîne décrivant la composition du menu
     */
    private String getCompositionString(Menu menu) {
        StringBuilder sb = new StringBuilder();
        
        if (menu.getIdBurger() != null) {
            burgerRepository.findById(menu.getIdBurger())
                .ifPresent(b -> sb.append(b.getNom()));
        }
        
        if (menu.getIdBoisson() != null) {
            complementRepository.findById(menu.getIdBoisson())
                .ifPresent(c -> sb.append(" + ").append(c.getNom()));
        }
        
        if (menu.getIdFrites() != null) {
            complementRepository.findById(menu.getIdFrites())
                .ifPresent(c -> sb.append(" + ").append(c.getNom()));
        }
        
        return sb.length() > 0 ? sb.toString() : menu.getDescription();
    }
    
    private String truncate(String text, int maxLength) {
        if (text == null) return "";
        return text.length() > maxLength ? text.substring(0, maxLength) + "..." : text;
    }
}
