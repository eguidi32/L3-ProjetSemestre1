package com.bresil.entity;

import java.math.BigDecimal;
import java.time.LocalDateTime;
import java.util.Objects;

/**
 * Entité Zone de Livraison
 */
public class Zone {
    
    private Long idZone;
    private String nom;
    private BigDecimal fraisLivraison;
    private String quartiers;
    private Boolean archive;
    private LocalDateTime dateCreation;
    
    public Zone() {
        this.archive = false;
        this.dateCreation = LocalDateTime.now();
    }
    
    public Zone(String nom, BigDecimal fraisLivraison, String quartiers) {
        this();
        this.nom = nom;
        this.fraisLivraison = fraisLivraison;
        this.quartiers = quartiers;
    }
    
    // Getters et Setters
    public Long getIdZone() {
        return idZone;
    }
    
    public void setIdZone(Long idZone) {
        this.idZone = idZone;
    }
    
    public String getNom() {
        return nom;
    }
    
    public void setNom(String nom) {
        this.nom = nom;
    }
    
    public BigDecimal getFraisLivraison() {
        return fraisLivraison;
    }
    
    public void setFraisLivraison(BigDecimal fraisLivraison) {
        this.fraisLivraison = fraisLivraison;
    }
    
    public String getQuartiers() {
        return quartiers;
    }
    
    public void setQuartiers(String quartiers) {
        this.quartiers = quartiers;
    }
    
    public Boolean getArchive() {
        return archive;
    }
    
    public void setArchive(Boolean archive) {
        this.archive = archive;
    }
    
    public LocalDateTime getDateCreation() {
        return dateCreation;
    }
    
    public void setDateCreation(LocalDateTime dateCreation) {
        this.dateCreation = dateCreation;
    }
    
    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        Zone zone = (Zone) o;
        return Objects.equals(idZone, zone.idZone);
    }
    
    @Override
    public int hashCode() {
        return Objects.hash(idZone);
    }
    
    @Override
    public String toString() {
        return "Zone{" +
                "idZone=" + idZone +
                ", nom='" + nom + '\'' +
                ", fraisLivraison=" + fraisLivraison +
                ", quartiers='" + quartiers + '\'' +
                ", archive=" + archive +
                '}';
    }
}
