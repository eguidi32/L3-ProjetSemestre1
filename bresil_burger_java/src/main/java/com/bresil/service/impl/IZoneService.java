package com.bresil.service.impl;

import com.bresil.config.factory.RepositoryFactory;
import com.bresil.entity.Zone;
import com.bresil.repository.ZoneRepository;
import com.bresil.service.ZoneService;

import java.math.BigDecimal;
import java.util.List;
import java.util.Optional;

/**
 * Implémentation du service Zone
 */
public class IZoneService implements ZoneService {
    
    private final ZoneRepository zoneRepository;
    
    public IZoneService() {
        this.zoneRepository = RepositoryFactory.getInstance().getZoneRepository();
    }
    
    @Override
    public Long creerZone(String nom, BigDecimal fraisLivraison, String quartiers) {
        try {
            validateZoneData(nom, fraisLivraison, quartiers);
            
            Zone zone = new Zone(nom, fraisLivraison, quartiers);
            Long id = zoneRepository.create(zone);
            
            System.out.println("Zone créée avec succès ! ID: " + id);
            return id;
            
        } catch (Exception e) {
            throw new RuntimeException("Erreur lors de la création de la zone: " + e.getMessage(), e);
        }
    }
    
    @Override
    public Zone getZoneById(Long id) {
        if (id == null || id <= 0) {
            throw new IllegalArgumentException("L'ID de la zone doit être valide");
        }
        
        Optional<Zone> zone = zoneRepository.findById(id);
        if (zone.isEmpty()) {
            throw new RuntimeException("Zone non trouvée avec l'ID: " + id);
        }
        
        return zone.get();
    }
    
    @Override
    public List<Zone> listerZones() {
        return zoneRepository.findAll();
    }
    
    @Override
    public void afficherZones() {
        List<Zone> zones = listerZones();
        
        if (zones.isEmpty()) {
            System.out.println("\nAucune zone disponible");
            return;
        }
        
        System.out.println("\nLISTE DES ZONES DE LIVRAISON");
        System.out.println("=".repeat(100));
        System.out.printf("%-5s %-25s %-15s %-50s%n", "ID", "Nom", "Frais (FCFA)", "Quartiers");
        System.out.println("-".repeat(100));
        
        for (Zone zone : zones) {
            String quartiers = zone.getQuartiers() != null ? zone.getQuartiers() : "Non spécifié";
            if (quartiers.length() > 47) quartiers = quartiers.substring(0, 47) + "...";
            System.out.printf("%-5d %-25s %-15s %-50s%n",
                zone.getIdZone(),
                zone.getNom(),
                zone.getFraisLivraison(),
                quartiers);
        }
        
        System.out.println("=".repeat(60));
        System.out.println("Total: " + zones.size() + " zone(s)");
    }
    
    @Override
    public void modifierZone(Long id, String nom, BigDecimal fraisLivraison, String quartiers) {
        try {
            Zone zone = getZoneById(id);
            validateZoneData(nom, fraisLivraison, quartiers);
            
            zone.setNom(nom);
            zone.setFraisLivraison(fraisLivraison);
            zone.setQuartiers(quartiers);
            
            boolean success = zoneRepository.update(zone);
            if (success) {
                System.out.println("Zone modifiée avec succès !");
            } else {
                throw new RuntimeException("Échec de la modification de la zone");
            }
            
        } catch (Exception e) {
            throw new RuntimeException("Erreur lors de la modification: " + e.getMessage(), e);
        }
    }
    
    @Override
    public void supprimerZone(Long id) {
        getZoneById(id);
        boolean success = zoneRepository.delete(id);
        
        if (success) {
            System.out.println("Zone archivée avec succès !");
        } else {
            throw new RuntimeException("Échec de la suppression de la zone");
        }
    }
    
    private void validateZoneData(String nom, BigDecimal fraisLivraison, String quartiers) {
        if (nom == null || nom.trim().isEmpty()) {
            throw new IllegalArgumentException("Le nom de la zone est obligatoire");
        }
        if (fraisLivraison == null || fraisLivraison.compareTo(BigDecimal.ZERO) < 0) {
            throw new IllegalArgumentException("Les frais de livraison doivent être supérieurs ou égaux à zéro");
        }
        if (quartiers == null || quartiers.trim().isEmpty()) {
            throw new IllegalArgumentException("Les quartiers sont obligatoires");
        }
    }
}
