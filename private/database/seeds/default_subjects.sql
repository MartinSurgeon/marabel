-- Seed: Level of Proficiency Scale (Fixed — not configurable)
USE uaddara_sba;

INSERT IGNORE INTO school_levels (name, code, sort_order) VALUES
  ('Lower Primary', 'LP',  1),
  ('Upper Primary', 'UP',  2),
  ('Junior High School', 'JHS', 3);

-- ── Default Subjects per Level ──────────────────────────────────
-- Lower Primary (B1–B3)
INSERT IGNORE INTO subjects (level_id, subject_name, subject_code, sort_order) VALUES
  (1, 'English Language',       'ENG',  1),
  (1, 'Mathematics',            'MATH', 2),
  (1, 'Natural Science',        'SCI',  3),
  (1, 'Religious & Moral Edu.', 'RME',  4),
  (1, 'Creative Arts',          'CA',   5),
  (1, 'Ghanaian Language',      'GL',   6),
  (1, 'History',                'HIST', 7);

-- Upper Primary (B4–B6)
INSERT IGNORE INTO subjects (level_id, subject_name, subject_code, sort_order) VALUES
  (2, 'English Language',       'ENG',  1),
  (2, 'Mathematics',            'MATH', 2),
  (2, 'Natural Science',        'SCI',  3),
  (2, 'Religious & Moral Edu.', 'RME',  4),
  (2, 'Creative Arts',          'CA',   5),
  (2, 'Ghanaian Language',      'GL',   6),
  (2, 'History',                'HIST', 7),
  (2, 'French',                 'FR',   8),
  (2, 'ICT',                    'ICT',  9),
  (2, 'Social Studies',         'SS',   10);

-- JHS (B7–B9)
INSERT IGNORE INTO subjects (level_id, subject_name, subject_code, sort_order) VALUES
  (3, 'English Language',       'ENG',  1),
  (3, 'Mathematics',            'MATH', 2),
  (3, 'Integrated Science',     'ISCI', 3),
  (3, 'Social Studies',         'SS',   4),
  (3, 'Religious & Moral Edu.', 'RME',  5),
  (3, 'French',                 'FR',   6),
  (3, 'ICT',                    'ICT',  7),
  (3, 'Ghanaian Language',      'GL',   8),
  (3, 'Creative Arts',          'CA',   9),
  (3, 'Career Technology',      'CT',   10);
