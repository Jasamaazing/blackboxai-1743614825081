-- SQLite schema for Ecobots Recycling Vending Machine
BEGIN TRANSACTION;

-- Users table (students)
CREATE TABLE users (
    student_number TEXT PRIMARY KEY,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    profile_picture TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin table
CREATE TABLE admin (
    admin_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inventory table
CREATE TABLE inventory (
    inventory_id INTEGER PRIMARY KEY AUTOINCREMENT,
    bottle_count INTEGER DEFAULT 0,
    profit_earned REAL DEFAULT 0.00,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Food rewards table
CREATE TABLE food_rewards (
    reward_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    stock_A INTEGER DEFAULT 0,
    stock_B INTEGER DEFAULT 0,
    price_in_bottles INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Transactions table
CREATE TABLE transactions (
    transaction_id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_number TEXT NOT NULL,
    bottles_inserted INTEGER NOT NULL,
    reward_claimed INTEGER DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_number) REFERENCES users(student_number),
    FOREIGN KEY (reward_claimed) REFERENCES food_rewards(reward_id)
);

-- Insert initial admin account (password: password)
INSERT INTO admin (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample food rewards
INSERT INTO food_rewards (name, description, stock_A, stock_B, price_in_bottles)
VALUES 
    ('Option A: Sandwich', 'Fresh sandwich with choice of filling', 50, 0, 10),
    ('Option B: Snack Pack', 'Assorted fruits and snacks', 0, 50, 8);

-- Initialize inventory
INSERT INTO inventory (bottle_count, profit_earned) VALUES (0, 0.00);

COMMIT;