-- ============================================
-- Script de Création de la Base de Données PostgreSQL
-- Projet: Brasil Burger
-- Date: 11 Décembre 2025
-- Cloud: Neon.tech
-- ============================================

-- Suppression de la base si elle existe (Neon ne nécessite pas cette étape)
-- La base est créée automatiquement via l'interface Neon

-- ============================================
-- TABLE: utilisateur
-- Description: Stocke tous les utilisateurs (Client, Gestionnaire, Livreur)
-- ============================================
CREATE TABLE utilisateur (
    id_utilisateur BIGSERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    etat BOOLEAN DEFAULT TRUE,
    type_utilisateur VARCHAR(20) NOT NULL CHECK (type_utilisateur IN ('CLIENT', 'GESTIONNAIRE', 'LIVREUR')),
    
    -- Attributs spécifiques au Client
    adresse TEXT,
    
    -- Attributs spécifiques au Livreur
    disponible BOOLEAN DEFAULT TRUE
);

CREATE INDEX idx_utilisateur_email ON utilisateur(email);
CREATE INDEX idx_utilisateur_type ON utilisateur(type_utilisateur);
CREATE INDEX idx_utilisateur_telephone ON utilisateur(telephone);

-- ============================================
-- TABLE: burger
-- Description: Catalogue des burgers
-- ============================================
CREATE TABLE burger (
    id_burger BIGSERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prix DECIMAL(10,2) NOT NULL CHECK (prix > 0),
    description TEXT,
    ingredients TEXT,
    image VARCHAR(500),
    archive BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_burger_archive ON burger(archive);
CREATE INDEX idx_burger_nom ON burger(nom);

-- ============================================
-- TABLE: complement
-- Description: Compléments (boissons, frites)
-- ============================================
CREATE TABLE complement (
    id_complement BIGSERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prix DECIMAL(10,2) NOT NULL CHECK (prix > 0),
    type_complement VARCHAR(20) NOT NULL CHECK (type_complement IN ('BOISSON', 'FRITES')),
    image VARCHAR(500),
    archive BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_complement_type ON complement(type_complement);
CREATE INDEX idx_complement_archive ON complement(archive);

-- ============================================
-- TABLE: menu
-- Description: Menus composés (Burger + Boisson + Frites)
-- ============================================
CREATE TABLE menu (
    id_menu BIGSERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(500),
    archive BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    id_burger BIGINT NOT NULL,
    id_boisson BIGINT NOT NULL,
    id_frites BIGINT NOT NULL,
    
    FOREIGN KEY (id_burger) REFERENCES burger(id_burger),
    FOREIGN KEY (id_boisson) REFERENCES complement(id_complement),
    FOREIGN KEY (id_frites) REFERENCES complement(id_complement)
);

CREATE INDEX idx_menu_archive ON menu(archive);
CREATE INDEX idx_menu_burger ON menu(id_burger);

-- ============================================
-- TABLE: zone
-- Description: Zones géographiques de livraison
-- ============================================
CREATE TABLE zone (
    id_zone BIGSERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prix_livraison DECIMAL(10,2) NOT NULL CHECK (prix_livraison >= 0),
    quartiers TEXT NOT NULL
);

CREATE INDEX idx_zone_nom ON zone(nom);

-- ============================================
-- TABLE: commande
-- Description: Commandes passées par les clients
-- ============================================
CREATE TABLE commande (
    id_commande BIGSERIAL PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    type_service VARCHAR(20) NOT NULL CHECK (type_service IN ('SUR_PLACE', 'A_EMPORTER', 'LIVRAISON')),
    etat VARCHAR(20) DEFAULT 'EN_ATTENTE' CHECK (etat IN ('EN_ATTENTE', 'EN_PREPARATION', 'PRETE', 'TERMINEE', 'ANNULEE')),
    montant_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    adresse_livraison TEXT,
    
    id_client BIGINT NOT NULL,
    id_zone BIGINT,
    id_livreur BIGINT,
    
    FOREIGN KEY (id_client) REFERENCES utilisateur(id_utilisateur),
    FOREIGN KEY (id_zone) REFERENCES zone(id_zone),
    FOREIGN KEY (id_livreur) REFERENCES utilisateur(id_utilisateur)
);

CREATE INDEX idx_commande_client ON commande(id_client);
CREATE INDEX idx_commande_livreur ON commande(id_livreur);
CREATE INDEX idx_commande_zone ON commande(id_zone);
CREATE INDEX idx_commande_date ON commande(date_commande);
CREATE INDEX idx_commande_etat ON commande(etat);
CREATE INDEX idx_commande_numero ON commande(numero);
CREATE INDEX idx_commande_date_etat ON commande(date_commande, etat);

-- ============================================
-- TABLE: ligne_commande
-- Description: Détails des produits dans une commande
-- ============================================
CREATE TABLE ligne_commande (
    id_ligne BIGSERIAL PRIMARY KEY,
    quantite INT NOT NULL CHECK (quantite > 0),
    prix_unitaire DECIMAL(10,2) NOT NULL,
    sous_total DECIMAL(10,2) NOT NULL,
    type_produit VARCHAR(20) NOT NULL CHECK (type_produit IN ('BURGER', 'MENU')),
    
    id_commande BIGINT NOT NULL,
    id_burger BIGINT,
    id_menu BIGINT,
    
    FOREIGN KEY (id_commande) REFERENCES commande(id_commande) ON DELETE CASCADE,
    FOREIGN KEY (id_burger) REFERENCES burger(id_burger),
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu),
    
    CHECK (
        (type_produit = 'BURGER' AND id_burger IS NOT NULL AND id_menu IS NULL) OR
        (type_produit = 'MENU' AND id_menu IS NOT NULL AND id_burger IS NULL)
    )
);

CREATE INDEX idx_ligne_commande ON ligne_commande(id_commande);
CREATE INDEX idx_ligne_produit ON ligne_commande(type_produit, id_burger, id_menu);

-- ============================================
-- TABLE: ligne_complement
-- Description: Compléments supplémentaires pour les burgers
-- ============================================
CREATE TABLE ligne_complement (
    id_ligne_complement BIGSERIAL PRIMARY KEY,
    
    id_ligne BIGINT NOT NULL,
    id_complement BIGINT NOT NULL,
    
    FOREIGN KEY (id_ligne) REFERENCES ligne_commande(id_ligne) ON DELETE CASCADE,
    FOREIGN KEY (id_complement) REFERENCES complement(id_complement),
    
    UNIQUE(id_ligne, id_complement)
);

-- ============================================
-- TABLE: paiement
-- Description: Paiements des commandes
-- ============================================
CREATE TABLE paiement (
    id_paiement BIGSERIAL PRIMARY KEY,
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    montant DECIMAL(10,2) NOT NULL CHECK (montant > 0),
    mode_paiement VARCHAR(20) NOT NULL CHECK (mode_paiement IN ('WAVE', 'ORANGE_MONEY')),
    reference VARCHAR(100) NOT NULL UNIQUE,
    statut VARCHAR(20) DEFAULT 'EN_ATTENTE' CHECK (statut IN ('EN_ATTENTE', 'VALIDE', 'ECHOUE', 'REMBOURSE')),
    
    id_commande BIGINT NOT NULL UNIQUE,
    
    FOREIGN KEY (id_commande) REFERENCES commande(id_commande)
);

CREATE INDEX idx_paiement_commande ON paiement(id_commande);
CREATE INDEX idx_paiement_reference ON paiement(reference);
CREATE INDEX idx_paiement_statut ON paiement(statut);

-- ============================================
-- VUES POUR LES STATISTIQUES
-- ============================================

-- Vue: Commandes du jour avec détails
CREATE VIEW v_commandes_jour AS
SELECT 
    c.id_commande,
    c.numero,
    c.date_commande,
    c.type_service,
    c.etat,
    c.montant_total,
    CONCAT(u.nom, ' ', u.prenom) AS nom_client,
    u.telephone AS telephone_client,
    z.nom AS zone_livraison,
    CONCAT(l.nom, ' ', l.prenom) AS nom_livreur
FROM commande c
INNER JOIN utilisateur u ON c.id_client = u.id_utilisateur
LEFT JOIN zone z ON c.id_zone = z.id_zone
LEFT JOIN utilisateur l ON c.id_livreur = l.id_utilisateur
WHERE DATE(c.date_commande) = CURRENT_DATE;

-- Vue: Statistiques journalières
CREATE VIEW v_statistiques_jour AS
SELECT 
    CURRENT_DATE AS date_stat,
    
    -- Commandes en cours (en préparation ou prêtes)
    (SELECT COUNT(*) FROM commande 
     WHERE DATE(date_commande) = CURRENT_DATE 
     AND etat IN ('EN_PREPARATION', 'PRETE')) AS commandes_en_cours,
    
    -- Commandes validées (prêtes ou terminées)
    (SELECT COUNT(*) FROM commande 
     WHERE DATE(date_commande) = CURRENT_DATE 
     AND etat IN ('PRETE', 'TERMINEE')) AS commandes_validees,
    
    -- Recettes journalières
    (SELECT COALESCE(SUM(montant_total), 0) FROM commande 
     WHERE DATE(date_commande) = CURRENT_DATE 
     AND etat != 'ANNULEE') AS recette_journaliere,
    
    -- Commandes annulées
    (SELECT COUNT(*) FROM commande 
     WHERE DATE(date_commande) = CURRENT_DATE 
     AND etat = 'ANNULEE') AS commandes_annulees;

-- Vue: Produits les plus vendus du jour
CREATE VIEW v_produits_plus_vendus_jour AS
SELECT 
    lc.type_produit,
    CASE 
        WHEN lc.type_produit = 'BURGER' THEN b.nom
        WHEN lc.type_produit = 'MENU' THEN m.nom
    END AS nom_produit,
    SUM(lc.quantite) AS total_vendu,
    SUM(lc.sous_total) AS chiffre_affaire
FROM ligne_commande lc
INNER JOIN commande c ON lc.id_commande = c.id_commande
LEFT JOIN burger b ON lc.id_burger = b.id_burger
LEFT JOIN menu m ON lc.id_menu = m.id_menu
WHERE DATE(c.date_commande) = CURRENT_DATE
AND c.etat != 'ANNULEE'
GROUP BY lc.type_produit, 
    CASE 
        WHEN lc.type_produit = 'BURGER' THEN b.nom
        WHEN lc.type_produit = 'MENU' THEN m.nom
    END
ORDER BY total_vendu DESC;

-- ============================================
-- FONCTIONS
-- ============================================

-- Fonction: Générer un numéro de commande unique
CREATE OR REPLACE FUNCTION fn_generer_numero_commande()
RETURNS VARCHAR(50) AS $$
DECLARE
    v_count INT;
    v_date VARCHAR(8);
    v_numero VARCHAR(50);
BEGIN
    v_date := TO_CHAR(NOW(), 'YYYYMMDD');
    
    SELECT COUNT(*) + 1 INTO v_count
    FROM commande
    WHERE DATE(date_commande) = CURRENT_DATE;
    
    v_numero := 'CMD-' || v_date || '-' || LPAD(v_count::TEXT, 4, '0');
    
    RETURN v_numero;
END;
$$ LANGUAGE plpgsql;

-- Fonction: Obtenir le prix d'un menu
CREATE OR REPLACE FUNCTION fn_get_prix_menu(p_id_menu BIGINT)
RETURNS DECIMAL(10,2) AS $$
DECLARE
    v_prix DECIMAL(10,2);
BEGIN
    SELECT b.prix + boisson.prix + frites.prix INTO v_prix
    FROM menu m
    INNER JOIN burger b ON m.id_burger = b.id_burger
    INNER JOIN complement boisson ON m.id_boisson = boisson.id_complement
    INNER JOIN complement frites ON m.id_frites = frites.id_complement
    WHERE m.id_menu = p_id_menu;
    
    RETURN COALESCE(v_prix, 0);
END;
$$ LANGUAGE plpgsql;

-- Fonction: Calculer le montant total d'une commande
CREATE OR REPLACE FUNCTION fn_calculer_montant_commande(p_id_commande BIGINT)
RETURNS VOID AS $$
DECLARE
    v_total DECIMAL(10,2);
    v_prix_livraison DECIMAL(10,2);
BEGIN
    -- Calculer la somme des lignes
    SELECT COALESCE(SUM(sous_total), 0) INTO v_total
    FROM ligne_commande
    WHERE id_commande = p_id_commande;
    
    -- Ajouter les frais de livraison si applicable
    SELECT COALESCE(z.prix_livraison, 0) INTO v_prix_livraison
    FROM commande c
    LEFT JOIN zone z ON c.id_zone = z.id_zone
    WHERE c.id_commande = p_id_commande
    AND c.type_service = 'LIVRAISON';
    
    v_total := v_total + v_prix_livraison;
    
    -- Mettre à jour la commande
    UPDATE commande 
    SET montant_total = v_total
    WHERE id_commande = p_id_commande;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- TRIGGERS
-- ============================================

-- Trigger: Calculer le sous-total avant insertion
CREATE OR REPLACE FUNCTION trg_ligne_commande_before_insert()
RETURNS TRIGGER AS $$
BEGIN
    NEW.sous_total := NEW.prix_unitaire * NEW.quantite;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_ligne_commande_before_insert
BEFORE INSERT ON ligne_commande
FOR EACH ROW
EXECUTE FUNCTION trg_ligne_commande_before_insert();

-- Trigger: Calculer le sous-total avant mise à jour
CREATE OR REPLACE FUNCTION trg_ligne_commande_before_update()
RETURNS TRIGGER AS $$
BEGIN
    NEW.sous_total := NEW.prix_unitaire * NEW.quantite;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_ligne_commande_before_update
BEFORE UPDATE ON ligne_commande
FOR EACH ROW
EXECUTE FUNCTION trg_ligne_commande_before_update();

-- Trigger: Recalculer le montant total après insertion
CREATE OR REPLACE FUNCTION trg_ligne_commande_after_insert()
RETURNS TRIGGER AS $$
BEGIN
    PERFORM fn_calculer_montant_commande(NEW.id_commande);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_ligne_commande_after_insert
AFTER INSERT ON ligne_commande
FOR EACH ROW
EXECUTE FUNCTION trg_ligne_commande_after_insert();

-- Trigger: Recalculer le montant total après mise à jour
CREATE OR REPLACE FUNCTION trg_ligne_commande_after_update()
RETURNS TRIGGER AS $$
BEGIN
    PERFORM fn_calculer_montant_commande(NEW.id_commande);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_ligne_commande_after_update
AFTER UPDATE ON ligne_commande
FOR EACH ROW
EXECUTE FUNCTION trg_ligne_commande_after_update();

-- Trigger: Recalculer le montant total après suppression
CREATE OR REPLACE FUNCTION trg_ligne_commande_after_delete()
RETURNS TRIGGER AS $$
BEGIN
    PERFORM fn_calculer_montant_commande(OLD.id_commande);
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_ligne_commande_after_delete
AFTER DELETE ON ligne_commande
FOR EACH ROW
EXECUTE FUNCTION trg_ligne_commande_after_delete();

-- Trigger: Générer automatiquement le numéro de commande
CREATE OR REPLACE FUNCTION trg_commande_before_insert()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.numero IS NULL OR NEW.numero = '' THEN
        NEW.numero := fn_generer_numero_commande();
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_commande_before_insert
BEFORE INSERT ON commande
FOR EACH ROW
EXECUTE FUNCTION trg_commande_before_insert();

-- ============================================
-- DONNÉES DE TEST
-- ============================================

-- Insertion d'un gestionnaire
INSERT INTO utilisateur (nom, prenom, telephone, email, password, type_utilisateur) 
VALUES ('Admin', 'Brasil', '771234567', 'admin@brasilburger.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'GESTIONNAIRE');

-- Insertion de clients de test
INSERT INTO utilisateur (nom, prenom, telephone, email, password, type_utilisateur, adresse) VALUES
('Diop', 'Fatou', '775551234', 'fatou.diop@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CLIENT', 'Sacré-Coeur 3, Dakar'),
('Ndiaye', 'Moussa', '776662345', 'moussa.ndiaye@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CLIENT', 'Mermoz, Dakar'),
('Sarr', 'Aissatou', '777773456', 'aissatou.sarr@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CLIENT', 'Plateau, Dakar');

-- Insertion de livreurs
INSERT INTO utilisateur (nom, prenom, telephone, email, password, type_utilisateur, disponible) VALUES
('Fall', 'Ibrahima', '778884567', 'ibrahima.fall@brasilburger.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'LIVREUR', TRUE),
('Kane', 'Mamadou', '779995678', 'mamadou.kane@brasilburger.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'LIVREUR', TRUE);

-- Insertion de zones de livraison
INSERT INTO zone (nom, prix_livraison, quartiers) VALUES
('Zone Centre', 1000.00, 'Plateau, Médina, HLM'),
('Zone Nord', 1500.00, 'Sacré-Coeur, Mermoz, Fann, Amitié'),
('Zone Ouest', 2000.00, 'Almadies, Ngor, Yoff, Ouakam'),
('Zone Est', 1500.00, 'Pikine, Guédiawaye'),
('Zone Sud', 2500.00, 'Rufisque, Bargny');

-- ============================================
-- FIN DU SCRIPT
-- ============================================
