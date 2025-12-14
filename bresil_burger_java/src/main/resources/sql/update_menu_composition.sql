-- Script SQL pour mettre à jour la table menu
-- Ajouter les colonnes pour la composition du menu (Burger + Boisson + Frites)

-- Ajouter les colonnes de composition
ALTER TABLE menu 
ADD COLUMN IF NOT EXISTS id_burger BIGINT,
ADD COLUMN IF NOT EXISTS id_boisson BIGINT,
ADD COLUMN IF NOT EXISTS id_frites BIGINT;

-- Ajouter les contraintes de clés étrangères
ALTER TABLE menu 
ADD CONSTRAINT fk_menu_burger 
FOREIGN KEY (id_burger) REFERENCES burger(id_burger) ON DELETE SET NULL;

ALTER TABLE menu 
ADD CONSTRAINT fk_menu_boisson 
FOREIGN KEY (id_boisson) REFERENCES complement(id_complement) ON DELETE SET NULL;

ALTER TABLE menu 
ADD CONSTRAINT fk_menu_frites 
FOREIGN KEY (id_frites) REFERENCES complement(id_complement) ON DELETE SET NULL;

-- Commentaire pour clarifier la structure
COMMENT ON COLUMN menu.id_burger IS 'ID du burger composant le menu';
COMMENT ON COLUMN menu.id_boisson IS 'ID du complément de type BOISSON composant le menu';
COMMENT ON COLUMN menu.id_frites IS 'ID du complément de type FRITES composant le menu';
COMMENT ON TABLE menu IS 'Table des menus - Un menu est composé de: Burger + Boisson + Frites. Le prix du menu est la somme des prix qui le composent.';
