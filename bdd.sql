-- 1. Table UTILISATEUR
CREATE TABLE utilisateur (
    id_utilisateur SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL, -- Sera haché en PHP (ex: password_hash)
    role SMALLINT DEFAULT 1 NOT NULL -- 0 = admin, 1 = utilisateur
);

-- 2. Table TERRAIN
CREATE TABLE terrain (
    id_terrain SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    type_surface VARCHAR(50) NOT NULL, -- Ex: Synthétique, Gazon, Futsal
    capacite INT NOT NULL, -- Ex: 10 pour un 5v5
    quartier VARCHAR(100), -- Quartier de Limoges
    adresse VARCHAR(255), -- Adresse indicative du terrain
    description TEXT, -- Petit texte de présentation
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
-- Mots de passe hachés avec password_hash() de PHP (bcrypt).
-- Identifiants de test : admin@3ilfootbook.fr / admin123  et  k.mbappe@user.fr / user123
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES
('Zidane', 'Zinedine', 'admin@3ilfootbook.fr', '$2y$10$yVskBrYA4eyi4uw4jwmAvuGiHQHgKpkXjD6/VHQd.B85Gw/SYg1nu', 0),
('Mbappé', 'Kylian', 'k.mbappe@user.fr', '$2y$10$vu.iu5F1s5Uw6wqpjt4/murKIechJX6UStKkYBz5L6S4hb2Nf.r5C', 1);

-- Terrains de foot à Limoges (adresses indicatives pour le projet étudiant)
INSERT INTO terrain (nom, type_surface, capacite, quartier, adresse, description, statut_terrain) VALUES
('Stade de Beaublanc', 'Gazon', 22, 'Beaublanc', 'Rue du Général Bessol, 87100 Limoges', 'Le grand stade historique de Limoges, pelouse naturelle pour les matchs à 11.', 'actif'),
('Complexe sportif de Landouge', 'Synthétique', 14, 'Landouge', 'Avenue de Landouge, 87100 Limoges', 'Terrain synthétique tout temps, idéal pour du 7v7 entre étudiants.', 'actif'),
('City-stade de Vanteaux', 'Synthétique', 10, 'Vanteaux', 'Rue de Vanteaux, 87000 Limoges', 'Petit terrain de proximité clôturé, parfait pour un 5v5 rapide.', 'actif'),
('Gymnase de la Bastide (Futsal)', 'Parquet', 10, 'La Bastide', 'Rue de la Bastide, 87000 Limoges', 'Salle couverte en parquet pour jouer au futsal, même quand il pleut.', 'actif'),
('Plaine des jeux de Roussillon', 'Synthétique', 12, 'Roussillon', 'Rue de Roussillon, 87100 Limoges', 'Terrain synthétique éclairé, disponible en soirée.', 'actif'),
('Stade de Beaune-les-Mines', 'Stabilisé', 22, 'Beaune-les-Mines', 'Route de Beaune, 87280 Limoges', 'Terrain stabilisé au nord de Limoges — actuellement en réfection.', 'maintenance');

-- Une réservation de test (terrain 1, par l'utilisateur 2, Kylian Mbappé)
INSERT INTO reservation (id_utilisateur, id_terrain, date_reservation, heure_debut, heure_fin, statut_reservation) VALUES
(2, 1, CURRENT_DATE + 3, '18:00:00', '20:00:00', 'validée');