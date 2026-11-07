ALTER TABLE users
    ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255) NULL;

ALTER TABLE spots
    ADD COLUMN IF NOT EXISTS user_id BIGINT NULL;

DO $$
DECLARE
    constraint_record RECORD;
BEGIN
    FOR constraint_record IN
        SELECT constraint_name.conname
        FROM pg_constraint AS constraint_name
        JOIN pg_attribute AS column_info
            ON column_info.attrelid = constraint_name.conrelid
            AND column_info.attnum = ANY (constraint_name.conkey)
        WHERE constraint_name.conrelid = 'spots'::regclass
            AND constraint_name.contype = 'f'
            AND column_info.attname = 'user_id'
    LOOP
        EXECUTE format('ALTER TABLE spots DROP CONSTRAINT %I', constraint_record.conname);
    END LOOP;
END $$;

ALTER TABLE spots
    ADD CONSTRAINT spots_user_id_fkey
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
