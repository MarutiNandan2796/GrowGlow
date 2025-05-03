-- Create users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Create plant_identifications table
CREATE TABLE IF NOT EXISTS plant_identifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    plant_name VARCHAR(255),
    confidence FLOAT,
    description TEXT,
    care_instructions TEXT,
    status ENUM('pending', 'completed', 'failed') NOT NULL,
    created_at DATETIME NOT NULL,
    completed_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create pest_identifications table
CREATE TABLE IF NOT EXISTS pest_identifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    pest_name VARCHAR(255),
    confidence FLOAT,
    description TEXT,
    treatments JSON,
    status ENUM('pending', 'completed', 'failed') NOT NULL,
    created_at DATETIME NOT NULL,
    completed_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
); 