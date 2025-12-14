package com.bresil.service;

import com.bresil.entity.Complement;

import java.io.File;
import java.math.BigDecimal;
import java.util.List;

/**
 * Interface du service Complement
 */
public interface ComplementService {
    
    Long creerComplement(String nom, BigDecimal prix, String categorie, File imageFile, String imageUrl);
    
    Complement getComplementById(Long id);
    
    List<Complement> listerComplements();
    
    List<Complement> listerComplementsParCategorie(String categorie);
    
    void afficherComplements();
    
    void modifierComplement(Long id, String nom, BigDecimal prix, String categorie, File nouvelleImage);
    
    void supprimerComplement(Long id);
}
