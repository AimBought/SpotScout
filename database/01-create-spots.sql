CREATE TABLE spots (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT spots_status_check CHECK (status IN ('planned', 'active', 'archived'))
);

CREATE INDEX spots_status_idx ON spots (status);
CREATE INDEX spots_created_at_idx ON spots (created_at);
