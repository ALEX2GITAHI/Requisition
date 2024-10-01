-- Create groups table if not exists
CREATE TABLE IF NOT EXISTS groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(255) NOT NULL,
    total_account_balance DECIMAL(15, 2) NOT NULL,
    group_logo LONGBLOB  -- Column for storing the group logo as binary data
);


-- Create users table if not exists
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,   -- Added first name
    last_name VARCHAR(50) NOT NULL,    -- Added last name
    phone_number VARCHAR(20) NOT NULL, -- Added phone number
    password VARCHAR(255) NOT NULL,
    role ENUM('treasurer', 'secretary', 'chairperson', 'patron', 'lcc_treasurer', 'lcc_secretary', 'lcc_chair', 'admin', 'user') NOT NULL,
    group_id INT,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL
);

-- Create requisitions table if not exists
-- Create requisitions table if not exists
CREATE TABLE IF NOT EXISTS requisitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT,
    created_by INT,
    updated_by INT,  -- New column for tracking who last updated the requisition
    total_amount DECIMAL(10, 2),
    requisition_pdf LONGBLOB,
    approved_by INT,  -- Column to store who approved the requisition
    status ENUM(
        'Pending', 
        'Treasurer Approved', 
        'Secretary Approved', 
        'Chairperson Approved', 
        'Patron Approved', 
        'LCC Treasurer Approved', 
        'LCC Secretary Approved', 
        'LCC Chairperson Approved', 
        'Disbursed', 
        'Disapproved'
    ) DEFAULT 'Pending',
    disapproval_comment TEXT,  -- Stores the comment when a requisition is disapproved
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL  -- Foreign key for updated_by
);

-- Create approvals table if not exists
CREATE TABLE IF NOT EXISTS approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_id INT,
    group_id INT,  -- Added group_id to track which group's approval it belongs to
    role ENUM('treasurer', 'secretary', 'chairperson', 'patron', 'lcc_treasurer', 'lcc_secretary', 'lcc_chair'),
    approved_by INT,
    status ENUM('approved', 'rejected') DEFAULT 'approved',
    comment TEXT,  -- Approval or disapproval comment
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requisition_id) REFERENCES requisitions(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE -- Link approval to group
);

-- Create requisition_items table if not exists
CREATE TABLE IF NOT EXISTS requisition_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_id INT,
    item_name VARCHAR(255),
    item_cost DECIMAL(10, 2),
    item_quantity INT,
    total_cost DECIMAL(10, 2),
    FOREIGN KEY (requisition_id) REFERENCES requisitions(id) ON DELETE CASCADE
);

-- Insert groups with account balances
INSERT INTO groups (group_name, total_account_balance) 
VALUES 
('admin', 1000.00),  -- Admin group with an initial balance of 1000
('youth', 500.00);   -- Youth group with an initial balance of 500

-- Insert users with default passwords (use hashed passwords in production)
INSERT INTO users (username, first_name, last_name, phone_number, password, role, group_id) 
VALUES 
('admin', 'admin', 'admin', '1234567890', '1111', 'admin', 1),  -- Admin user
('youth', 'JANET', 'KARANI', '0987654321', '3868', 'treasurer', 2);  -- Youth treasurer

-- (Optional) You can insert default approvals for testing (replace 1 and 2 with actual requisition_id and user_id)
-- INSERT INTO approvals (requisition_id, group_id, role, approved_by, status, comment) VALUES (1, 1, 'treasurer', 1, 'approved', 'Approved for processing');
