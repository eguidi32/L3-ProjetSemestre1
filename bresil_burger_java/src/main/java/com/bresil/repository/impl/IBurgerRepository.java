package com.bresil.repository.impl;

import com.bresil.config.database.DatabaseConfig;
import com.bresil.entity.Burger;
import com.bresil.repository.BurgerRepository;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

/**
 * Repository Burger
 */
public class IBurgerRepository implements BurgerRepository {
    
    private final DatabaseConfig dbConfig;
    
    public IBurgerRepository() {
        this.dbConfig = DatabaseConfig.getInstance();
    }
    
    @Override
    public Long create(Burger burger) {
        String sql = "INSERT INTO burger (nom, prix, description, ingredients, image) VALUES (?, ?, ?, ?, ?) RETURNING id_burger";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setString(1, burger.getNom());
            stmt.setBigDecimal(2, burger.getPrix());
            stmt.setString(3, burger.getDescription());
            stmt.setString(4, burger.getIngredients());
            stmt.setString(5, burger.getImage());
            
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                return rs.getLong("id_burger");
            }
            
            throw new SQLException("Échec de la création du burger, aucun ID obtenu");
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la création du burger: " + e.getMessage(), e);
        }
    }
    
    @Override
    public Optional<Burger> findById(Long id) {
        String sql = "SELECT * FROM burger WHERE id_burger = ? AND archive = false";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setLong(1, id);
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next()) {
                return Optional.of(mapResultSetToBurger(rs));
            }
            
            return Optional.empty();
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la recherche du burger: " + e.getMessage(), e);
        }
    }
    
    @Override
    public List<Burger> findAll() {
        List<Burger> burgers = new ArrayList<>();
        String sql = "SELECT * FROM burger WHERE archive = false ORDER BY date_creation DESC";
        
        try (Connection conn = dbConfig.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            
            while (rs.next()) {
                burgers.add(mapResultSetToBurger(rs));
            }
            
            return burgers;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la récupération des burgers: " + e.getMessage(), e);
        }
    }
    
    @Override
    public boolean update(Burger burger) {
        String sql = "UPDATE burger SET nom = ?, prix = ?, description = ?, ingredients = ?, image = ? WHERE id_burger = ?";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setString(1, burger.getNom());
            stmt.setBigDecimal(2, burger.getPrix());
            stmt.setString(3, burger.getDescription());
            stmt.setString(4, burger.getIngredients());
            stmt.setString(5, burger.getImage());
            stmt.setLong(6, burger.getIdBurger());
            
            return stmt.executeUpdate() > 0;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la mise à jour du burger: " + e.getMessage(), e);
        }
    }
    
    @Override
    public boolean delete(Long id) {
        String sql = "UPDATE burger SET archive = true WHERE id_burger = ?";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la suppression du burger: " + e.getMessage(), e);
        }
    }
    
    /**
     * Méthode privée pour mapper un ResultSet vers un objet Burger
     * Principe: DRY (Don't Repeat Yourself)
     */
    private Burger mapResultSetToBurger(ResultSet rs) throws SQLException {
        Burger burger = new Burger();
        burger.setIdBurger(rs.getLong("id_burger"));
        burger.setNom(rs.getString("nom"));
        burger.setPrix(rs.getBigDecimal("prix"));
        burger.setDescription(rs.getString("description"));
        burger.setIngredients(rs.getString("ingredients"));
        burger.setImage(rs.getString("image"));
        burger.setArchive(rs.getBoolean("archive"));
        
        Timestamp timestamp = rs.getTimestamp("date_creation");
        if (timestamp != null) {
            burger.setDateCreation(timestamp.toLocalDateTime());
        }
        
        return burger;
    }
}
