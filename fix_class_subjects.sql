-- Fix class_subjects table - restore missing columns
USE uaddara_sba;

-- Step 1: Add missing columns if they don't exist
ALTER TABLE class_subjects 
  ADD COLUMN IF NOT EXISTS term_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER teacher_id,
  ADD COLUMN IF NOT EXISTS is_locked TINYINT(1) NOT NULL DEFAULT 0 AFTER term_id,
  ADD COLUMN IF NOT EXISTS locked_at TIMESTAMP NULL AFTER is_locked;

-- Step 2: Remove unsafe DEFAULT 1 after adding
ALTER TABLE class_subjects MODIFY COLUMN term_id INT UNSIGNED NOT NULL;

-- Step 3: Add foreign key if not present
SET @fk_exists = (
  SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
  WHERE CONSTRAINT_SCHEMA = 'uaddara_sba'
    AND TABLE_NAME = 'class_subjects'
    AND CONSTRAINT_NAME = 'fk_class_subj_term'
);

SET @sql = IF(@fk_exists = 0,
  'ALTER TABLE class_subjects ADD CONSTRAINT fk_class_subj_term FOREIGN KEY (term_id) REFERENCES terms(id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 4: Add unique key if not present  
ALTER IGNORE TABLE class_subjects 
  ADD UNIQUE KEY IF NOT EXISTS uq_class_subj_term (class_id, subject_id, term_id);

SELECT 'class_subjects table fixed successfully!' as status;
DESCRIBE class_subjects;
