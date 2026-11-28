WITH sample_data AS (
    SELECT
        number,
        titles[1 + ((number - 1) % cardinality(titles))] AS place_name,
        categories[1 + ((number - 1) % cardinality(categories))] AS category,
        49.0 + ((number * 37) % 500) / 100.0 AS latitude,
        14.1 + ((number * 53) % 990) / 100.0 AS longitude
    FROM generate_series(1, 50) AS series(number)
    CROSS JOIN (SELECT ARRAY[
        'Abandoned', 'Ruins', 'Underground', 'Water', 'Forest', 'Mountains',
        'Interior', 'Street', 'Rural', 'Architecture', 'Industrial', 'Urban',
        'Nature', 'Drone', 'Night'
    ]::TEXT[] AS categories) category_values
    CROSS JOIN (SELECT ARRAY[
        'Old Factory', 'Railway Bridge', 'Hidden Courtyard', 'Stone Quarry',
        'Forest Clearing', 'Mountain Viewpoint', 'Empty Warehouse', 'Old Chapel',
        'Riverside Path', 'Forgotten Station'
    ]::TEXT[] AS titles) title_values
), named_data AS (
    SELECT
        number,
        format('TEST SPOT #%s - %s', lpad(number::TEXT, 3, '0'), place_name) AS title,
        category,
        latitude,
        longitude
    FROM sample_data
)
INSERT INTO spots (title, description, status, latitude, longitude, category, image_path, user_id)
SELECT
    named_data.title,
    'Testowy spot SpotScout dodany do sprawdzenia paginacji w Explore.',
    'approved',
    named_data.latitude,
    named_data.longitude,
    jsonb_build_array(named_data.category)::TEXT,
    NULL,
    NULL
FROM named_data
WHERE NOT EXISTS (
    SELECT 1 FROM spots existing WHERE existing.title = named_data.title
);

SELECT COUNT(*) AS test_spot_count
FROM spots
WHERE title LIKE 'TEST SPOT #%';
