package com.bresil.repository.impl;

import com.bresil.config.database.DatabaseConfig;
import com.bresil.entity.Complement;
import com.bresil.repository.ComplementRepository;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

/**
 * Implémentation du repository Complement
 */
public class IComplementRepository implements ComplementRepository {
    
    private final DatabaseConfig dbConfig;
    
    public IComplementRepository() {
        this.dbConfig = DatabaseConfig.getInstance();
    }
    
    @Override
    public Long create(Complement complement) {
        String sql = "INSERT INTO complement (nom, prix, type_complement, image) VALUES (?, ?, ?, ?) RETURNING id_complement";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setString(1, complement.getNom());
            stmt.setBigDecimal(2, complement.getPrix());
            stmt.setString(3, complement.getCategorie());
            stmt.setString(4, complement.getImage());
            
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                return rs.getLong("id_complement");
            }
            
            throw new SQLException("Échec de la création du complément");
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la création du complément: " + e.getMessage(), e);
        }
    }
    
    @Override
    public Optional<Complement> findById(Long id) {
        String sql = "SELECT * FROM complement WHERE id_complement = ? AND archive = false";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setLong(1, id);
            ResultSet rs = stmt.executeQuery();
            
            if (rs.next()) {
                return Optional.of(mapResultSetToComplement(rs));
            }
            
            return Optional.empty();
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la recherche du complément: " + e.getMessage(), e);
        }
    }
    
    @Override
    public List<Complement> findAll() {
        List<Complement> complements = new ArrayList<>();
        String sql = "SELECT * FROM complement WHERE archive = false ORDER BY type_complement, nom";
        
        try (Connection conn = dbConfig.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            
            while (rs.next()) {
                complements.add(mapResultSetToComplement(rs));
            }
            
            return complements;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la récupération des compléments: " + e.getMessage(), e);
        }
    }
    
    @Override
    public boolean update(Complement complement) {
        String sql = "UPDATE complement SET nom = ?, prix = ?, type_complement = ?, image = ? WHERE id_complement = ?";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setString(1, complement.getNom());
            stmt.setBigDecimal(2, complement.getPrix());
            stmt.setString(3, complement.getCategorie());
            stmt.setString(4, complement.getImage());
            stmt.setLong(5, complement.getIdComplement());
            
            return stmt.executeUpdate() > 0;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la mise à jour du complément: " + e.getMessage(), e);
        }
    }
    
    @Override
    public boolean delete(Long id) {
        String sql = "UPDATE complement SET archive = true WHERE id_complement = ?";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la suppression du complément: " + e.getMessage(), e);
        }
    }
    
    @Override
    public List<Complement> findByCategorie(String categorie) {
        List<Complement> complements = new ArrayList<>();
        String sql = "SELECT * FROM complement WHERE type_complement = ? AND archive = false ORDER BY nom";
        
        try (Connection conn = dbConfig.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            
            stmt.setString(1, categorie);
            ResultSet rs = stmt.executeQuery();
            
            while (rs.next()) {
                complements.add(mapResultSetToComplement(rs));
            }
            
            return complements;
            
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la recherche par catégorie: " + e.getMessage(), e);
        }
    }
    
    private Complement mapResultSetToComplement(ResultSet rs) throws SQLException {
        Complement complement = new Complement();
        complement.setIdComplement(rs.getLong("id_complement"));
        complement.setNom(rs.getString("nom"));
        complement.setPrix(rs.getBigDecimal("prix"));
        complement.setCategorie(rs.getString("type_complement"));
        complement.setImage(rs.getString("image"));
        complement.setArchive(rs.getBoolean("archive"));
        
        Timestamp timestamp = rs.getTimestamp("date_creation");
        if (timestamp != null) {
            complement.setDateCreation(timestamp.toLocalDateTime());
        }
        
        return complement;
    }
}
