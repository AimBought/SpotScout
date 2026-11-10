ALTER TABLE spots
    ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'pending';

ALTER TABLE spots
    ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL;

ALTER TABLE spots
    ALTER COLUMN status SET DEFAULT 'pending';

ALTER TABLE spots DROP CONSTRAINT IF EXISTS spots_status_check;

UPDATE spots
SET status = CASE status
    WHEN 'active' THEN 'approved'
    WHEN 'planned' THEN 'pending'
    WHEN 'archived' THEN 'rejected'
    ELSE 'pending'
END
WHERE status NOT IN ('pending', 'approved', 'rejected');

ALTER TABLE spots
    ADD CONSTRAINT spots_status_check
    CHECK (status IN ('pending', 'approved', 'rejected'));

CREATE TABLE IF NOT EXISTS notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS notifications_user_unread_created_idx
    ON notifications (user_id, created_at DESC)
    WHERE is_read = FALSE;
