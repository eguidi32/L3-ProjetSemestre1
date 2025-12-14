package com.bresil.config.factory;


import com.bresil.repository.BurgerRepository;
import com.bresil.repository.ComplementRepository;
import com.bresil.repository.impl.IBurgerRepository;
import com.bresil.repository.impl.IComplementRepository;
import com.bresil.repository.MenuRepository;
import com.bresil.repository.impl.IMenuRepository;
/**
 * Factory des repositories
 */
public class RepositoryFactory {
    
    private static RepositoryFactory instance;
    private ComplementRepository complementRepository;
    private MenuRepository menuRepository;

    // Cache des instances de repositories (Singleton par type)
    private BurgerRepository burgerRepository;
    
    // Constructeur privé (Singleton)
    private RepositoryFactory() {
    }
    
    /**
     * Récupère l'instance unique de la factory
     */
    public static synchronized RepositoryFactory getInstance() {
        if (instance == null) {
            instance = new RepositoryFactory();
        }
        return instance;
    }
    
    /**
     * Récupère le repository Burger
     */
    public BurgerRepository getBurgerRepository() {
        if (burgerRepository == null) {
            burgerRepository = new IBurgerRepository();
        }
        return burgerRepository;
    }

    public ComplementRepository getComplementRepository() {
        if (complementRepository == null) {
            complementRepository = new IComplementRepository();
        }
        return complementRepository;
    }
    
    public MenuRepository getMenuRepository() {
        if (menuRepository == null) {
            menuRepository = new IMenuRepository();
        }
        return menuRepository;
    }

    

}
