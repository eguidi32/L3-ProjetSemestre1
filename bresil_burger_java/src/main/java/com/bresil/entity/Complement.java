package com.bresil.entity;

import java.math.BigDecimal;
import java.time.LocalDateTime;
import java.util.Objects;

/**
 * Entité Complement
 */
public class Complement {
    
    private Long idComplement;
    private String nom;
    private BigDecimal prix;
    private String categorie;
    private String image;
    private Boolean archive;
    private LocalDateTime dateCreation;
    
    public Complement() {
        this.archive = false;
        this.dateCreation = LocalDateTime.now();
    }
    
    public Complement(String nom, BigDecimal prix, String categorie, String image) {
        this();
        this.nom = nom;
        this.prix = prix;
        this.categorie = categorie;
        this.image = image;
    }
    
    // Getters et Setters
    public Long getIdComplement() {
        return idComplement;
    }
    
    public void setIdComplement(Long idComplement) {
        this.idComplement = idComplement;
    }
    
    public String getNom() {
        return nom;
    }
    
    public void setNom(String nom) {
        this.nom = nom;
    }
    
    public BigDecimal getPrix() {
        return prix;
    }
    
    public void setPrix(BigDecimal prix) {
        this.prix = prix;
    }
    
    public String getCategorie() {
        return categorie;
    }
    
    public void setCategorie(String categorie) {
        this.categorie = categorie;
    }
    
    public String getImage() {
        return image;
    }
    
    public void setImage(String image) {
        this.image = image;
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
        Complement that = (Complement) o;
        return Objects.equals(idComplement, that.idComplement);
    }
    
    @Override
    public int hashCode() {
        return Objects.hash(idComplement);
    }
    
    @Override
    public String toString() {
        return "Complement{" +
                "idComplement=" + idComplement +
                ", nom='" + nom + '\'' +
                ", prix=" + prix +
                ", categorie='" + categorie + '\'' +
                ", archive=" + archive +
                '}';
    }
}
