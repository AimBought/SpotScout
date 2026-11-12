ALTER TABLE reports
    ADD COLUMN IF NOT EXISTS proposed_latitude DECIMAL(10,8) NULL,
    ADD COLUMN IF NOT EXISTS proposed_longitude DECIMAL(11,8) NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'reports_proposed_location_pair_check'
    ) THEN
        ALTER TABLE reports
            ADD CONSTRAINT reports_proposed_location_pair_check
            CHECK (
                (proposed_latitude IS NULL AND proposed_longitude IS NULL)
                OR (proposed_latitude BETWEEN -90 AND 90 AND proposed_longitude BETWEEN -180 AND 180)
            );
    END IF;
END $$;
