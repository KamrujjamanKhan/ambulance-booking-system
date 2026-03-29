USE `ambulancehub`;

-- 1. Admins (Password : admin123)
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES 
('Site Administrator', 'admin@example.com', '+8801000000000', '$2y$10$R62Uu46DDCKcy86B1mMfp.T9TK82U7wOG0frNdM5Dsi4hGkxCRxE.', 'admin', NOW())
;

-- 2. Patients (Password for all: patient)
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES 
('Patient X', 'patient01@gmail.com', '01789000000', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('John Doe', 'john.doe@example.com', '01711223344', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('Jane Smith', 'jane.smith@example.com', '01822334455', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('Rahim Uddin', 'rahim@gmail.com', '01933445566', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('Ayesha Khan', 'ayesha.khan@example.com', '01755667788', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('Nusrat Jahan', 'nusrat.jahan@example.com', '01866554433', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('Mahmud Hasan', 'mahmud.hasan@example.com', '01944556677', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW()),
('Farzana Akter', 'farzana.akter@example.com', '01677889900', '$2y$10$CCnrDnTq4AIbcQqOrIgSlObIuDaleRmA9B3KjcIjf2Vg6ajFxU9xm', 'patient', NOW())
;

-- 3. Drivers (Password for all: driver)
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES 
('Driver X', 'driver01@gmail.com', '01890000000', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Kamal Hossain', 'kamal.driver@example.com', '01799887766', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Jamal Mia', 'jamal.driver@example.com', '01888776655', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Selim Reza', 'selim.driver@example.com', '01977665544', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Rafiq Islam', 'rafiq.driver@example.com', '01766778899', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Imran Ali', 'imran.driver@example.com', '01877665522', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Tariq Mahmud', 'tariq.driver@example.com', '01922334488', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW()),
('Biplob Das', 'biplob.driver@example.com', '01655443322', '$2y$10$HPL/70RNitOzCsrIHK2aM.734tP.LAxxIURs/BWPiiTywT.tHgkyK', 'driver', NOW())
;

-- 4. Ambulances
INSERT INTO `ambulances` (`driver_id`, `vehicle_type`, `license_plate`, `status`) VALUES
((SELECT id FROM `users` WHERE `email` = 'driver01@gmail.com'), 'ICU Ambulance', 'DHA-11-2233', 'Available'),
((SELECT id FROM `users` WHERE `email` = 'kamal.driver@example.com'), 'Standard Ambulance', 'DHA-12-4455', 'Available'),
((SELECT id FROM `users` WHERE `email` = 'jamal.driver@example.com'), 'Freezer Ambulance', 'CTG-11-9988', 'Offline'),
((SELECT id FROM `users` WHERE `email` = 'selim.driver@example.com'), 'Standard Ambulance', 'SYL-14-3322', 'Busy'),
((SELECT id FROM `users` WHERE `email` = 'rafiq.driver@example.com'), 'ICU Ambulance', 'DHA-15-7788', 'Available'),
((SELECT id FROM `users` WHERE `email` = 'imran.driver@example.com'), 'Standard Ambulance', 'CTG-18-5544', 'Available'),
((SELECT id FROM `users` WHERE `email` = 'tariq.driver@example.com'), 'Freezer Ambulance', 'KHL-21-6677', 'Offline'),
((SELECT id FROM `users` WHERE `email` = 'biplob.driver@example.com'), 'Standard Ambulance', 'RAJ-09-1122', 'Busy');

-- 5. Bookings
-- A completed trip
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `pickup_lat`, `pickup_lng`, `destination`, `dest_lat`, `dest_lng`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, d.id, 'Gulshan 2, Dhaka', 23.7946, 90.4144, 'Square Hospital', 23.7533, 90.3813, 'Completed', 'Name: Patient X\nPhone: 01789000000\nType: standard\nCondition: General weakness, needs transport.', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)
FROM `users` p, `users` d
WHERE p.email = 'patient01@gmail.com' AND d.email = 'driver01@gmail.com'
LIMIT 1;

-- An active trip (On the way)
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `pickup_lat`, `pickup_lng`, `destination`, `dest_lat`, `dest_lng`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, d.id, 'Banani, Dhaka', 23.7940, 90.4043, 'Evercare Hospital', 23.8078, 90.4285, 'On the way', 'Name: Jane Smith\nPhone: 01822334455\nType: icu\nCondition: Critical, suspected heart attack.', DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW()
FROM `users` p, `users` d
WHERE p.email = 'jane.smith@example.com' AND d.email = 'selim.driver@example.com'
LIMIT 1;

-- A pending trip request
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `pickup_lat`, `pickup_lng`, `destination`, `dest_lat`, `dest_lng`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, NULL, 'Mirpur 10, Dhaka', 23.8070, 90.3687, 'Dhaka Medical College', 23.7266, 90.3976, 'Pending', 'Name: John Doe\nPhone: 01711223344\nType: standard\nCondition: Road accident, leg fracture.', NOW(), NOW()
FROM `users` p
WHERE p.email = 'john.doe@example.com'
LIMIT 1;

-- An accepted trip
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `pickup_lat`, `pickup_lng`, `destination`, `dest_lat`, `dest_lng`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, d.id, 'Uttara, Dhaka', 23.8728, 90.3984, 'BIRDEM Hospital', 23.7385, 90.3957, 'Accepted', 'Name: Rahim Uddin\nPhone: 01933445566\nType: standard\nCondition: Severe asthma attack.', DATE_SUB(NOW(), INTERVAL 30 MINUTE), DATE_SUB(NOW(), INTERVAL 15 MINUTE)
FROM `users` p, `users` d
WHERE p.email = 'rahim@gmail.com' AND d.email = 'kamal.driver@example.com'
LIMIT 1;

-- An arrived trip
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `pickup_lat`, `pickup_lng`, `destination`, `dest_lat`, `dest_lng`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, d.id, 'Dhanmondi, Dhaka', 23.7461, 90.3742, 'Labaid Hospital', 23.7431, 90.3831, 'Arrived', 'Name: Patient X\nPhone: 01789000000\nType: icu\nCondition: Diabetic emergency.', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR)
FROM `users` p, `users` d
WHERE p.email = 'patient01@gmail.com' AND d.email = 'driver01@gmail.com'
LIMIT 1;

-- A cancelled trip
INSERT INTO `bookings` (`patient_id`, `driver_id`, `pickup_location`, `pickup_lat`, `pickup_lng`, `destination`, `dest_lat`, `dest_lng`, `status`, `emergency_details`, `created_at`, `updated_at`)
SELECT p.id, NULL, 'Mohakhali, Dhaka', 23.7788, 90.4005, 'Kurmitola General Hospital', 23.8118, 90.4026, 'Cancelled', 'Name: Jane Smith\nPhone: 01822334455\nType: standard\nCondition: False alarm, condition improved.', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)
FROM `users` p
WHERE p.email = 'jane.smith@example.com'
LIMIT 1;

-- Add demo tracking values for active trips
UPDATE `bookings`
SET `simulated_progress` = 15, `eta_minutes` = 18, `last_progress_at` = NOW()
WHERE `status` = 'Accepted';

UPDATE `bookings`
SET `simulated_progress` = 60, `eta_minutes` = 8, `last_progress_at` = NOW()
WHERE `status` = 'On the way';

UPDATE `bookings`
SET `simulated_progress` = 100, `eta_minutes` = 0, `last_progress_at` = NOW()
WHERE `status` = 'Arrived';
