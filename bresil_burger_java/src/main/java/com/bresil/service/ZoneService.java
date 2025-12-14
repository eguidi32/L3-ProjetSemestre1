package com.bresil.service;

import com.bresil.entity.Zone;

import java.math.BigDecimal;
import java.util.List;

/**
 * Interface du service Zone
 */
public interface ZoneService {
    
    Long creerZone(String nom, BigDecimal fraisLivraison, String quartiers);
    
    Zone getZoneById(Long id);
    
    List<Zone> listerZones();
    
    void afficherZones();
    
    void modifierZone(Long id, String nom, BigDecimal fraisLivraison, String quartiers);
    
    void supprimerZone(Long id);
}
