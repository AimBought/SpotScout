CREATE TABLE IF NOT EXISTS spot_bookmarks (
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    spot_id BIGINT NOT NULL REFERENCES spots(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, spot_id)
);

CREATE INDEX IF NOT EXISTS idx_spot_bookmarks_user_created
    ON spot_bookmarks (user_id, created_at DESC);
