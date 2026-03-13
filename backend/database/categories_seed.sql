-- Seed categories if they don't exist
INSERT INTO categorias (nom_cat, created_at, updated_at)
SELECT 'Restauració', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nom_cat = 'Restauració');

INSERT INTO categorias (nom_cat, created_at, updated_at)
SELECT 'Moda i Complements', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nom_cat = 'Moda i Complements');

INSERT INTO categorias (nom_cat, created_at, updated_at)
SELECT 'Serveis', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nom_cat = 'Serveis');
