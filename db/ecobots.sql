-- Database schema for Ecobots Recycling Vending Machine
CREATE DATABASE IF NOT EXISTS ecobots;
USE ecobots;

-- Users table (students)
CREATE TABLE users (
    student_number VARCHAR(20) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin table
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory table
CREATE TABLE inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    bottle_count INT DEFAULT 0,
    profit_earned DECIMAL(10,2) DEFAULT 0.00,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Food rewards table
CREATE TABLE food_rewards (
    reward_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    stock_A INT DEFAULT 0,
    stock_B INT DEFAULT 0,
    price_in_bottles INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transactions table
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20) NOT NULL,
    bottles_inserted INT NOT NULL,
    reward_claimed INT DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_number) REFERENCES users(student_number),
    FOREIGN KEY (reward_claimed) REFERENCES food_rewards(reward_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial admin account
INSERT INTO admin (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Insert sample food rewards
INSERT INTO food_rewards (name, description, stock_A, stock_B, price_in_bottles)
VALUES 
    ('Option A: Sandwich', 'Fresh sandwich with choice of filling', 50, 0, 10),
    ('Option B: Snack Pack', 'Assorted fruits and snacks', 0, 50, 8);

-- Initialize inventory
INSERT INTO inventory (bottle_count, profit_earned) VALUES (0, 0.00);

-- Create trigger to update inventory on bottle deposits
DELIMITER //
CREATE TRIGGER update_inventory_after_deposit
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    IF NEW.bottles_inserted > 0 THEN
        UPDATE inventory 
        SET bottle_count = bottle_count + NEW.bottles_inserted,
            profit_earned = profit_earned + (NEW.bottles_inserted * 0.05); -- Assuming $0.05 profit per bottle
    END IF;
END//
DELIMITER ;

-- Create trigger to update reward stock when claimed
DELIMITER //
CREATE TRIGGER update_reward_stock_after_claim
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    IF NEW.reward_claimed IS NOT NULL THEN
        UPDATE food_rewards 
        SET stock_A = CASE 
                        WHEN reward_id = 1 THEN stock_A - 1 
                        ELSE stock_A 
                      END,
            stock_B = CASE 
                        WHEN reward_id = 2 THEN stock_B - 1 
                        ELSE stock_B 
                      END
        WHERE reward_id = NEW.reward_claimed;
    END IF;
END//
DELIMITER ;