-- Create common_plants table
CREATE TABLE IF NOT EXISTS common_plants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    scientific_name VARCHAR(100),
    description TEXT NOT NULL,
    care_instructions TEXT,
    light_requirements VARCHAR(100),
    water_requirements VARCHAR(100),
    temperature_range VARCHAR(100),
    humidity_requirements VARCHAR(100),
    soil_type VARCHAR(100),
    fertilizer_needs VARCHAR(100),
    growth_rate VARCHAR(50),
    mature_height VARCHAR(50),
    toxicity_level VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 