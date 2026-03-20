-- Default Admin User Seed
-- Uaddara Basic School SBA

INSERT INTO users (full_name, email, phone, role, password_hash, is_active, created_at)
VALUES (
    'System Administrator',
    'admin@uaddara.edu.gh',
    '0240000000',
    'admin',
    '$2y$12$ozNQCU/NHJZVOS7.l8da9ezCuFhoFwvsBVB/MXP5.D7zacsssGfigW', -- password123
    1,
    NOW()
);
