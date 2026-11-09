ALTER TABLE spots
    ALTER COLUMN category TYPE TEXT USING category::TEXT;
