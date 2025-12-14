package com.bresil.config.database;

import java.io.IOException;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

/**
 * Configuration base de données
 */
public class DatabaseConfig {
    
    private static DatabaseConfig instance;
    private final Properties properties;
    
    // Constructeur privé (Singleton)
    private DatabaseConfig() {
        properties = new Properties();
        loadProperties();
    }
    
    /**
     * Récupère l'instance unique (Singleton)
     */
    public static synchronized DatabaseConfig getInstance() {
        if (instance == null) {
            instance = new DatabaseConfig();
        }
        return instance;
    }
    
    /**
     * Charge les propriétés depuis le fichier de configuration
     */
    private void loadProperties() {
        try (InputStream input = getClass().getClassLoader()
                .getResourceAsStream("application.properties")) {
            
            if (input == null) {
                throw new RuntimeException("Fichier application.properties introuvable");
            }
            
            properties.load(input);
            
            // Chargement du driver JDBC
            Class.forName(properties.getProperty("db.driver"));
            
        } catch (IOException | ClassNotFoundException e) {
            throw new RuntimeException("Erreur lors du chargement de la configuration: " + e.getMessage(), e);
        }
    }
    
    /**
     * Crée une nouvelle connexion à la base de données
     */
    public Connection getConnection() throws SQLException {
        return DriverManager.getConnection(
            properties.getProperty("db.url"),
            properties.getProperty("db.username"),
            properties.getProperty("db.password")
        );
    }
    
    /**
     * Récupère une propriété
     */
    public String getProperty(String key) {
        return properties.getProperty(key);
    }
    
    /**
     * Teste la connexion à la base de données
     */
    public boolean testConnection() {
        try (Connection conn = getConnection()) {
            return conn != null && !conn.isClosed();
        } catch (SQLException e) {
            System.err.println("Erreur de connexion: " + e.getMessage());
            return false;
        }
    }
}
