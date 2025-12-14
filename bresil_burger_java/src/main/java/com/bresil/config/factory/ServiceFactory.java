package com.bresil.config.factory;

import com.bresil.service.BurgerService;
import com.bresil.service.ComplementService;
import com.bresil.service.impl.IBurgerService;
import com.bresil.service.impl.IComplementService;
import com.bresil.service.MenuService;
import com.bresil.service.ZoneService;
import com.bresil.service.impl.IMenuService;
import com.bresil.service.impl.IZoneService;

public class ServiceFactory {
    private static ServiceFactory instance;
    private BurgerService burgerService;
    private ComplementService complementService;
    private MenuService menuService;
    private ZoneService zoneService;

    // Constructeur privé (Singleton)
    private ServiceFactory() {
    }

    /**
     * Récupère l'instance unique de la factory
     */
    public static synchronized ServiceFactory getInstance() {
        if (instance == null) {
            instance = new ServiceFactory();
        }
        return instance;
    }

    /**
     * Récupère le service Burger
     */
    public BurgerService getBurgerService() {
        if (burgerService == null) {
            burgerService = new IBurgerService();
        }
        return burgerService;
    }

    public ComplementService getComplementService() {
        if (complementService == null) {
            complementService = new IComplementService();
        }
        return complementService;
    }

    public MenuService getMenuService() {
        if (menuService == null) {
            menuService = new IMenuService();
        }
        return menuService;
    }

    public ZoneService getZoneService() {
        if (zoneService == null) {
            zoneService = new IZoneService();
        }
        return zoneService;
    }
}
