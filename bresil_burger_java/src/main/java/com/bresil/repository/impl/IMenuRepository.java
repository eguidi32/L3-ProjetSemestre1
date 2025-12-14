package com.bresil.repository.impl;

import com.bresil.config.database.DatabaseConfig;
import com.bresil.entity.Menu;
import com.bresil.repository.MenuRepository;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

/**
 * Implémentation du repository Menu
 */
public class IMenuRepository implements MenuRepository {
    
    private final DatabaseConfig dbConfig;
    
    public IMenuRepository() {
        this.dbConfig = DatabaseConfig.getInstance();
    }
    
    @Override
    public Long create(Menu menu) {
        String sql = "INSERT INTO menu (nom, prix, description, image, id_burger, id_boisson, id_frites) VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING id_menu";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setString(1, menu.getNom());
            stmt.setBigDecimal(2, menu.getPrix());
            stmt.setString(3, menu.getDescription());
            stmt.setString(4, menu.getImage());
            
            // Gestion des valeurs null pour les compositions
            if (menu.getIdBurger() != null) {
                stmt.setLong(5, menu.getIdBurger());
            } else {
                stmt.setNull(5, Types.BIGINT);
            }
            
            if (menu.getIdBoisson() != null) {
                stmt.setLong(6, menu.getIdBoisson());
            } else {
                stmt.setNull(6, Types.BIGINT);
            }
            
            if (menu.getIdFrites() != null) {
                stmt.setLong(7, menu.getIdFrites());
            } else {
                stmt.setNull(7, Types.BIGINT);
            }
            
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                return rs.getLong("id_menu");
            }
            
            throw new SQLException("Échec de la création du menu");
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la création du menu: " + e.getMessage(), e);
        }
    }
    
    @Override
    public Optional<Menu> findById(Long id) {
        String sql = "SELECT * FROM menu WHERE id_menu = ? AND archive = false";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setLong(1, id);
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next()) {
                return Optional.of(mapResultSetToMenu(rs));
            }
            
            return Optional.empty();
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la recherche du menu: " + e.getMessage(), e);
        }
    }
    
    @Override
    public List<Menu> findAll() {
        List<Menu> menus = new ArrayList<>();
        String sql = "SELECT * FROM menu WHERE archive = false ORDER BY date_creation DESC";
        
        try (Connection conn = dbConfig.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            
            while (rs.next()) {
                menus.add(mapResultSetToMenu(rs));
            }
            
            return menus;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la récupération des menus: " + e.getMessage(), e);
        }
    }
    
    @Override
    public boolean update(Menu menu) {
        String sql = "UPDATE menu SET nom = ?, prix = ?, description = ?, image = ?, id_burger = ?, id_boisson = ?, id_frites = ? WHERE id_menu = ?";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setString(1, menu.getNom());
            stmt.setBigDecimal(2, menu.getPrix());
            stmt.setString(3, menu.getDescription());
            stmt.setString(4, menu.getImage());
            
            // Gestion des valeurs null pour les compositions
            if (menu.getIdBurger() != null) {
                stmt.setLong(5, menu.getIdBurger());
            } else {
                stmt.setNull(5, Types.BIGINT);
            }
            
            if (menu.getIdBoisson() != null) {
                stmt.setLong(6, menu.getIdBoisson());
            } else {
                stmt.setNull(6, Types.BIGINT);
            }
            
            if (menu.getIdFrites() != null) {
                stmt.setLong(7, menu.getIdFrites());
            } else {
                stmt.setNull(7, Types.BIGINT);
            }
            
            stmt.setLong(8, menu.getIdMenu());
            
            return stmt.executeUpdate() > 0;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la mise à jour du menu: " + e.getMessage(), e);
        }
    }
    
    @Override
    public boolean delete(Long id) {
        String sql = "UPDATE menu SET archive = true WHERE id_menu = ?";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la suppression du menu: " + e.getMessage(), e);
        }
    }
    
    private Menu mapResultSetToMenu(ResultSet rs) throws SQLException {
        Menu menu = new Menu();
        menu.setIdMenu(rs.getLong("id_menu"));
        menu.setNom(rs.getString("nom"));
        menu.setPrix(rs.getBigDecimal("prix"));
        menu.setDescription(rs.getString("description"));
        menu.setImage(rs.getString("image"));
        menu.setArchive(rs.getBoolean("archive"));
        
        // Gestion des valeurs null pour les IDs de composition
        long idBurger = rs.getLong("id_burger");
        menu.setIdBurger(rs.wasNull() ? null : idBurger);
        
        long idBoisson = rs.getLong("id_boisson");
        menu.setIdBoisson(rs.wasNull() ? null : idBoisson);
        
        long idFrites = rs.getLong("id_frites");
        menu.setIdFrites(rs.wasNull() ? null : idFrites);
        
        Timestamp timestamp = rs.getTimestamp("date_creation");
        if (timestamp != null) {
            menu.setDateCreation(timestamp.toLocalDateTime());
        }
        
        return menu;
    }
}
