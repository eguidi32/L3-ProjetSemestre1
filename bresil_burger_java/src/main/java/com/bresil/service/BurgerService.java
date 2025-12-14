package com.bresil.service;

import com.bresil.entity.Burger;

import java.io.File;
import java.math.BigDecimal;
import java.util.List;

/**
 * Interface service Burger
 */
public interface BurgerService {
    
    /**
     * Crée un nouveau burger avec upload d'image (fichier ou URL)
     */
    Long creerBurger(String nom, BigDecimal prix, String description, String ingredients, File imageFile, String imageUrl);
    
    /**
     * Récupère un burger par son ID
     */
    Burger getBurgerById(Long id);
    
    /**
     * Récupère tous les burgers
     */
    List<Burger> listerBurgers();
    
    /**
     * Affiche la liste des burgers de manière formatée
     */
    void afficherBurgers();
    
    /**
     * Modifie un burger existant
     */
    void modifierBurger(Long id, String nom, BigDecimal prix, String description, 
                        String ingredients, File nouvelleImage);
    
    /**
     * Archive un burger (soft delete)
     */
    void supprimerBurger(Long id);
}
