CREATE DATABASE IF NOT EXISTS bookstore_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE bookstore_db;

CREATE TABLE IF NOT EXISTS user_name_tbl (
  name_id INT AUTO_INCREMENT PRIMARY KEY,
  fname_fld VARCHAR(100) NOT NULL,
  lname_fld VARCHAR(100) NOT NULL,
  name_created_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  name_updated_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_account_tbl (
  account_id INT AUTO_INCREMENT PRIMARY KEY,
  name_id INT NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone_encrypted TEXT NULL,
  phone_iv VARCHAR(255) NULL,
  phone_tag VARCHAR(255) NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  account_created_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  account_updated_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_account_name
    FOREIGN KEY (name_id) REFERENCES user_name_tbl(name_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_address_tbl (
  address_id INT AUTO_INCREMENT PRIMARY KEY,
  account_id INT NOT NULL,
  country_fld VARCHAR(100) NOT NULL,
  state_province_fld VARCHAR(100) NOT NULL,
  city_town_fld VARCHAR(100) NOT NULL,
  barangay_fld VARCHAR(100) NULL,
  apartment_unit_fld VARCHAR(100) NULL,
  streetnum_fld VARCHAR(120) NOT NULL,
  housenum_fld VARCHAR(120) NOT NULL,
  address_created_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  address_updated_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_address_account
    FOREIGN KEY (account_id) REFERENCES user_account_tbl(account_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories_tbl (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name_fld VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS books_tbl (
  book_id INT AUTO_INCREMENT PRIMARY KEY,
  title_fld VARCHAR(255) NOT NULL,
  author_fld VARCHAR(255) NOT NULL,
  description_fld TEXT NULL,
  isbn_fld VARCHAR(64) NOT NULL UNIQUE,
  price_fld DECIMAL(10, 2) NOT NULL,
  stock_qty_fld INT NOT NULL DEFAULT 0,
  book_cover_image VARCHAR(255) NULL,
  original_cover_image VARCHAR(255) NULL,
  image_scale DECIMAL(8, 2) NOT NULL DEFAULT 1.00,
  image_offset_x DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
  image_offset_y DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
  book_created_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  book_updated_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS book_categories_tbl (
  book_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (book_id, category_id),
  CONSTRAINT fk_book_category_book
    FOREIGN KEY (book_id) REFERENCES books_tbl(book_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_book_category_category
    FOREIGN KEY (category_id) REFERENCES categories_tbl(category_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders_tbl (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  account_id INT NOT NULL,
  total_amount_fld DECIMAL(10, 2) NOT NULL,
  payment_encrypted TEXT NULL,
  payment_iv VARCHAR(255) NULL,
  payment_tag VARCHAR(255) NULL,
  order_status_fld VARCHAR(50) NOT NULL DEFAULT 'pending',
  order_created_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  order_updated_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_account
    FOREIGN KEY (account_id) REFERENCES user_account_tbl(account_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items_tbl (
  order_item_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  book_id INT NOT NULL,
  quantity_fld INT NOT NULL,
  price_at_purchase_fld DECIMAL(10, 2) NOT NULL,
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders_tbl(order_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_order_items_book
    FOREIGN KEY (book_id) REFERENCES books_tbl(book_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews_tbl (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  account_id INT NOT NULL,
  book_id INT NOT NULL,
  rating_fld TINYINT NOT NULL,
  comment_fld TEXT NULL,
  review_created_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_account
    FOREIGN KEY (account_id) REFERENCES user_account_tbl(account_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_reviews_book
    FOREIGN KEY (book_id) REFERENCES books_tbl(book_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_tokens_tbl (
  token_id INT AUTO_INCREMENT PRIMARY KEY,
  account_id INT NOT NULL,
  token VARCHAR(128) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NOT NULL,
  CONSTRAINT fk_tokens_account
    FOREIGN KEY (account_id) REFERENCES user_account_tbl(account_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO categories_tbl (category_id, category_name_fld) VALUES
  (1, 'Fantasy'),
  (2, 'Self-help'),
  (3, 'History'),
  (4, 'Children'),
  (5, 'Classic'),
  (6, 'Poetry'),
  (7, 'Science'),
  (8, 'Science Fiction'),
  (9, 'Thriller')
ON DUPLICATE KEY UPDATE category_name_fld = VALUES(category_name_fld);

INSERT INTO books_tbl (
  book_id, title_fld, author_fld, description_fld, isbn_fld, price_fld, stock_qty_fld, book_cover_image
) VALUES
  (3, 'The Hobbit', 'J.R.R. Tolkien', 'A comfortable, stay-at-home hobbit is swept into an epic quest to reclaim a lost dwarf kingdom from a fearsome dragon.', '9780547928227', 650.00, 40, 'book_1777008205_2c16ce23a86189fc.jpeg'),
  (4, 'Atomic Habits', 'James Clear', 'A practical and proven framework for improving every day by forming good habits and breaking bad ones.', '9780735211292', 1195.00, 84, 'book_1777096179_1a2d15b4195784bd.jpeg'),
  (5, 'A Brief History of Humankind', 'Yuval Noah Harari', 'An exploration of how biology and history have defined us and enhanced our understanding of what it means to be human.', '9780062316097', 1149.99, 12, 'book_1777096154_2770b70bd6267c0b.jpeg'),
  (6, 'Where the Wild Things Are', 'Maurice Sendak', 'A young boy named Max journeys to an island of terrifying but easily tamed beasts after being sent to bed without supper.', '9780060254926', 450.00, 17, 'book_1777096092_a5d80d0c7eb79de2.jpeg'),
  (8, 'To Kill a Mockingbird', 'Harper Lee', 'A gripping, heart-wrenching, and wholly remarkable tale of coming-of-age in a South poisoned by virulent prejudice.', '9780060935467', 550.00, 54, 'book_1777096240_4b2b409e1a67eb31.jpeg'),
  (9, 'The Sun and Her Flowers', 'Rupi Kaur', 'A vibrant and transcendent journey about growth and healing, ancestry and honoring one''s roots.', '9781449486792', 799.00, 22, 'book_1777096311_dc1a089f7e488f76.jpeg'),
  (10, 'Pride and Prejudice', 'Jane Austen', 'A classic novel following the turbulent relationship between Elizabeth Bennet and the haughty Mr. Darcy.', '9780141439518', 350.00, 59, 'book_1777096372_f5658d351df78d9a.jpeg'),
  (11, 'A Brief History of Time', 'Stephen Hawking', 'A landmark volume in science writing that explores fundamental questions about the universe, time, and space.', '9780553380163', 850.00, 14, 'book_1777096434_2ae1980d293a17a1.jpeg'),
  (12, 'Dune', 'Frank Herbert', 'Set on the desert planet Arrakis, this epic tale follows young Paul Atreides as he navigates a complex political and ecological landscape.', '9780441172719', 699.00, 75, 'book_1777183910_1480d2ecdb830111.jpeg'),
  (13, 'The Girl with the Dragon', 'Stieg Larsson', 'A disgraced journalist and a brilliant but troubled hacker team up to solve a decades-old disappearance.', '9780307949486', 599.00, 24, 'book_1777096617_e033c28ef7f5741e.jpeg')
ON DUPLICATE KEY UPDATE
  title_fld = VALUES(title_fld),
  author_fld = VALUES(author_fld),
  description_fld = VALUES(description_fld),
  price_fld = VALUES(price_fld),
  stock_qty_fld = VALUES(stock_qty_fld),
  book_cover_image = VALUES(book_cover_image);

INSERT INTO book_categories_tbl (book_id, category_id) VALUES
  (3, 1), (4, 2), (5, 3), (6, 4), (8, 5), (9, 6), (10, 5), (11, 7), (12, 8), (13, 9)
ON DUPLICATE KEY UPDATE category_id = VALUES(category_id);

INSERT INTO user_name_tbl (name_id, fname_fld, lname_fld) VALUES
  (9001, 'System', 'Admin')
ON DUPLICATE KEY UPDATE fname_fld = VALUES(fname_fld), lname_fld = VALUES(lname_fld);

INSERT INTO user_account_tbl (
  account_id, name_id, email, password_hash, phone_encrypted, phone_iv, phone_tag, role
) VALUES (
  9001,
  9001,
  'admin@example.com',
  '$2y$10$Zf/24sMslGfrd04Xauv8Eu6dA41tRt4JOgdlELNq13hOqiY4S8YjK',
  NULL,
  NULL,
  NULL,
  'admin'
)
ON DUPLICATE KEY UPDATE
  email = VALUES(email),
  password_hash = VALUES(password_hash),
  role = VALUES(role);
