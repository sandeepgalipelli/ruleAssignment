CREATE DATABASE IF NOT EXISTS rule_assignment_db;
USE rule_assignment_db;

CREATE TABLE IF NOT EXISTS rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('condition', 'decision') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    rule_id INT NOT NULL,
    parent_assignment_id INT NULL,
    tier TINYINT NOT NULL,
    sort_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_assignment_group FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    CONSTRAINT fk_assignment_rule FOREIGN KEY (rule_id) REFERENCES rules(id),
    CONSTRAINT fk_assignment_parent FOREIGN KEY (parent_assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
);

CREATE INDEX idx_assignments_group ON assignments(group_id);
CREATE INDEX idx_assignments_parent ON assignments(parent_assignment_id);

INSERT INTO rules(name, type) VALUES
('Decision Rule 1', 'decision'),
('Condition Rule 1', 'condition'),
('Decision Rule 2', 'decision'),
('Condition Rule 2', 'condition'),
('Decision Rule 3', 'decision');

