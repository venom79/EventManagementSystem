-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2025 at 08:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eventmanagementsystemdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `userName` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `userName`, `password`, `created_at`) VALUES
(3, 'aditya', '$2y$10$VwM7t7TrSyv.3Hf/3wBRaO/QN5lJIu2l7xX0uNw8qTlj2Uo/bDjYq', '2025-03-16 05:38:45');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `venue_id` int(11) NOT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `name`, `description`, `date`, `time`, `venue_id`, `capacity`, `status`, `created_at`) VALUES
(1, 20, 'technotronics', 'a tech evrnt', '2025-03-12', '08:30:00', 4, 125, 'upcoming', '2025-03-09 13:52:37'),
(5, 20, 'some random event', 'wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww', '2025-03-14', '18:30:00', 3, 300, 'upcoming', '2025-03-10 15:29:42'),
(7, 20, 'hrush', 'birthday event', '2025-03-20', '12:30:00', 4, 405, 'upcoming', '2025-03-11 07:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `booking_type` enum('venue','vendor') NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `booking_id`, `booking_type`, `message`, `status`, `created_at`) VALUES
(7, 14, 9, 'venue', 'Dear aditya,\nWe\'re excited to inform you that your booking for Stone Water Eco Resort. on 2025-03-29 has been confirmed! 🎊\nWe look forward to hosting your event. If you have any special requirements or need further assistance, feel free to reach out.\nThank you for choosing us!\nBest Regards,\nStone Water Eco Resort.', 'read', '2025-03-23 14:52:58'),
(15, 14, 1, 'vendor', 'Dear aditya,\nWe regret to inform you that your booking at adityaVendors on 2025-03-28 has been cancelled. 😔\nWe understand this might be disappointing, and we sincerely apologize for any inconvenience caused. If you would like to reschedule or need further assistance, please do not hesitate to contact us.\nWe truly appreciate your interest in adityaVendors and hope to serve you in the future.\nBest regards,\nadityaVendors Team', 'read', '2025-03-23 19:41:36');

-- --------------------------------------------------------

--
-- Table structure for table `organizers`
--

CREATE TABLE `organizers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `experience` int(11) NOT NULL CHECK (`experience` >= 0),
  `website` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `speciality` text NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizers`
--

INSERT INTO `organizers` (`id`, `user_id`, `company_name`, `experience`, `website`, `instagram`, `speciality`, `description`) VALUES
(7, 20, 'organizingExpo', 5, 'https://aditya.com', 'https://aditya.com', '', NULL),
(8, 21, 'hrishabOrganizers', 5, 'https://www.wedmegood.com/vendors/goa/wedding-venues/all/banquet-hall/', 'https://www.instagram.com/aditya_7zz/', 'Wedding,Party,Birthday', 'we offer the best of all');

-- --------------------------------------------------------

--
-- Table structure for table `organizer_photos`
--

CREATE TABLE `organizer_photos` (
  `id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `photo_url` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizer_photos`
--

INSERT INTO `organizer_photos` (`id`, `organizer_id`, `photo_url`, `uploaded_at`) VALUES
(2, 8, 'uploads/organizer/8_1742463588_event-organizer-thrive-meetings-1440x840.jpg', '2025-03-20 09:39:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `role` enum('user','organizer','vendor','venue_owner') NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`, `location`, `role`, `profile_picture`, `created_at`) VALUES
(14, 'aditya', 'aditya.gaonkar@gmail.com', '9518554423', '$2y$10$fn4H96aHLtyPtpiflZdsEO7d.Eicv7GNm4TthNW83K5hZPWMyuSEy', 'dhargal', 'vendor', '/EventManagementSystem/uploads/profilePics/default.png', '2025-03-03 19:26:29'),
(15, 'xyz', 'xyz@gmail.com', '1234567890', '$2y$10$I5oe.olO8kmVfBNqvdnSXuDeuBnyGQkRMBASXmlRLRjFwQfqCmD9a', 'dhargal', 'venue_owner', '/EventManagementSystem/uploads/profilePics/default.png', '2025-03-07 16:04:09'),
(20, 'organizer', 'organizer@gmail.com', '6789012345', '$2y$10$TTqFxUfIMXDVVrL6SkguR.CS.GAkGyr.V3zEctoRT4Sn.nKL7qJNi', 'dhargal', 'organizer', '/EventManagementSystem/uploads/profilePics/profile_67cd6c23632ea4.73150364.JPG', '2025-03-09 10:23:31'),
(21, 'hrishab', 'hrishab@gmail.com', '9359947504', '$2y$10$D66z4oP3ME0ShtOiVs7XbOdmMy3kr3l1.hXprnr.i8UUHI0XBi2Ba', 'porvorim', 'organizer', '/EventManagementSystem/uploads/profilePics/default.png', '2025-03-14 10:44:16'),
(22, 'magnus', 'magnus.rodrigues@gmail.com', '8975525710', '$2y$10$Jp8HhfbX8KPtM3pPS9.Nuuu9K2oYgvY5OiS3/U/7QyoaazQoWRLyW', 'parsa', 'venue_owner', '/EventManagementSystem/uploads/profilePics/profile_67d56f442a3547.54190600.png', '2025-03-15 12:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `service` enum('catering','photography','decorator','anchor','dj','karaoke') NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `service_locations` text DEFAULT NULL,
  `price_range` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `user_id`, `business_name`, `description`, `service`, `website`, `instagram`, `service_locations`, `price_range`) VALUES
(6, 14, 'adityaVendors', 'this is the place where you will eat and  visit again & again', 'catering', 'https://aditya.com', 'https://instagram.com', 'dhargal', '200-5000');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_bookings`
--

CREATE TABLE `vendor_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `venue_location` varchar(255) NOT NULL,
  `venue_name` varchar(255) NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor_bookings`
--

INSERT INTO `vendor_bookings` (`id`, `user_id`, `vendor_id`, `booking_date`, `venue_location`, `venue_name`, `status`, `created_at`, `updated_at`) VALUES
(3, 14, 6, '2025-03-26', 'dhargal near kamaxi hall', 'Other', 'pending', '2025-03-23 14:40:08', '2025-03-23 19:17:24'),
(5, 14, 6, '2025-03-27', 'Karma Woodhouse Estate, Santarem Beach,Vasco da Gama Issorcim, Dabolim, Goa 403806', 'Stone Water Eco Resort.', 'pending', '2025-03-23 15:03:52', '2025-03-23 15:03:52');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_photos`
--

CREATE TABLE `vendor_photos` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `photo_url` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor_photos`
--

INSERT INTO `vendor_photos` (`id`, `vendor_id`, `photo_url`, `uploaded_at`) VALUES
(8, 6, 'uploads/vendors/6_1741957421_download (1).jpeg', '2025-03-14 13:03:41'),
(9, 6, 'uploads/vendors/6_1741957429_images.jpeg', '2025-03-14 13:03:49'),
(11, 6, 'uploads/vendors/6_1742023651_download.jpeg', '2025-03-15 07:27:31'),
(12, 6, 'uploads/vendors/6_1742023660_istockphoto-650655146-612x612.jpg', '2025-03-15 07:27:40');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_ratings`
--

CREATE TABLE `vendor_ratings` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor_ratings`
--

INSERT INTO `vendor_ratings` (`id`, `vendor_id`, `user_id`, `rating`, `created_at`) VALUES
(4, 6, 21, 5, '2025-03-15 07:37:47'),
(12, 6, 14, 3, '2025-03-23 13:18:37');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_reviews`
--

CREATE TABLE `vendor_reviews` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor_reviews`
--

INSERT INTO `vendor_reviews` (`id`, `vendor_id`, `user_id`, `review`, `created_at`) VALUES
(4, 6, 21, 'nice', '2025-03-16 10:34:09'),
(7, 6, 14, 'very nice food', '2025-03-23 12:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `manager_email` varchar(255) DEFAULT NULL,
  `manager_phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `venue_used_for` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `owner_id`, `name`, `location`, `capacity`, `price_per_day`, `description`, `thumbnail`, `manager_name`, `manager_email`, `manager_phone`, `status`, `created_at`, `venue_used_for`) VALUES
(1, 15, 'greenPark', 'mapusa', 340, 50000.00, 'the best place for wedding and celebration', '/EventManagementSystem/uploads/venue/1741507503_Thumbnail.jpg', 'aditya gaonkar', 'aditya.gaonakr1907@gamil.com', '9518554423', 'rejected', '2025-03-09 08:05:03', 'wedding,birthdays,party'),
(2, 15, 'green', 'mapusa', 340, 50000.00, 'best for birthdays and wediings', '/EventManagementSystem/uploads/venue/1741508038_valorantGameplayThumbnail.jpg', 'aditya gaonkar', 'aditya.gaonakr1907@gamil.com', '9518554423', 'rejected', '2025-03-09 08:13:58', 'wedding,birthdays,party'),
(3, 15, 'Stone Water Eco Resort.', 'Karma Woodhouse Estate, Santarem Beach,Vasco da Gama Issorcim, Dabolim, Goa 403806', 340, 50000.00, 'Set on Santrem Beach along the Arabian Sea, this down-to-earth resort on tropical gardens is 2 km from Casino Pearl and 3 km from the Naval Aviation Museum.\r\n\r\nRustic wood cabins feature exposed beams and wood-panelled walls, plus air-conditioning. They all come with flat-screen TVs, minifridges and furnished patios. Upgraded cabins add sea views. Room service is available.\r\n\r\nBreakfast and parking are complimentary. There\'s a classic international restaurant offering open-air dining, along with a laid-back bar. Additional amenities include a beach, a spa and a fitness centre. Bike rentals are available.', '/EventManagementSystem/uploads/venue/1741526874_venue1.png', 'aditya gaonkar', 'aditya.gaonkar1907@gamil.com', '9518554423', 'approved', '2025-03-09 13:27:54', 'wedding,birthdays,party'),
(4, 15, 'The Westin Goa', 'Survey No 204/1, Sub Division 1, Bardez, Dmello Vaddo, Anjuna, Goa, 403509', 340, 50000.00, 'Situated in Anjuna, 1.3 km from Ozran Beach, The Westin Goa features accommodation with free bikes, free private parking, a fitness centre and a garden. Boasting room service, this property also provides guests with a terrace. The accommodation provides a 24-hour front desk, airport transfers, a kids\' club and free WiFi throughout the property.\r\n\r\nThe hotel will provide guests with air-conditioned rooms offering a desk, a kettle, a fridge, a minibar, a safety deposit box, a flat-screen TV and a private bathroom with a shower. At The Westin Goa the rooms are fitted with bed linen and towels.\r\n\r\nA buffet, continental or Full English/Irish breakfast is available each morning at the property. At the accommodation you will find a restaurant serving Indian, local and international cuisine. Vegetarian, dairy-free and halal options can also be requested.\r\n\r\nYou can play billiards at this 5-star hotel, and the area is popular for fishing and cycling.\r\n\r\nAnjuna Beach is 1.5 km from The Westin Goa, while Vagator Beach is 2.1 km from the property. The nearest airport is Manohar International Airport, 29.8 km from the hotel.', '/EventManagementSystem/uploads/venue/1741527053_venue2.png', 'aditya gaonkar', 'aditya.gaonakr1907@gamil.com', '9518554423', 'approved', '2025-03-09 13:30:53', 'wedding,birthdays,party'),
(6, 15, 'Confetti Hall', 'mapusa', 340, 50000.00, 'Confetti Hall, Goa is a gorgeous venue located in the scintillating beach city. Capable of accommodating a few thousand guests in its huge lawn, Confetti Hall is perfect if you are planning to host a lavish wedding and reception. The venue also has a hall that is fit to host a large gathering of a few hundred guests. One can plan to host various kinds of functions at this venue, such as pre-wedding functions, weddings, receptions, and many more such events. Overnight functions are not an issue at this venue, one can celebrate host night meanwhile staff make sure all the arrangements are done on point.\r\nYou must definitely book this venue if you want to experience top-notch services. Confetti Hall, Goa has a cozy ambiance which makes everyone feel welcomed. The experienced and well-trained staff makes sure that you don’t have to worry about even the minutest of detail at the time of your event. Confetti Hall, Velim, renders in-house catering and decor. You can even outsource these services from outside the venue according to your preference. The arrangements here are so well taken care of that the host need not bother about a thing. The venue offers a complimentary changing room to the host for their special day. \r\nConfetti Hall Velim Goa is located close to Chapel of the Sacred Heart of Jesus, making it well-connected and easily accessible. If your outstation guests are arriving at the venue via plane, Confetti Banquet Hall, Goa is 38.5 km far from the Goa International Airport, which takes around 55 minutes to drive down to the destination, via NH 66 and NH566. The guests who are arriving at this place via train, Vasco-Da-Gama Railway Station which is around 42.7 km far from the venue, which is around an hour and five minutes\' drive via NH 66 and NH566. The venue has an inviting ambiance which makes your guests feel welcomed.  \r\nSo what are you waiting for? Go ahead and book Confetti Hall, Goa today.', '../../uploads/venue/hall.jpeg', 'aditya gaonkar', 'aditya.gaonakr1907@gamil.com', '9518554423', 'approved', '2025-03-14 08:31:07', 'wedding,birthdays,party'),
(11, 15, 'Aanantya AC Banquet Hall', 'behind JMB Capitol, Ximer, Khorlim, Mapusa, Goa 403507', 1000, 50000.00, 'Aanantya Banquet Hall an elegant fully air-conditioned Grand Hall is spacious spread over 1200 sq. mt with 12 ft high ceiling in the heart of Mapusa city, having a capacity of over a 1000 people. The Hall is “Vastu” compliant and fitted with chandeliers and smart lighting. It also has ample parking space to match for up to 200 cars. The hall has a separate independent area for food and beverage counters having an area of 450 sq. mt.\r\n\r\nAanantya Banquet Hall sets the highest standards in Goa that every major celebration demands and expects to be par excellence.\r\n\r\nAanantya Banquet Hall is the perfect setting for handling all your memorable celebratory events as wedding ceremonies, engagements, birthdays, including a perfect venue for exhibitions, corporate conferences, product launches and fashion shows.\r\n\r\nCome create new treasures in your memory banks at Aanantya located conveniently behind JMB Capitol Building, Khorlim-bypass road, Mapusa Bardez Goa-403507.', '../../uploads/venue/hall2.jpg', 'aditya gaonkar', 'aditya.gaonakr1907@gamil.com', '9518554423', 'approved', '2025-03-14 08:43:00', 'wedding,birthdays,party'),
(12, 22, 'Shri Kamakshi Hall', 'Near Don Khamb, Dhargal, Goa 403512', 500, 50000.00, 'best place to celebrate you best days', '../../uploads/venue/unnamed.jpg', 'amul shirodkar', 'amul@gmail.com', '9518554423', 'approved', '2025-03-15 12:21:19', 'Wedding, Conference, Party, Birthday');

-- --------------------------------------------------------

--
-- Table structure for table `venue_bookings`
--

CREATE TABLE `venue_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `event_purpose` varchar(255) NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue_bookings`
--

INSERT INTO `venue_bookings` (`id`, `user_id`, `venue_id`, `event_date`, `event_purpose`, `status`, `created_at`, `updated_at`) VALUES
(9, 14, 3, '2025-03-29', 'wedding', 'confirmed', '2025-03-23 14:51:49', '2025-03-23 14:52:58');

-- --------------------------------------------------------

--
-- Table structure for table `venue_images`
--

CREATE TABLE `venue_images` (
  `id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue_images`
--

INSERT INTO `venue_images` (`id`, `venue_id`, `image_url`) VALUES
(11, 3, 'uploads/venue/_1742063068_40.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `userName` (`userName`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `organizers`
--
ALTER TABLE `organizers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `organizer_photos`
--
ALTER TABLE `organizer_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `vendor_bookings`
--
ALTER TABLE `vendor_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `vendor_photos`
--
ALTER TABLE `vendor_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `vendor_ratings`
--
ALTER TABLE `vendor_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `vendor_reviews`
--
ALTER TABLE `vendor_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `venue_bookings`
--
ALTER TABLE `venue_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_date` (`event_date`),
  ADD UNIQUE KEY `venue_id` (`venue_id`,`event_date`),
  ADD KEY `fk_venue_bookings_user` (`user_id`);

--
-- Indexes for table `venue_images`
--
ALTER TABLE `venue_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `organizers`
--
ALTER TABLE `organizers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `organizer_photos`
--
ALTER TABLE `organizer_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vendor_bookings`
--
ALTER TABLE `vendor_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vendor_photos`
--
ALTER TABLE `vendor_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `vendor_ratings`
--
ALTER TABLE `vendor_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `vendor_reviews`
--
ALTER TABLE `vendor_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `venue_bookings`
--
ALTER TABLE `venue_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `venue_images`
--
ALTER TABLE `venue_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `organizers`
--
ALTER TABLE `organizers`
  ADD CONSTRAINT `organizers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `organizer_photos`
--
ALTER TABLE `organizer_photos`
  ADD CONSTRAINT `organizer_photos_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendors`
--
ALTER TABLE `vendors`
  ADD CONSTRAINT `vendors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_bookings`
--
ALTER TABLE `vendor_bookings`
  ADD CONSTRAINT `vendor_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_bookings_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_photos`
--
ALTER TABLE `vendor_photos`
  ADD CONSTRAINT `vendor_photos_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_ratings`
--
ALTER TABLE `vendor_ratings`
  ADD CONSTRAINT `vendor_ratings_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_reviews`
--
ALTER TABLE `vendor_reviews`
  ADD CONSTRAINT `vendor_reviews_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `venues`
--
ALTER TABLE `venues`
  ADD CONSTRAINT `venues_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `venue_bookings`
--
ALTER TABLE `venue_bookings`
  ADD CONSTRAINT `fk_venue_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_venue_bookings_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `venue_bookings_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `venue_images`
--
ALTER TABLE `venue_images`
  ADD CONSTRAINT `venue_images_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
