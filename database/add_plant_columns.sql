-- Add last_watered column if it doesn't exist
SET @dbname = 'plantpal';
SET @tablename = 'plants';
SET @columnname = 'last_watered';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 'Column last_watered already exists'",
  "ALTER TABLE plants ADD COLUMN last_watered DATE DEFAULT NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add watering_frequency column if it doesn't exist
SET @columnname = 'watering_frequency';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 'Column watering_frequency already exists'",
  "ALTER TABLE plants ADD COLUMN watering_frequency INT DEFAULT NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing plants with a default watering frequency if needed
UPDATE plants SET watering_frequency = 7 WHERE watering_frequency IS NULL; 