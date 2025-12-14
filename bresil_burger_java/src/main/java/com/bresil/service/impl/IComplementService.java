package com.bresil.service.impl;

import com.bresil.config.database.CloudinaryConfig;
import com.bresil.config.factory.RepositoryFactory;
import com.bresil.entity.Complement;
import com.bresil.repository.ComplementRepository;
import com.bresil.service.ComplementService;

import java.io.File;
import java.math.BigDecimal;
import java.util.List;
import java.util.Optional;

/**
 * Implémentation du service Complement
 */
public class IComplementService implements ComplementService {
    
    private final ComplementRepository complementRepository;
    private final CloudinaryConfig cloudinaryConfig;
    
    public IComplementService() {
        this.complementRepository = RepositoryFactory.getInstance().getComplementRepository();
        this.cloudinaryConfig = CloudinaryConfig.getInstance();
    }
    
    @Override
    public Long creerComplement(String nom, BigDecimal prix, String categorie, File imageFile, String imageSource) {
        try {
            validateComplementData(nom, prix, categorie, imageFile, imageSource);
            
            // Upload de l'image sur Cloudinary (fichier local ou URL)
            String imageUrl;
            if (imageFile != null) {
                imageUrl = cloudinaryConfig.uploadImage(imageFile, "brasil-burger/complements");
            } else {
                imageUrl = cloudinaryConfig.uploadImageFromUrl(imageSource, "brasil-burger/complements");
            }
            
            Complement complement = new Complement(nom, prix, categorie, imageUrl);
            Long id = complementRepository.create(complement);
            
            System.out.println("Complément créé avec succès ! ID: " + id);
            return id;
            
        } catch (Exception e) {
            throw new RuntimeException("Erreur lors de la création du complément: " + e.getMessage(), e);
        }
    }
    
    @Override
    public Complement getComplementById(Long id) {
        if (id == null || id <= 0) {
            throw new IllegalArgumentException("L'ID du complément doit être valide");
        }
        
        Optional<Complement> complement = complementRepository.findById(id);
        if (complement.isEmpty()) {
            throw new RuntimeException("Complément non trouvé avec l'ID: " + id);
        }
        
        return complement.get();
    }
    
    @Override
    public List<Complement> listerComplements() {
        return complementRepository.findAll();
    }
    
    @Override
    public List<Complement> listerComplementsParCategorie(String categorie) {
        if (categorie == null || categorie.trim().isEmpty()) {
            throw new IllegalArgumentException("La catégorie ne peut pas être vide");
        }
        return complementRepository.findByCategorie(categorie.toUpperCase());
    }
    
    @Override
    public void afficherComplements() {
        List<Complement> complements = listerComplements();
        
        if (complements.isEmpty()) {
            System.out.println("\nAucun complément disponible");
            return;
        }
        
        System.out.println("\nLISTE DES COMPLÉMENTS");
        System.out.println("=".repeat(80));
        System.out.printf("%-5s %-30s %-15s %-20s%n", "ID", "Nom", "Prix (FCFA)", "Catégorie");
        System.out.println("-".repeat(80));
        
        for (Complement complement : complements) {
            System.out.printf("%-5d %-30s %-15s %-20s%n",
                complement.getIdComplement(),
                complement.getNom(),
                complement.getPrix(),
                complement.getCategorie());
        }
        
        System.out.println("=".repeat(80));
        System.out.println("Total: " + complements.size() + " complément(s)");
    }
    
    @Override
    public void modifierComplement(Long id, String nom, BigDecimal prix, String categorie, File nouvelleImage) {
        try {
            Complement complement = getComplementById(id);
            validateComplementData(nom, prix, categorie, nouvelleImage, complement.getImage());
            
            String imageUrl = complement.getImage();
            
            if (nouvelleImage != null && nouvelleImage.exists()) {
                String oldPublicId = cloudinaryConfig.extractPublicId(imageUrl);
                if (oldPublicId != null) {
                    cloudinaryConfig.deleteImage(oldPublicId);
                }
                imageUrl = cloudinaryConfig.uploadImage(nouvelleImage, "brasil-burger/complements");
            }
            
            complement.setNom(nom);
            complement.setPrix(prix);
            complement.setCategorie(categorie);
            complement.setImage(imageUrl);
            
            boolean success = complementRepository.update(complement);
            if (success) {
                System.out.println("Complément modifié avec succès !");
            } else {
                throw new RuntimeException("Échec de la modification du complément");
            }
            
        } catch (Exception e) {
            throw new RuntimeException("Erreur lors de la modification: " + e.getMessage(), e);
        }
    }
    
    @Override
    public void supprimerComplement(Long id) {
        getComplementById(id);
        boolean success = complementRepository.delete(id);
        
        if (success) {
            System.out.println("Complément archivé avec succès !");
        } else {
            throw new RuntimeException("Échec de la suppression du complément");
        }
    }
    
    private void validateComplementData(String nom, BigDecimal prix, String categorie, File imageFile, String imageSource) {
        if (nom == null || nom.trim().isEmpty()) {
            throw new IllegalArgumentException("Le nom du complément est obligatoire");
        }
        if (prix == null || prix.compareTo(BigDecimal.ZERO) <= 0) {
            throw new IllegalArgumentException("Le prix doit être supérieur à zéro");
        }
        if (categorie == null || categorie.trim().isEmpty()) {
            throw new IllegalArgumentException("La catégorie est obligatoire");
        }
        
        // Validation des catégories autorisées (selon le sujet: frites ou boissons)
        if (!categorie.equals("FRITES") && !categorie.equals("BOISSON")) {
            throw new IllegalArgumentException("Catégorie invalide. Valeurs autorisées: FRITES, BOISSON");
        }
        
        // Vérifier qu'on a soit un fichier soit une URL
        if (imageFile == null && (imageSource == null || imageSource.trim().isEmpty())) {
            throw new IllegalArgumentException("Une image est obligatoire (fichier ou URL)");
        }
        if (imageFile != null && !imageFile.exists()) {
            throw new IllegalArgumentException("Le fichier image n'existe pas");
        }
    }
}
