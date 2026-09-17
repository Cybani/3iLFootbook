-- 1. Table UTILISATEUR
CREATE TABLE utilisateur (
    id_utilisateur SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL, -- Sera haché en PHP (ex: password_hash)
    role VARCHAR(20) DEFAULT 'user' NOT NULL
);

-- 2. Table TERRAIN
CREATE TABLE terrain (
    id_terrain SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    type_surface VARCHAR(50) NOT NULL, -- Ex: Synthétique, Gazon, Futsal
    capacite INT NOT NULL, -- Ex: 10 pour un 5v5
    statut_terrain VARCHAR(50) DEFAULT 'actif' NOT NULL -- actif ou maintenance
);

-- 3. Table RESERVATION
CREATE TABLE reservation (
    id_reservation SERIAL PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_terrain INT NOT NULL,
    date_reservation DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    statut_reservation VARCHAR(50) DEFAULT 'validée' NOT NULL,
    
    -- Création des clés étrangères pour lier les tables
    CONSTRAINT fk_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_terrain FOREIGN KEY (id_terrain) REFERENCES terrain(id_terrain) ON DELETE CASCADE
);

-- =======================================================
-- Insertion de données de test
-- =======================================================

-- Un admin et un utilisateur de test
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES 
('Zidane', 'Zinedine', 'admin@3ilfootbook.fr', 'admin123', 'admin'),
('Mbappé', 'Kylian', 'k.mbappe@user.fr', 'user123', 'user');

-- Trois terrains de test
INSERT INTO terrain (nom, type_surface, capacite, statut_terrain) VALUES 
('Terrain Maracana', 'Synthétique', 10, 'actif'),
('Terrain Camp Nou', 'Gazon', 22, 'actif'),
('Terrain Futsal Arena', 'Parquet', 10, 'maintenance');

-- Une réservation de test (pour le terrain 1, par l'utilisateur 2)
INSERT INTO reservation (id_utilisateur, id_terrain, date_reservation, heure_debut, heure_fin, statut_reservation) VALUES 
(2, 1, '2023-11-20', '18:00:00', '20:00:00', 'validée');