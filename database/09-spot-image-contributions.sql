ALTER TABLE spot_images
    ADD COLUMN IF NOT EXISTS contributor_user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'approved',
    ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL;

UPDATE spot_images si
SET contributor_user_id = s.user_id
FROM spots s
WHERE si.spot_id = s.id
  AND si.contributor_user_id IS NULL;

ALTER TABLE spot_images DROP CONSTRAINT IF EXISTS spot_images_status_check;

ALTER TABLE spot_images
    ADD CONSTRAINT spot_images_status_check
    CHECK (status IN ('pending', 'approved', 'rejected'));

CREATE INDEX IF NOT EXISTS spot_images_pending_idx
    ON spot_images (created_at ASC)
    WHERE status = 'pending';

CREATE INDEX IF NOT EXISTS spot_images_spot_approved_idx
    ON spot_images (spot_id, created_at ASC)
    WHERE status = 'approved';
