package com.bresil.config.database;

import com.cloudinary.Cloudinary;
import com.cloudinary.utils.ObjectUtils;

import java.io.File;
import java.util.Map;

/**
 * Configuration Cloudinary
 */
public class CloudinaryConfig {
    
    private static CloudinaryConfig instance;
    private final Cloudinary cloudinary;
    
    // Constructeur privé (Singleton)
    private CloudinaryConfig() {
        DatabaseConfig dbConfig = DatabaseConfig.getInstance();
        
        cloudinary = new Cloudinary(ObjectUtils.asMap(
            "cloud_name", dbConfig.getProperty("cloudinary.cloud_name"),
            "api_key", dbConfig.getProperty("cloudinary.api_key"),
            "api_secret", dbConfig.getProperty("cloudinary.api_secret")
        ));
    }
    
    /**
     * Récupère l'instance unique (Singleton)
     */
    public static synchronized CloudinaryConfig getInstance() {
        if (instance == null) {
            instance = new CloudinaryConfig();
        }
        return instance;
    }
    
    /**
     * Récupère l'instance Cloudinary
     */
    public Cloudinary getCloudinary() {
        return cloudinary;
    }
    
    /**
     * Upload une image sur Cloudinary
     * @param imageFile Le fichier image
     * @param folder Le dossier de destination
     * @return L'URL de l'image uploadée
     */
    public String uploadImage(File imageFile, String folder) throws Exception {
        @SuppressWarnings("unchecked")
        Map<String, Object> uploadResult = cloudinary.uploader().upload(imageFile, ObjectUtils.asMap(
            "folder", folder
        ));
        
        return (String) uploadResult.get("secure_url");
    }
    
    /**
     * Upload une image depuis une URL sur Cloudinary
     * @param imageUrl L'URL de l'image source
     * @param folder Le dossier de destination
     * @return L'URL de l'image uploadée sur Cloudinary
     */
    public String uploadImageFromUrl(String imageUrl, String folder) throws Exception {
        @SuppressWarnings("unchecked")
        Map<String, Object> uploadResult = cloudinary.uploader().upload(imageUrl, ObjectUtils.asMap(
            "folder", folder
        ));
        
        return (String) uploadResult.get("secure_url");
    }
    
    /**
     * Supprime une image de Cloudinary
     * @param publicId L'ID public de l'image
     */
    public void deleteImage(String publicId) throws Exception {
        cloudinary.uploader().destroy(publicId, ObjectUtils.emptyMap());
    }
    
    /**
     * Extrait le public_id depuis une URL Cloudinary
     */
    public String extractPublicId(String imageUrl) {
        if (imageUrl == null || !imageUrl.contains("cloudinary.com")) {
            return null;
        }
        
        String[] parts = imageUrl.split("/upload/");
        if (parts.length < 2) return null;
        
        String path = parts[1];
        int lastSlash = path.lastIndexOf('/');
        int lastDot = path.lastIndexOf('.');
        
        if (lastSlash != -1 && lastDot != -1) {
            return path.substring(0, lastDot);
        }
        
        return null;
    }
}
