-- Drop existing tables to avoid conflicts
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;

-- Create Orders Table
CREATE TABLE `orders` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    customer_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postcode VARCHAR(10) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending'
) ENGINE=InnoDB;

-- Create Order Items Table
CREATE TABLE `order_items` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample Orders for a single user
INSERT INTO `orders` (customer_name, email, phone, address, city, state, postcode, subtotal, tax, total_amount, status)
VALUES 
('User', 'alex@example.com', '012-3456789', '123, Jalan Bukit Bintang', 'Kuala Lumpur', 'W.P. Kuala Lumpur', '55100', 850.00, 51.00, 901.00, 'Delivered'),
('User', 'alex@example.com', '019-8765432', '456, Jalan Peel', 'George Town', 'Penang', '10350', 1300.00, 78.00, 1378.00, 'Processing');

INSERT INTO `order_items` (order_id, product_id, product_name, price, quantity, subtotal)
VALUES 
(1, 1, 'Hype Text T-Shirt', 750.00, 1, 750.00),
(1, 2, 'Monogram Print T-Shirt', 100.00, 1, 100.00),
(2, 12, 'Purple Graphic Street Hoodie', 1300.00, 1, 1300.00);
