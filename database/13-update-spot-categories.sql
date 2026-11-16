UPDATE spots
SET category = REPLACE(category, 'Drone Friendly', 'Drone')
WHERE category LIKE '%Drone Friendly%';
