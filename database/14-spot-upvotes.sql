CREATE TABLE IF NOT EXISTS spot_upvotes (
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    spot_id BIGINT NOT NULL REFERENCES spots(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT spot_upvotes_user_spot_unique UNIQUE (user_id, spot_id)
);

CREATE INDEX IF NOT EXISTS spot_upvotes_spot_id_idx ON spot_upvotes (spot_id);
