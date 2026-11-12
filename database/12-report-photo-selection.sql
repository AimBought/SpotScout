ALTER TABLE reports
    ADD COLUMN IF NOT EXISTS proposed_photo_keys JSONB NOT NULL DEFAULT '[]'::jsonb;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'reports_proposed_photo_keys_array_check'
    ) THEN
        ALTER TABLE reports
            ADD CONSTRAINT reports_proposed_photo_keys_array_check
            CHECK (jsonb_typeof(proposed_photo_keys) = 'array');
    END IF;
END $$;
