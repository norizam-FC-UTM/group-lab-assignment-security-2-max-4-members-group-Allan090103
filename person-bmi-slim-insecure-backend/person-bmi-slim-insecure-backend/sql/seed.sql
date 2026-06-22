USE security_bmi_lab;

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM persons;
DELETE FROM users;

ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE persons AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;
-- ==========================================================
-- Seed users table
-- Passwords are intentionally stored as plain text for the
-- insecure starter lab.
-- ==========================================================

INSERT INTO users (name, email, password, password_hash, role) VALUES
('Muhammad Aiman Hakimi', 'aiman@student.utm.my', 'password123', 'password123', 'user'),
('Nur Aisyah Zulkifli', 'aisyah@student.utm.my', 'password123', 'password123', 'user'),
('Ahmad Farhan Roslan', 'farhan@student.utm.my', 'password123', 'password123', 'user'),
('Siti Nur Balqis Ismail', 'balqis@student.utm.my', 'password123', 'password123', 'user'),
('Muhammad Danish Azman', 'danish@student.utm.my', 'password123', 'password123', 'user'),
('Nur Imanina Hassan', 'imanina@student.utm.my', 'password123', 'password123', 'user'),
('Amirul Hakim Rahman', 'amirul@student.utm.my', 'password123', 'password123', 'user'),
('Farah Nadhirah Yusof', 'farah@student.utm.my', 'password123', 'password123', 'user'),
('Haziq Irfan Abdullah', 'haziq@student.utm.my', 'password123', 'password123', 'user'),
('Nurul Syafiqah Kamarudin', 'syafiqah@student.utm.my', 'password123', 'password123', 'user'),
('Mohd Hafiz Jamal', 'hafiz@google.com', 'password123', 'password123', 'user'),
('Nadia Sofea Ramli', 'nadia@google.com', 'password123', 'password123', 'user'),
('Fikri Hazim Othman', 'fikri@google.com', 'password123', 'password123', 'user'),
('Puteri Amira Shafie', 'amira@google.com', 'password123', 'password123', 'user'),

('Siti Hajar Ibrahim', 'siti.hajar@utm.my', 'password123', 'password123', 'staff'),
('Faizal Zainuddin', 'faizal.zainuddin@utm.my', 'password123', 'password123', 'staff'),
('Noraini Salleh', 'noraini.salleh@utm.my', 'password123', 'password123', 'staff'),
('Khairul Anwar Musa', 'khairul.anwar@utm.my', 'password123', 'password123', 'staff'),

('Amran Hamid', 'amran.hamid@utm.my', 'password123', 'password123', 'admin'),
('Mazlina Ahmad', 'mazlina.ahmad@utm.my', 'password123', 'password123', 'admin');


-- ==========================================================
-- Seed persons table
-- Each record belongs to one user_id.
-- BMI values are pre-calculated for the insecure starter lab.
-- ==========================================================

INSERT INTO persons (user_id, name, age, height, weight, bmi, category, notes) VALUES
(1, 'Muhammad Aiman Hakimi', 21, 1.70, 65.00, 22.49, 'Normal', 'Student sample BMI record'),
(2, 'Nur Aisyah Zulkifli', 22, 1.60, 49.00, 19.14, 'Normal', 'Healthy BMI range'),
(3, 'Ahmad Farhan Roslan', 21, 1.75, 82.00, 26.78, 'Overweight', 'Needs weight monitoring'),
(4, 'Siti Nur Balqis Ismail', 23, 1.55, 43.00, 17.90, 'Underweight', 'Low BMI sample'),
(5, 'Muhammad Danish Azman', 22, 1.68, 72.00, 25.51, 'Overweight', 'Slightly above normal range'),
(6, 'Nur Imanina Hassan', 21, 1.62, 54.00, 20.58, 'Normal', 'Normal BMI record'),
(7, 'Amirul Hakim Rahman', 24, 1.72, 95.00, 32.11, 'Obese', 'High BMI sample for monitoring'),
(8, 'Farah Nadhirah Yusof', 22, 1.58, 50.00, 20.03, 'Normal', 'Regular BMI check'),
(9, 'Haziq Irfan Abdullah', 23, 1.80, 68.00, 20.99, 'Normal', 'Tall student with normal BMI'),
(10, 'Nurul Syafiqah Kamarudin', 21, 1.57, 61.00, 24.75, 'Normal', 'Near upper normal range'),

(11, 'Mohd Hafiz Jamal', 25, 1.69, 78.00, 27.31, 'Overweight', 'Sample external user record'),
(12, 'Nadia Sofea Ramli', 24, 1.63, 47.00, 17.69, 'Underweight', 'Underweight example record'),
(13, 'Fikri Hazim Othman', 26, 1.74, 88.00, 29.07, 'Overweight', 'Close to obese threshold'),
(14, 'Puteri Amira Shafie', 23, 1.59, 52.00, 20.57, 'Normal', 'Normal BMI record'),

(15, 'Siti Hajar Ibrahim', 35, 1.58, 56.00, 22.43, 'Normal', 'Staff sample BMI record'),
(16, 'Faizal Zainuddin', 38, 1.73, 85.00, 28.40, 'Overweight', 'Staff overweight sample'),
(17, 'Noraini Salleh', 42, 1.61, 63.00, 24.30, 'Normal', 'Staff health monitoring record'),
(18, 'Khairul Anwar Musa', 40, 1.76, 98.00, 31.64, 'Obese', 'Staff high BMI sample'),

(19, 'Amran Hamid', 50, 1.70, 74.00, 25.61, 'Overweight', 'Admin sample BMI record'),
(20, 'Mazlina Ahmad', 45, 1.60, 58.00, 22.66, 'Normal', 'Admin sample BMI record');