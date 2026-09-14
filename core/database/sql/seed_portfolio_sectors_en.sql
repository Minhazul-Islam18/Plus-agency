-- Portfolio Sectors & Subsectors seed (English, language_id=169)
-- Idempotent: safe to re-run — every row is guarded by a NOT EXISTS check
-- against (language_id, parent_id, name), so running this twice never
-- creates duplicates.

-- ==== Sector: Agriculture and agri-food ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Agriculture and agri-food', 'fas fa-leaf', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Agriculture and agri-food');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Agriculture and agri-food' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Agricultural products', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Agricultural products');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Livestock farming', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Livestock farming');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Fishing', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Fishing');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Forestry', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Forestry');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Agri-food', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Agri-food');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Drinks', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Drinks');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Agricultural services', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Agricultural services');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Horticulture', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Horticulture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Beekeeping', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Beekeeping');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Agricultural machinery', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Agricultural machinery');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Seeds', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Seeds');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Fertilizer', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Fertilizer');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Plant protection products', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Plant protection products');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Animal health', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Animal health');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Aquaculture', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Aquaculture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Agricultural irrigation', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Agricultural irrigation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Agricultural storage', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Agricultural storage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Cold chain', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Cold chain');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Agricultural transformation', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Agricultural transformation');

-- ==== Sector: Information technology, telecommunications and technologies ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Information technology, telecommunications and technologies', 'fas fa-laptop-code', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Information technology, telecommunications and technologies');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Information technology, telecommunications and technologies' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Computer science', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Computer science');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Office automation', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Office automation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Computer networks', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Computer networks');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Software', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Software');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Information systems', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Information systems');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'IT services', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'IT services');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Telecommunications', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Telecommunications');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Audiovisual', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Audiovisual');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Postal services', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Postal services');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Cybersecurity', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Cybersecurity');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Cloud', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Cloud');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Data centers', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Data centers');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Multimedia', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Multimedia');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Artificial intelligence', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Artificial intelligence');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'IoT', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'IoT');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Digital solutions', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Digital solutions');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Web development', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Web development');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Mobile applications', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Mobile applications');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Electronics', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Electronics');

-- ==== Sector: Construction, real estate and infrastructure ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Construction, real estate and infrastructure', 'fas fa-building', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Construction, real estate and infrastructure');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Construction, real estate and infrastructure' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Construction', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Construction');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Real estate', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Real estate');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Architecture', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Architecture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Engineering', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Engineering');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Technical inspection', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Technical inspection');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Building materials', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Building materials');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Construction equipment', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Construction equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Mining equipment', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Mining equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Drilling', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Drilling');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Hydraulics', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Hydraulics');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Roads', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Roads');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Civil engineering', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Civil engineering');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Engineering structures', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Engineering structures');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Equipment rental', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Equipment rental');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Topography', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Topography');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Buildings', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Buildings');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Urban infrastructure', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Urban infrastructure');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Facilities', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Facilities');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Property maintenance', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Property maintenance');

-- ==== Sector: Defense and security ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Defense and security', 'fas fa-shield-alt', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Defense and security');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Defense and security' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Mine detection', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Mine detection');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Safety equipment', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Safety equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Defense equipment', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Defense equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Fire safety', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Fire safety');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Defense maintenance', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Defense maintenance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Defense systems', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Defense systems');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Military software', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Military software');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Defense R&D', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Defense R&D');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Defense Administration', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Defense Administration');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Investigations', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Investigations');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Private security', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Private security');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Safety training', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Safety training');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Personal protective equipment', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Personal protective equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Video surveillance', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Video surveillance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Access control', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Access control');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Electronic security', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Electronic security');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Risk management', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Risk management');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Civil security', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Civil security');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Rescue and relief', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Rescue and relief');

-- ==== Sector: Education and training ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Education and training', 'fas fa-graduation-cap', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Education and training');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Education and training' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Education', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Education');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Training', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Training');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'School supplies', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'School supplies');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Teaching materials', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Teaching materials');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Professional training', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Professional training');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Technical training', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Technical training');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Teaching equipment', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Teaching equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Educational laboratories', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Educational laboratories');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'E-learning', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'E-learning');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Digital training', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Digital training');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'School furniture', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'School furniture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Libraries', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Libraries');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Continuing education', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Continuing education');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Training simulators', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Training simulators');

-- ==== Sector: Energy ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Energy', 'fas fa-bolt', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Energy');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Energy' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Electricity', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Electricity');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Fuels', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Fuels');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Petroleum products', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Petroleum products');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Oil and gas', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Oil and gas');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Electrical equipment', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Electrical equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Lighting', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Lighting');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Solar energy', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Solar energy');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Wind energy', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Wind energy');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Renewable energies', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Renewable energies');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Electrical substations', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Electrical substations');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Power lines', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Power lines');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Generator sets', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Generator sets');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Transformers', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Transformers');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Electrical networks', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Electrical networks');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Energy storage', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Energy storage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Batteries', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Batteries');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Inverters', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Inverters');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Rural electrification', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Rural electrification');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Energy efficiency', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Energy efficiency');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Electrical automation', 1, 20
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Electrical automation');

-- ==== Sector: Environment, water and sanitation ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Environment, water and sanitation', 'fas fa-tint', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Environment, water and sanitation');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Environment, water and sanitation' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Drinking water', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Drinking water');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Sanitation', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Sanitation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Environmental protection', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Environmental protection');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Water supply', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Water supply');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Solid waste', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Solid waste');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Environmental studies', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Environmental studies');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Water boreholes', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Water boreholes');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Water treatment', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Water treatment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Pumping stations', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Pumping stations');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Water networks', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Water networks');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Urban hydraulics', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Urban hydraulics');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Rural hydraulics', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Rural hydraulics');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Wastewater', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Wastewater');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Drainage', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Drainage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Waste management', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Waste management');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Recycling', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Recycling');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Public hygiene', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Public hygiene');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Environmental monitoring', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Environmental monitoring');

-- ==== Sector: Finance and insurance ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Finance and insurance', 'fas fa-coins', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Finance and insurance');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Finance and insurance' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Finance', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Finance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Insurance', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Insurance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Bank', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Bank');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Microfinance', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Microfinance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Payments', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Payments');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Fintech', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Fintech');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Credit', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Credit');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Financial management', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Financial management');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Financial advice', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Financial advice');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Brokerage', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Brokerage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Risk management', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Risk management');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Payment services', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Payment services');

-- ==== Sector: Industry, materials and technical equipment ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Industry, materials and technical equipment', 'fas fa-cogs', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Industry, materials and technical equipment');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Industry, materials and technical equipment' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Mines', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Mines');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Metals', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Metals');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Chemicals', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Chemicals');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Textiles', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Textiles');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Plastics', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Plastics');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Rubber', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Rubber');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Industrial machinery', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Industrial machinery');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Laboratory equipment', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Laboratory equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Metallurgy', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Metallurgy');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Steel industry', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Steel industry');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Measurement and control', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Measurement and control');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Instrumentation', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Instrumentation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Automation', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Automation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Mechanical equipment', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Mechanical equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Industrial tools', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Industrial tools');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Industrial maintenance', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Industrial maintenance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Mining equipment', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Mining equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Quality control', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Quality control');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Precision equipment', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Precision equipment');

-- ==== Sector: Health and medical equipment ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Health and medical equipment', 'fas fa-heartbeat', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Health and medical equipment');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Health and medical equipment' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Medical equipment', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Medical equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Pharmacy', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Pharmacy');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Health', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Health');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Social action', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Social action');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Hospital services', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Hospital services');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Medical devices', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Medical devices');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Medical consumables', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Medical consumables');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Drugs', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Drugs');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Vaccines', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Vaccines');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Medical laboratories', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Medical laboratories');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Medical imaging', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Medical imaging');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Diagnosis', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Diagnosis');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Medical furniture', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Medical furniture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Hospital hygiene', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Hospital hygiene');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Biomedical maintenance', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Biomedical maintenance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Digital health', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Digital health');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Ambulances', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Ambulances');

-- ==== Sector: Consumer goods, furniture and leisure ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Consumer goods, furniture and leisure', 'fas fa-shopping-bag', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Consumer goods, furniture and leisure');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Consumer goods, furniture and leisure' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Clothing', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Clothing');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Shoes', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Shoes');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Leather goods', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Leather goods');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Luggage storage', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Luggage storage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Sport', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Sport');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Hobbies', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Hobbies');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Games and toys', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Games and toys');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Craftsmanship', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Craftsmanship');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Furniture', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Furniture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Office furniture', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Office furniture');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Household appliances', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Household appliances');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Cleaning products', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Cleaning products');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Professional kitchen', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Professional kitchen');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Catering services', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Catering services');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Sports equipment', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Sports equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Cultural articles', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Cultural articles');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Decoration', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Decoration');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Leisure equipment', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Leisure equipment');

-- ==== Sector: Printing, publishing and communication ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Printing, publishing and communication', 'fas fa-print', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Printing, publishing and communication');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Printing, publishing and communication' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Printed materials', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Printed materials');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Impression', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Impression');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Edition', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Edition');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Communication', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Communication');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Public Relations', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Public Relations');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Signage', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Signage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Advertisement', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Advertisement');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Digital communication', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Digital communication');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Events', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Events');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Audiovisual production', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Audiovisual production');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Graphic design', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Graphic design');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Photography', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Photography');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Promotional materials', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Promotional materials');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Media', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Media');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Digital marketing', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Digital marketing');

-- ==== Sector: Research and development ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Research and development', 'fas fa-flask', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Research and development');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Research and development' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Research', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Research');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Development', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Development');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Scientific Council', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Scientific Council');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Technical studies', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Technical studies');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Technical expertise', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Technical expertise');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Innovation', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Innovation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Technology transfer', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Technology transfer');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Feasibility studies', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Feasibility studies');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Prototyping', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Prototyping');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Technical tests', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Technical tests');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Applied research', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Applied research');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Technology watch', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Technology watch');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Research engineering', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Research engineering');

-- ==== Sector: Transport and logistics ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Transport and logistics', 'fas fa-truck', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Transport and logistics');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Transport and logistics' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Transportation equipment', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Transportation equipment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Transportation', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Transportation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Logistics', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Logistics');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Travel', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Travel');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Vehicle rental', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Vehicle rental');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Freight', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Freight');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Transit', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Transit');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Customs clearance', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Customs clearance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Road transport', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Road transport');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Air transport', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Air transport');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Maritime transport', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Maritime transport');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Handling', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Handling');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Storage', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Storage');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Supply chain', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Supply chain');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Delivery', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Delivery');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Fleet management', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Fleet management');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Auto parts', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Auto parts');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Automotive maintenance', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Automotive maintenance');

-- ==== Sector: Business services and miscellaneous services ====
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `icon`, `status`, `serial_number`)
SELECT 169, NULL, 'Business services and miscellaneous services', 'fas fa-briefcase', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Business services and miscellaneous services');
SET @sector_id := (SELECT `id` FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` IS NULL AND `name` = 'Business services and miscellaneous services' LIMIT 1);
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Repair', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Repair');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Maintenance', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Maintenance');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Facility', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Facility');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Hospitality', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Hospitality');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Restoration', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Restoration');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Public services', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Public services');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Advice', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Advice');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Legal services', 1, 8
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Legal services');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Marketing', 1, 9
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Marketing');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Recruitment', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Recruitment');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Social services', 1, 11
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Social services');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Audit', 1, 12
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Audit');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Accounting', 1, 13
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Accounting');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Translation', 1, 14
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Translation');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Interpreting', 1, 15
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Interpreting');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Security', 1, 16
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Security');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Cleaning', 1, 17
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Cleaning');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Project management', 1, 18
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Project management');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Studies and consulting', 1, 19
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Studies and consulting');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Outsourcing', 1, 20
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Outsourcing');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Administrative management', 1, 21
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Administrative management');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Corporate training', 1, 22
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Corporate training');
INSERT INTO `portfolio_sectors` (`language_id`, `parent_id`, `name`, `status`, `serial_number`)
SELECT 169, @sector_id, 'Control and inspection', 1, 23
WHERE NOT EXISTS (SELECT 1 FROM `portfolio_sectors` WHERE `language_id` = 169 AND `parent_id` = @sector_id AND `name` = 'Control and inspection');
