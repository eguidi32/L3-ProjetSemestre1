package com.bresil.repository.impl;

import com.bresil.config.database.DatabaseConfig;
import com.bresil.entity.Zone;
import com.bresil.repository.ZoneRepository;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

/**
 * Implémentation du repository Zone
 */
public class IZoneRepository implements ZoneRepository {
    
    private final DatabaseConfig dbConfig;
    
    public IZoneRepository() {
        this.dbConfig = DatabaseConfig.getInstance();
    }
    
    @Override
    public Long create(Zone zone) {
        String sql = "INSERT INTO zone (nom, prix_livraison, quartiers) VALUES (?, ?, ?) RETURNING id_zone";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setString(1, zone.getNom());
            stmt.setBigDecimal(2, zone.getFraisLivraison());
            stmt.setString(3, zone.getQuartiers());
            
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                return rs.getLong("id_zone");
            }
            
            throw new SQLException("Échec de la création de la zone");
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la création de la zone: " + e.getMessage(), e);
        }
    }
    
    @Override
    public Optional<Zone> findById(Long id) {
        String sql = "SELECT * FROM zone WHERE id_zone = ?";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setLong(1, id);
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next()) {
                return Optional.of(mapResultSetToZone(rs));
            }
            
            return Optional.empty();
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la recherche de la zone: " + e.getMessage(), e);
        }
    }
    
    @Override
    public List<Zone> findAll() {
        List<Zone> zones = new ArrayList<>();
        String sql = "SELECT * FROM zone ORDER BY nom";
        
        try (Connection conn = dbConfig.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            
            while (rs.next()) {
                zones.add(mapResultSetToZone(rs));
            }
            
            return zones;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la récupération des zones: " + e.getMessage(), e);
        }
    }
    
    @Override
    public boolean update(Zone zone) {
        String sql = "UPDATE zone SET nom = ?, prix_livraison = ?, quartiers = ? WHERE id_zone = ?";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setString(1, zone.getNom());
            stmt.setBigDecimal(2, zone.getFraisLivraison());
            stmt.setString(3, zone.getQuartiers());
            stmt.setLong(4, zone.getIdZone());
            
            return stmt.executeUpdate() > 0;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la mise à jour de la zone: " + e.getMessage(), e);
        }
    }
    
    @Override
    public boolean delete(Long id) {
        String sql = "DELETE FROM zone WHERE id_zone = ?";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la suppression de la zone: " + e.getMessage(), e);
        }
    }
    
    private Zone mapResultSetToZone(ResultSet rs) throws SQLException {
        Zone zone = new Zone();
        zone.setIdZone(rs.getLong("id_zone"));
        zone.setNom(rs.getString("nom"));
        zone.setFraisLivraison(rs.getBigDecimal("prix_livraison"));
        zone.setQuartiers(rs.getString("quartiers"));
        return zone;
    }
}
