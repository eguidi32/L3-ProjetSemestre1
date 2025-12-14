package com.bresil.repository;

import com.bresil.entity.Complement;

import java.util.List;

/**
 * Interface du repository Complement
 */
public interface ComplementRepository extends Repository<Complement, Long> {
    
    /**
     * Recherche les compléments par catégorie
     * @param categorie La catégorie (BOISSON, FRITES, etc.)
     * @return Liste des compléments de cette catégorie
     */
    List<Complement> findByCategorie(String categorie);
}
