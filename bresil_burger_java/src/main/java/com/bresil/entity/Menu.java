package com.bresil.entity;

import java.math.BigDecimal;
import java.time.LocalDateTime;
import java.util.Objects;

/**
 * Entité Menu
 * Composition: Burger + Boisson + Frites (selon le sujet)
 * Le prix du menu est la somme des prix qui le composent
 */
public class Menu {
    
    private Long idMenu;
    private String nom;
    private BigDecimal prix;
    private String description;
    private String image;
    private Boolean archive;
    private LocalDateTime dateCreation;
    
    // Composition du menu (Burger + Boisson + Frites)
    private Long idBurger;
    private Long idBoisson;  // Complément de type BOISSON
    private Long idFrites;   // Complément de type FRITES
    
    public Menu() {
        this.archive = false;
        this.dateCreation = LocalDateTime.now();
    }
    
    public Menu(String nom, BigDecimal prix, String description, String image) {
        this();
        this.nom = nom;
        this.prix = prix;
        this.description = description;
        this.image = image;
    }
    
    public Menu(String nom, String description, String image, Long idBurger, Long idBoisson, Long idFrites) {
        this();
        this.nom = nom;
        this.description = description;
        this.image = image;
        this.idBurger = idBurger;
        this.idBoisson = idBoisson;
        this.idFrites = idFrites;
    }
    
    // Getters et Setters
    public Long getIdMenu() {
        return idMenu;
    }
    
    public void setIdMenu(Long idMenu) {
        this.idMenu = idMenu;
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
    
    public Long getIdBurger() {
        return idBurger;
    }
    
    public void setIdBurger(Long idBurger) {
        this.idBurger = idBurger;
    }
    
    public Long getIdBoisson() {
        return idBoisson;
    }
    
    public void setIdBoisson(Long idBoisson) {
        this.idBoisson = idBoisson;
    }
    
    public Long getIdFrites() {
        return idFrites;
    }
    
    public void setIdFrites(Long idFrites) {
        this.idFrites = idFrites;
    }
    
    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        Menu menu = (Menu) o;
        return Objects.equals(idMenu, menu.idMenu);
    }
    
    @Override
    public int hashCode() {
        return Objects.hash(idMenu);
    }
    
    @Override
    public String toString() {
        return "Menu{" +
                "idMenu=" + idMenu +
                ", nom='" + nom + '\'' +
                ", prix=" + prix +
                ", archive=" + archive +
                '}';
    }
}
