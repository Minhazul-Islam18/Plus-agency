-- Portfolio Sectors & Subsectors seed (French, language_id=183)
-- Idempotent: safe to re-run — every row is guarded by a NOT EXISTS check
-- against (language_id, parent_id, name), so running this twice never
-- creates duplicates.

-- ==== Sector: Agriculture et agroalimentaire ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Agriculture et agroalimentaire', 'fas fa-leaf', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Agriculture et agroalimentaire');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Agriculture et agroalimentaire' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Produits agricoles', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Produits agricoles');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Élevage', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Élevage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Pêche', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Pêche');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Foresterie', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Foresterie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Agroalimentaire', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Agroalimentaire');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Boissons', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Boissons');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Services agricoles', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Services agricoles');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Horticulture', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Horticulture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Apiculture', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Apiculture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Machines agricoles', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Machines agricoles');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Semences', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Semences');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Engrais', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Engrais');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Produits phytosanitaires', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Produits phytosanitaires');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Santé animale', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Santé animale');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Aquaculture', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Aquaculture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Irrigation agricole', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Irrigation agricole');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Stockage agricole', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Stockage agricole');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Chaîne du froid', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Chaîne du froid');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Transformation agricole', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Transformation agricole');

-- ==== Sector: Technologies de l'information et télécommunications ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Technologies de l\'information et télécommunications', 'fas fa-laptop-code', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Technologies de l\'information et télécommunications');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Technologies de l\'information et télécommunications' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Informatique', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Informatique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Bureautique', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Bureautique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Réseaux informatiques', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Réseaux informatiques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Logiciels', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Logiciels');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Systèmes d\'information', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Systèmes d\'information');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Services informatiques', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Services informatiques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Télécommunications', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Télécommunications');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Audiovisuel', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Audiovisuel');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Services postaux', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Services postaux');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Cybersécurité', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Cybersécurité');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Cloud', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Cloud');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Centres de données', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Centres de données');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Multimédia', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Multimédia');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Intelligence artificielle', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Intelligence artificielle');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'IoT', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'IoT');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Solutions numériques', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Solutions numériques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Développement web', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Développement web');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Applications mobiles', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Applications mobiles');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Électronique', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Électronique');

-- ==== Sector: Construction, immobilier et infrastructures ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Construction, immobilier et infrastructures', 'fas fa-building', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Construction, immobilier et infrastructures');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Construction, immobilier et infrastructures' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Construction', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Construction');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Immobilier', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Immobilier');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Architecture', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Architecture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Ingénierie', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Ingénierie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Contrôle technique', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Contrôle technique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Matériaux de construction', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Matériaux de construction');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements de construction', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements de construction');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements miniers', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements miniers');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Forage', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Forage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Hydraulique', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Hydraulique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Routes', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Routes');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Génie civil', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Génie civil');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Ouvrages d\'art', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Ouvrages d\'art');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Location d\'équipements', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Location d\'équipements');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Topographie', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Topographie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Bâtiments', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Bâtiments');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Infrastructures urbaines', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Infrastructures urbaines');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Installations', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Installations');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Entretien immobilier', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Entretien immobilier');

-- ==== Sector: Défense et sécurité ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Défense et sécurité', 'fas fa-shield-alt', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Défense et sécurité');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Défense et sécurité' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Détection de mines', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Détection de mines');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements de sécurité', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements de sécurité');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements de défense', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements de défense');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Sécurité incendie', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Sécurité incendie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Maintenance de défense', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Maintenance de défense');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Systèmes de défense', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Systèmes de défense');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Logiciels militaires', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Logiciels militaires');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'R&D défense', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'R&D défense');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Administration de la défense', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Administration de la défense');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Enquêtes', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Enquêtes');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Sécurité privée', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Sécurité privée');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Formation à la sécurité', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Formation à la sécurité');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements de protection individuelle', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements de protection individuelle');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Vidéosurveillance', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Vidéosurveillance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Contrôle d\'accès', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Contrôle d\'accès');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Sécurité électronique', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Sécurité électronique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Gestion des risques', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Gestion des risques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Sécurité civile', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Sécurité civile');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Secours et sauvetage', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Secours et sauvetage');

-- ==== Sector: Éducation et formation ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Éducation et formation', 'fas fa-graduation-cap', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Éducation et formation');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Éducation et formation' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Éducation', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Éducation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Formation', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Formation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Fournitures scolaires', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Fournitures scolaires');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Matériel pédagogique', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Matériel pédagogique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Formation professionnelle', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Formation professionnelle');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Formation technique', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Formation technique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements pédagogiques', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements pédagogiques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Laboratoires éducatifs', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Laboratoires éducatifs');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'E-learning', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'E-learning');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Formation numérique', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Formation numérique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Mobilier scolaire', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Mobilier scolaire');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Bibliothèques', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Bibliothèques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Formation continue', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Formation continue');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Simulateurs de formation', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Simulateurs de formation');

-- ==== Sector: Énergie ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Énergie', 'fas fa-bolt', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Énergie');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Énergie' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Électricité', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Électricité');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Carburants', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Carburants');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Produits pétroliers', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Produits pétroliers');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Pétrole et gaz', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Pétrole et gaz');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements électriques', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements électriques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Éclairage', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Éclairage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Énergie solaire', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Énergie solaire');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Énergie éolienne', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Énergie éolienne');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Énergies renouvelables', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Énergies renouvelables');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Postes électriques', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Postes électriques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Lignes électriques', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Lignes électriques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Groupes électrogènes', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Groupes électrogènes');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Transformateurs', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Transformateurs');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Réseaux électriques', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Réseaux électriques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Stockage d\'énergie', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Stockage d\'énergie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Batteries', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Batteries');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Onduleurs', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Onduleurs');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Électrification rurale', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Électrification rurale');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Efficacité énergétique', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Efficacité énergétique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Automatisation électrique', 1, 20
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Automatisation électrique');

-- ==== Sector: Environnement, eau et assainissement ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Environnement, eau et assainissement', 'fas fa-tint', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Environnement, eau et assainissement');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Environnement, eau et assainissement' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Eau potable', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Eau potable');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Assainissement', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Assainissement');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Protection de l\'environnement', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Protection de l\'environnement');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Approvisionnement en eau', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Approvisionnement en eau');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Déchets solides', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Déchets solides');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Études environnementales', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Études environnementales');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Forages d\'eau', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Forages d\'eau');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Traitement des eaux', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Traitement des eaux');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Stations de pompage', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Stations de pompage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Réseaux d\'eau', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Réseaux d\'eau');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Hydraulique urbaine', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Hydraulique urbaine');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Hydraulique rurale', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Hydraulique rurale');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Eaux usées', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Eaux usées');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Drainage', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Drainage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Gestion des déchets', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Gestion des déchets');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Recyclage', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Recyclage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Hygiène publique', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Hygiène publique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Surveillance environnementale', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Surveillance environnementale');

-- ==== Sector: Finance et assurance ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Finance et assurance', 'fas fa-coins', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Finance et assurance');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Finance et assurance' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Finance', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Finance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Assurance', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Assurance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Banque', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Banque');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Microfinance', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Microfinance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Paiements', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Paiements');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Fintech', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Fintech');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Crédit', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Crédit');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Gestion financière', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Gestion financière');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Conseil financier', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Conseil financier');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Courtage', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Courtage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Gestion des risques', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Gestion des risques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Services de paiement', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Services de paiement');

-- ==== Sector: Industrie, matériaux et équipements techniques ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Industrie, matériaux et équipements techniques', 'fas fa-cogs', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Industrie, matériaux et équipements techniques');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Industrie, matériaux et équipements techniques' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Mines', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Mines');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Métaux', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Métaux');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Produits chimiques', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Produits chimiques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Textiles', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Textiles');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Plastiques', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Plastiques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Caoutchouc', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Caoutchouc');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Machines industrielles', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Machines industrielles');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements de laboratoire', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements de laboratoire');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Métallurgie', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Métallurgie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Sidérurgie', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Sidérurgie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Mesure et contrôle', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Mesure et contrôle');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Instrumentation', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Instrumentation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Automatisation', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Automatisation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements mécaniques', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements mécaniques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Outillage industriel', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Outillage industriel');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Maintenance industrielle', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Maintenance industrielle');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements miniers', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements miniers');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Contrôle qualité', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Contrôle qualité');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements de précision', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements de précision');

-- ==== Sector: Santé et équipements médicaux ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Santé et équipements médicaux', 'fas fa-heartbeat', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Santé et équipements médicaux');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Santé et équipements médicaux' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements médicaux', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements médicaux');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Pharmacie', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Pharmacie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Santé', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Santé');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Action sociale', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Action sociale');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Services hospitaliers', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Services hospitaliers');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Dispositifs médicaux', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Dispositifs médicaux');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Consommables médicaux', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Consommables médicaux');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Médicaments', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Médicaments');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Vaccins', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Vaccins');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Laboratoires médicaux', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Laboratoires médicaux');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Imagerie médicale', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Imagerie médicale');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Diagnostic', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Diagnostic');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Mobilier médical', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Mobilier médical');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Hygiène hospitalière', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Hygiène hospitalière');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Maintenance biomédicale', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Maintenance biomédicale');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Santé numérique', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Santé numérique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Ambulances', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Ambulances');

-- ==== Sector: Biens de consommation, mobilier et loisirs ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Biens de consommation, mobilier et loisirs', 'fas fa-shopping-bag', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Biens de consommation, mobilier et loisirs');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Biens de consommation, mobilier et loisirs' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Habillement', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Habillement');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Chaussures', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Chaussures');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Maroquinerie', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Maroquinerie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Bagagerie', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Bagagerie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Sport', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Sport');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Loisirs créatifs', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Loisirs créatifs');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Jeux et jouets', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Jeux et jouets');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Artisanat', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Artisanat');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Mobilier', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Mobilier');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Mobilier de bureau', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Mobilier de bureau');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Électroménager', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Électroménager');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Produits d\'entretien', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Produits d\'entretien');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Cuisine professionnelle', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Cuisine professionnelle');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Services de restauration', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Services de restauration');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements sportifs', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements sportifs');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Articles culturels', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Articles culturels');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Décoration', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Décoration');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements de loisirs', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements de loisirs');

-- ==== Sector: Imprimerie, édition et communication ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Imprimerie, édition et communication', 'fas fa-print', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Imprimerie, édition et communication');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Imprimerie, édition et communication' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Imprimés', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Imprimés');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Impression', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Impression');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Édition', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Édition');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Communication', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Communication');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Relations publiques', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Relations publiques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Signalétique', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Signalétique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Publicité', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Publicité');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Communication digitale', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Communication digitale');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Événementiel', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Événementiel');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Production audiovisuelle', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Production audiovisuelle');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Design graphique', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Design graphique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Photographie', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Photographie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Supports promotionnels', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Supports promotionnels');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Médias', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Médias');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Marketing digital', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Marketing digital');

-- ==== Sector: Recherche et développement ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Recherche et développement', 'fas fa-flask', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Recherche et développement');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Recherche et développement' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Recherche', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Recherche');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Développement', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Développement');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Conseil scientifique', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Conseil scientifique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Études techniques', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Études techniques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Expertise technique', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Expertise technique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Innovation', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Innovation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Transfert de technologie', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Transfert de technologie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Études de faisabilité', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Études de faisabilité');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Prototypage', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Prototypage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Essais techniques', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Essais techniques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Recherche appliquée', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Recherche appliquée');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Veille technologique', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Veille technologique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Ingénierie de recherche', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Ingénierie de recherche');

-- ==== Sector: Transport et logistique ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Transport et logistique', 'fas fa-truck', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Transport et logistique');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Transport et logistique' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Équipements de transport', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Équipements de transport');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Transport', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Transport');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Logistique', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Logistique');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Voyages', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Voyages');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Location de véhicules', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Location de véhicules');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Fret', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Fret');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Transit', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Transit');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Dédouanement', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Dédouanement');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Transport routier', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Transport routier');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Transport aérien', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Transport aérien');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Transport maritime', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Transport maritime');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Manutention', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Manutention');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Stockage', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Stockage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Chaîne d\'approvisionnement', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Chaîne d\'approvisionnement');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Livraison', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Livraison');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Gestion de flotte', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Gestion de flotte');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Pièces automobiles', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Pièces automobiles');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Entretien automobile', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Entretien automobile');

-- ==== Sector: Services aux entreprises et services divers ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 183, NULL, 'Services aux entreprises et services divers', 'fas fa-briefcase', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Services aux entreprises et services divers');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` IS NULL AND `name` = 'Services aux entreprises et services divers' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Réparation', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Réparation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Maintenance', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Maintenance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Facility management', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Facility management');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Hôtellerie', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Hôtellerie');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Restauration', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Restauration');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Services publics', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Services publics');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Conseil', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Conseil');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Services juridiques', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Services juridiques');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Marketing', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Marketing');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Recrutement', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Recrutement');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Services sociaux', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Services sociaux');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Audit', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Audit');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Comptabilité', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Comptabilité');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Traduction', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Traduction');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Interprétariat', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Interprétariat');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Sécurité', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Sécurité');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Nettoyage', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Nettoyage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Gestion de projet', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Gestion de projet');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Études et conseil', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Études et conseil');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Externalisation', 1, 20
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Externalisation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Gestion administrative', 1, 21
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Gestion administrative');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Formation en entreprise', 1, 22
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Formation en entreprise');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 183, @sector_id, 'Contrôle et inspection', 1, 23
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 183 AND `parent_id` = @sector_id AND `name` = 'Contrôle et inspection');
