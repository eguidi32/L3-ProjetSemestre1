package com.bresil.entity;

import java.math.BigDecimal;
import java.time.LocalDateTime;
import java.util.Objects;

/**
 * Entité Burger
 */
public class Burger {
    
    private Long idBurger;
    private String nom;
    private BigDecimal prix;
    private String description;
    private String ingredients;
    private String image;
    private Boolean archive;
    private LocalDateTime dateCreation;
    
    // Constructeur par défaut
    public Burger() {
        this.archive = false;
        this.dateCreation = LocalDateTime.now();
    }
    
    // Constructeur avec paramètres essentiels
    public Burger(String nom, BigDecimal prix, String description, String ingredients, String image) {
        this();
        this.nom = nom;
        this.prix = prix;
        this.description = description;
        this.ingredients = ingredients;
        this.image = image;
    }
    
    // Getters et Setters
    public Long getIdBurger() {
        return idBurger;
    }
    
    public void setIdBurger(Long idBurger) {
        this.idBurger = idBurger;
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
    
    public String getDescription() {
        return description;
    }
    
    public void setDescription(String description) {
        this.description = description;
    }
    
    public String getIngredients() {
        return ingredients;
    }
    
    public void setIngredients(String ingredients) {
        this.ingredients = ingredients;
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
    
    // Méthodes utilitaires
    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        Burger burger = (Burger) o;
        return Objects.equals(idBurger, burger.idBurger);
    }
    
    @Override
    public int hashCode() {
        return Objects.hash(idBurger);
    }
    
    @Override
    public String toString() {
        return "Burger{" +
                "idBurger=" + idBurger +
                ", nom='" + nom + '\'' +
                ", prix=" + prix +
                ", archive=" + archive +
                '}';
    }
}
