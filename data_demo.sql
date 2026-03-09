USE `ambulancehub`;

-- Password for all demo accounts is the same as their role (admin123, patient01, driver01) but the hashes are identical for the same role

-- 1. Admins
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES 
('Site Administrator', 'admin@example.com', '+8801000000000', '$2y$10$R62Uu46DDCKcy86B1mMfp.T9TK82U7wOG0frNdM5Dsi4hGkxCRxE.', 'admin', NOW())
ON DUPLICATE KEY UPDATE email = email;

-- 2. Patients (Password for all: patient)
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES 
('Patient X', 'patient01@gmail.com', '01789000000', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('John Doe', 'john.doe@example.com', '01711223344', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('Jane Smith', 'jane.smith@example.com', '01822334455', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('Rahim Uddin', 'rahim@gmail.com', '01933445566', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW())
ON DUPLICATE KEY UPDATE email = email;

-- 3. Drivers (Password for all: driver)
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES 
('Driver X', 'driver01@gmail.com', '01890000000', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Kamal Hossain', 'kamal.driver@example.com', '01799887766', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Jamal Mia', 'jamal.driver@example.com', '01888776655', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Selim Reza', 'selim.driver@example.com', '01977665544', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW())
ON DUPLICATE KEY UPDATE email = email;

-- 4. Ambulances
INSERT IGNORE INTO `ambulances` (`driver_id`, `vehicle_type`, `license_plate`, `status`)
SELECT id, 'ICU Ambulance', 'DHA-11-2233', 'Available' FROM `users` WHERE `email` = 'driver01@gmail.com';

INSERT IGNORE INTO `ambulances` (`driver_id`, `vehicle_type`, `license_plate`, `status`)
SELECT id, 'Standard Ambulance', 'DHA-12-4455', 'Available' FROM `users` WHERE `email` = 'kamal.driver@example.com';

INSERT IGNORE INTO `ambulances` (`driver_id`, `vehicle_type`, `license_plate`, `status`)
SELECT id, 'Freezer Ambulance', 'CTG-11-9988', 'Offline' FROM `users` WHERE `email` = 'jamal.driver@example.com';

INSERT IGNORE INTO `ambulances` (`driver_id`, `vehicle_type`, `license_plate`, `status`)
SELECT id, 'Standard Ambulance', 'SYL-14-3322', 'Busy' FROM `users` WHERE `email` = 'selim.driver@example.com';

-- 5. Bookings
-- A completed trip
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `destination`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, d.id, 'Gulshan 2, Dhaka', 'Square Hospital', 'Completed', 'Name: Patient X\nPhone: 01789000000\nType: standard\nCondition: General weakness, needs transport.', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)
FROM `users` p, `users` d
WHERE p.email = 'patient01@gmail.com' AND d.email = 'driver01@gmail.com'
LIMIT 1;

-- An active trip (On the way)
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `destination`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, d.id, 'Banani, Dhaka', 'Evercare Hospital', 'On the way', 'Name: Jane Smith\nPhone: 01822334455\nType: icu\nCondition: Critical, suspected heart attack.', DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW()
FROM `users` p, `users` d
WHERE p.email = 'jane.smith@example.com' AND d.email = 'selim.driver@example.com'
LIMIT 1;

-- A pending trip request
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `destination`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, NULL, 'Mirpur 10, Dhaka', 'Dhaka Medical College', 'Pending', 'Name: John Doe\nPhone: 01711223344\nType: standard\nCondition: Road accident, leg fracture.', NOW(), NOW()
FROM `users` p
WHERE p.email = 'john.doe@example.com'
LIMIT 1;

-- An accepted trip
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `destination`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, d.id, 'Uttara, Dhaka', 'BIRDEM Hospital', 'Accepted', 'Name: Rahim Uddin\nPhone: 01933445566\nType: standard\nCondition: Severe asthma attack.', DATE_SUB(NOW(), INTERVAL 30 MINUTE), DATE_SUB(NOW(), INTERVAL 15 MINUTE)
FROM `users` p, `users` d
WHERE p.email = 'rahim@gmail.com' AND d.email = 'kamal.driver@example.com'
LIMIT 1;

-- An arrived trip
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `destination`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, d.id, 'Dhanmondi, Dhaka', 'Labaid Hospital', 'Arrived', 'Name: Patient X\nPhone: 01789000000\nType: icu\nCondition: Diabetic emergency.', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR)
FROM `users` p, `users` d
WHERE p.email = 'patient01@gmail.com' AND d.email = 'driver01@gmail.com'
LIMIT 1;

-- A cancelled trip
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `destination`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, NULL, 'Mohakhali, Dhaka', 'Kurmitola General Hospital', 'Cancelled', 'Name: Jane Smith\nPhone: 01822334455\nType: standard\nCondition: False alarm, condition improved.', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)
FROM `users` p
WHERE p.email = 'jane.smith@example.com'
LIMIT 1;
