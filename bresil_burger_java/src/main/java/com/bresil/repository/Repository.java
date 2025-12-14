package com.bresil.repository;

import java.util.List;
import java.util.Optional;

/**
 * Interface générique CRUD
 */
public interface Repository<T, ID> {
    
    /**
     * Crée une nouvelle entité
     * @param entity L'entité à créer
     * @return L'ID généré
     */
    ID create(T entity);
    
    /**
     * Récupère une entité par son ID
     * @param id L'identifiant
     * @return Optional contenant l'entité si trouvée
     */
    Optional<T> findById(ID id);
    
    /**
     * Récupère toutes les entités non archivées
     * @return Liste des entités
     */
    List<T> findAll();
    
    /**
     * Met à jour une entité
     * @param entity L'entité à mettre à jour
     * @return true si succès
     */
    boolean update(T entity);
    
    /**
     * Archive une entité (soft delete)
     * @param id L'identifiant
     * @return true si succès
     */
    boolean delete(ID id);
}
