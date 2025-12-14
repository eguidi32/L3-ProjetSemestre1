package com.bresil.service;

import com.bresil.entity.Menu;

import java.io.File;
import java.util.List;

/**
 * Service Menu
 */
public interface MenuService {
    
    /**
     * Crée un menu avec composition complète
     * Le prix est calculé automatiquement
     */
    Long creerMenuAvecComposition(String nom, File imageFile, String imageUrl, Long idBurger, Long idBoisson, Long idFrites);
    
    Menu getMenuById(Long id);
    
    List<Menu> listerMenus();
    
    void afficherMenus();
    
    void modifierMenu(Long id, String nom, Long idBurger, Long idBoisson, Long idFrites, File nouvelleImage, String imageUrl);
    
    void supprimerMenu(Long id);
}
