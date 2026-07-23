-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jul 24, 2026 at 12:39 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inafrica`
--

-- --------------------------------------------------------

--
-- Table structure for table `gifadvert`
--

CREATE TABLE `gifadvert` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` longblob DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Is_Active` int(11) NOT NULL DEFAULT 1,
  `PostingDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gifadvert`
--

INSERT INTO `gifadvert` (`id`, `title`, `file`, `url`, `description`, `Is_Active`, `PostingDate`, `UpdationDate`) VALUES
(3, 'Test Landscape Banner', 0x89504e470d0a1a0a0000000d49484452000002d8000000c808020000005df801e0000000097048597300000ec400000ec401952b0e1b0000062649444154789ceddd5b72da48004051676af613efc35eaebd8f2c693ea85269f44208e18be373be5202352dfca18b5a845fbf3fde5e00000affd41300007e2e210200648408009011220040468800001921020064840800901122004046880000192102006484080090112200404688000019210200648408009011220040468800001921020064840800901122004046880000192102006484080090112200404688000019210200648408009011220040468800001921020064840800901122004046880000192102006484080090112200404688000019210200648408009011220040468800001921020064840800901122004046880000192102006484080090112200404688000019210200648408009011220040468800001921020064840800901122004046880000192102006484080090112200404688000019210200648408009011220040468800001921020064840800901122004046880000192102006484080090112200404688000019210200648408009011220040468800001921020064840800901122004046880000997feb09c00ff5e7ed63b2e5f5f37dd87ef9f7f899db5b16875d7cc2c664f63fff1e7b5eeb2be703b45c1181c6ebe7fb70a21dfffb629e29f387e6cf19cedfe3a6792a1b93ffe2693ce19b033f93108127b578a67c506acc4be8119ebf9380af676906be8dc982c5ebe7fbe593fd62430c1b87bdd6566d86edf37126adb0b8d7dabeb74e7e711a5797a5160f6ae390c75b2c00c1337045049ed189d70c26b79ecc475e3b138f7799ecb5f1d031e301f70fb576501b87bcbd22067c3d21027f83f14d0f6b3740dc74029e5f2d78dc297c7eb16432e77900adedb5769d4673c0d3b234034f6a58bc986cdf888c2f5b6b387cfde3f4fb42dc6802df9d1081e7b5b84eb171a3c6d758bb6f638f037b4d826c9259ae73c07767690658b0786be7c617794e7cadfd81b27386db7c95175aae884063e7d7376eba79f3dc69ccd7862e0fcd2f519cd222c398f301872df3cb218b33bce9e5f6ef053cc2afdf1f6ff51c00801fcad20c00901122004046880000192102006484080090112200404688000019210200648408009011220040c66fcdc0751bbf05f38d5ee22c6bbf2cf3c593dffe5d62e0bb1022c06d86f3fdb9f1746cb4f164febc7d6811f8762ccd007f834b823cf4978a81477045044e30ffd9fa97d147fcb59f9bbfba7d32e0c66b9d328df943c7ac0db8765ce3ed87afb2cc77bcf5dd38f646cdf73a367ff8b17efdfe78abe700cf6efbec387974ed7cb6f6b497ff17c9da0975e720f74f63ff4974f1f96b036e1fd7e1571fdb33f8c6bb71f88ddade11d8666906eef5faf9be71d6191e1a9fa526e7aaed93d630fefc0c377ee9b3a671cfeac64d036e4f78a7cb20f3173af06e6c3cb4e7b876fe2180094b3370af73ef4bb87c6adf587f397d1aa7df57b138e0e1e3da6918ff71378bb801051e4188c05daede9a70c07cb1e3eaf741ee99c6e91fdfd7063c705cc73ce28ff2f280370a78b13403e7da79c29b7c6a9fdcadb9fd1f75ec79f27ce3e4b52e43ed1f70a78d016f1af9c03436eecc382b0d4f7ca380819b55e1bab5b586f9a3e3bb4d77de9e397ff2b16fcd5c9dc6ce2fe9dc79b3eace195e9dc9d569ec7f9776fe51f6ffbd168fcbf512384688c05fce69127866966600808c2b220040c615110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c808110020234400808c10010032420400c8fc07b7e5161c3d2c0b4b0000000049454e44ae426082, 'https://inafricaa.net', 'Seeded test advert to verify landscape placement.', 1, '2026-07-18 14:17:51', '2026-07-18 14:17:51'),
(4, 'Test Portrait Poster', 0x89504e470d0a1a0a0000000d494844520000012c00000258080200000057444c68000000097048597300000ec400000ec401952b0e1b000008fa49444154789ceddc5b6ee2480040d1eed1ac23595067b9b3a1dec97c4442c88fc21827178773be22c0a6ecf8e247827fbfbdfdf90574fea90700af4e84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c444083111424c84101321c44408311142ecdf7a0099bf7fff9b3cf2fefe7179fcf3e7eb578e1f599cede20b0683d9fefa476c79afef1c0fafbb277c7fffb86c64d73f7f9a273a7f6afe9acbb67bddf353190cfe9b87f1842ba7f2ba11deb4b8957c5166f34f81aff0fc9f11afe9750f4777981ca4bdbf7f7c7ea22ff67379f032d5da91eae5f1f97c269d2c4eb536edbd835f1cc6cd43f1c5851a2cf2f5230e7a3fd9132e3b705f3139d59ccf796d2bbc9e6432d5e0a97dae67b87d566b0b3558e4f159c06b12e131ae4f72d64e78eedaf8e67b89afdb7ce73bc9c998e7f1af4db5b67fd6db80c3d1559703b6c9e383c0beedf86af77eeff0f34027968f13e1c8e2b1d9e0c4ec7bac9da76db163aac987d1e423c6feed710e479fd4e2658cc105db03df6b7b9c1b4738e6cf15afbb27dc7899eeae0b15c70e637e3cfcf9d47cd77448879779ce67787964be1b5c1ce15d6fb77daa9feaf7dbdb9f7a0cf0d21c8e424c84101321c444083111424c84101321c444083111424c84103be5ff8e8ebfadf7548efa72d3da7f877ef3529f68cd9fc82923fc34f956ceda6d26b67bb65b9e5d9bdf392219c66430bf4eb8e69fd089233c8597ddb0d8ee674678e0dd87b6dfa669f0d4daeb0fdc036c1cdeaf0d8bfc54c3b8f7577946a7fc2ad3f8cc64701f94b53b11cd275c7cbbf95437e7309ee78eed66b0a5de1cdef6451ebffbc4cd35bf7b18bb7f95e772e23de1e21a5ffb5eecfce6456b73b8f9768bb30a6d59e48b4306bc7bcddf358caffb553e9b1347f80c9ee7be0c6bfba8f9cd23be7f7b7d6418cfb386bf8e08f77be4864b875bdba6e707ae8f5fccdc61f7304ebd8bdbe8a7fdb17ef19e45fb4ebdee2d6a77810fdee96870c3a5bbe67cec3026a7733b8671d48da49edf892fcc0cd21a5fa3bb77c2c15493d7af6d3df3910c063976f3ead1e2e08f1ac6836b7ec730c6cbf533f693a78c107e929f76380aa723428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288891062228498082126428889106222849808212642888910622284980821264288fd0f5ceec897b9b513210000000049454e44ae426082, 'https://inafricaa.net', 'Seeded test advert to verify portrait placement.', 1, '2026-07-18 14:17:51', '2026-07-21 18:30:07');

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `id` int(11) NOT NULL,
  `AdminUserName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AdminPassword` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AdminEmailId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userType` int(11) DEFAULT NULL,
  `CreationDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `profileImage` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`id`, `AdminUserName`, `AdminPassword`, `AdminEmailId`, `userType`, `CreationDate`, `UpdationDate`, `profileImage`) VALUES
(1, 'Felix Habibi', '$2y$10$2KX.DP1HEdvjy1UpdbF3Yea8zr4ejFplgbJKOCaj3elUJUEfZUT/.', 'felixhabineza994@gmail.com', 1, '2026-07-16 13:59:17', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblcategory`
--

CREATE TABLE `tblcategory` (
  `id` int(11) NOT NULL,
  `CategoryName` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Description` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PostingDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `Is_Active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblcomments`
--

CREATE TABLE `tblcomments` (
  `id` int(11) NOT NULL,
  `PostUrl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postingDate` timestamp NULL DEFAULT current_timestamp(),
  `status` int(11) DEFAULT NULL,
  `ParentId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblcontactmessages`
--

CREATE TABLE `tblcontactmessages` (
  `id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Message` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `MailSent` tinyint(1) NOT NULL DEFAULT 0,
  `IsRead` tinyint(1) NOT NULL DEFAULT 0,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblcountries`
--

CREATE TABLE `tblcountries` (
  `id` int(11) NOT NULL,
  `RegionId` int(11) NOT NULL,
  `CountryName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CountryCode` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Hook` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SizeArea` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Latitude` decimal(9,6) DEFAULT NULL,
  `Longitude` decimal(9,6) DEFAULT NULL,
  `LocationDesc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Languages` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AdministrativeType` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Culture` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EconomicBasis` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Is_Active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblcountries`
--

INSERT INTO `tblcountries` (`id`, `RegionId`, `CountryName`, `CountryCode`, `Hook`, `SizeArea`, `Latitude`, `Longitude`, `LocationDesc`, `Languages`, `AdministrativeType`, `Culture`, `EconomicBasis`, `Is_Active`) VALUES
(1, 1, 'Burundi', 'bi', 'Burundi is a small, densely populated East African nation on Lake Tanganyika, one of the world\'s poorest but culturally rich countries.', '27,834 km²', '-3.373100', '29.918900', 'East Africa, landlocked, on the northeastern shore of Lake Tanganyika, bordering Rwanda, Tanzania and the DRC.', 'Kirundi, French and English (official)', 'Unitary Presidential Republic', 'Renowned for the Royal Drummers of Burundi, a UNESCO-recognized drumming tradition, and a farming culture centred on the hilly interior.', 'Subsistence agriculture (coffee and tea are the main cash-crop exports) dominates; among the least industrialized economies in the region.', 1),
(2, 1, 'Kenya', 'ke', 'Kenya is East Africa\'s economic and technology hub, famed for its wildlife safaris and long-distance running champions.', '580,367 km²', '-0.023600', '37.906200', 'East Africa, on the Indian Ocean, bordering Ethiopia, Somalia, Tanzania, Uganda and South Sudan.', 'Swahili and English (official)', 'Presidential Republic', 'Over 40 ethnic communities including Kikuyu, Luo and Maasai contribute to a culture famous for its runners, Maasai beadwork, and Nairobi\'s status as a regional creative and tech hub (\'Silicon Savannah\').', 'Diversified regional leader: agriculture (tea, coffee, horticulture), tourism, financial services and a fast-growing tech/mobile-money sector (M-Pesa).', 1),
(3, 1, 'Rwanda', 'rw', 'Rwanda is a hilly, densely populated East African nation known as the \'Land of a Thousand Hills\' and for its remarkable post-1994 recovery.', '26,338 km²', '-1.940300', '29.873900', 'East Africa, landlocked in the Great Lakes region, bordering Uganda, Tanzania, Burundi and the DRC.', 'Kinyarwanda, English and French (official)', 'Unitary Presidential Republic', 'Known for Intore warrior dance, traditional woven imigongo art, and Umuganda, a nationwide monthly community service tradition.', 'Coffee and tea exports, mining (coltan, tin), and a fast-growing services, tourism (gorilla trekking) and tech sector.', 1),
(4, 1, 'Tanzania', 'tz', 'Tanzania is an East African nation home to Mount Kilimanjaro, the Serengeti, and the Zanzibar archipelago.', '947,303 km²', '-6.369000', '34.888800', 'East Africa, on the Indian Ocean including Zanzibar, bordering Kenya, Uganda, Rwanda, Burundi, DRC, Zambia, Malawi and Mozambique.', 'Swahili and English (official)', 'Presidential Republic (union of Tanganyika and Zanzibar)', 'Home to Mount Kilimanjaro, the Serengeti\'s Great Migration, and Zanzibar\'s Swahili-Arab Stone Town heritage, with Swahili serving as a unifying national language.', 'Agriculture, tourism (safari and Zanzibar beaches), and mining (gold, gemstones like tanzanite) drive the economy.', 1),
(5, 1, 'Uganda', 'ug', 'Uganda is a landlocked East African nation on Lake Victoria, home to mountain gorillas and often called the \'Pearl of Africa\'.', '241,038 km²', '1.373300', '32.290300', 'East Africa, landlocked, on the shores of Lake Victoria, bordering Kenya, South Sudan, DRC, Rwanda and Tanzania.', 'English and Swahili (official)', 'Presidential Republic', 'Home to the Buganda kingdom\'s traditional monarchy, more than half the world\'s remaining mountain gorillas, and a diverse mix of Bantu and Nilotic ethnic traditions.', 'Agriculture (coffee is the top export) underpins the economy, alongside growing tourism and recently discovered oil reserves.', 1),
(8, 2, 'Angola', 'ao', 'Angola is a resource-rich Southern African nation rebuilding since its long civil war, anchored by vast oil and diamond reserves.', '1,246,700 km²', '-11.202700', '17.873900', 'Southern Africa, on the Atlantic coast, bordering Namibia, Zambia and the Democratic Republic of the Congo.', 'Portuguese (official), Umbundu, Kimbundu, Kikongo', 'Unitary Presidential Republic', 'Angolan culture fuses Bantu traditions with Portuguese colonial heritage, expressed through semba and kizomba music, vibrant textile art, and a growing film and literature scene.', 'Heavily dependent on offshore crude oil production, with diamonds, agriculture and an expanding services sector making up the rest.', 1),
(9, 2, 'Botswana', 'bw', 'Botswana is a landlocked, diamond-powered Southern African democracy known for stable governance and the Okavango Delta.', '581,730 km²', '-22.328500', '24.684900', 'Southern Africa, landlocked, bordering South Africa, Namibia, Zimbabwe and Zambia.', 'English (official), Setswana', 'Unitary Parliamentary Republic', 'Tswana traditions of consensus-based kgotla (community assembly) governance remain central to public life, alongside a strong San (Bushmen) heritage in the Kalahari and a thriving crafts and safari-tourism culture.', 'One of the world\'s largest diamond producers; mining is complemented by cattle ranching, tourism (Okavango Delta, Chobe) and a growing financial sector.', 1),
(10, 2, 'Comoros', 'km', 'Comoros is a small volcanic island nation in the Indian Ocean, known as the \'Perfume Islands\' for its ylang-ylang production.', '1,862 km²', '-11.645500', '43.333300', 'Indian Ocean, an archipelago between Madagascar and the East African coast (Mozambique Channel).', 'Comorian (Shikomor), Arabic and French (official)', 'Federal Presidential Republic', 'A blend of Swahili, Arab, and French influences reflected in Islamic traditions, the grande mariage wedding ceremony, and taarab-influenced music.', 'One of the world\'s largest producers of ylang-ylang and vanilla; a small, agriculture- and remittance-dependent economy.', 1),
(11, 2, 'Eswatini', 'sz', 'Eswatini (formerly Swaziland) is one of the world\'s last absolute monarchies, a small landlocked kingdom in Southern Africa.', '17,364 km²', '-26.522500', '31.465900', 'Southern Africa, landlocked between South Africa and Mozambique.', 'siSwati and English (official)', 'Absolute Monarchy', 'The annual Umhlanga (Reed Dance) and Incwala ceremonies remain central to national identity, reflecting deep-rooted Swazi royal traditions.', 'Sugar and soft-drink concentrate manufacturing, textiles, and agriculture, with close economic ties to South Africa.', 1),
(12, 2, 'Lesotho', 'ls', 'Lesotho is a mountainous kingdom entirely surrounded by South Africa, nicknamed the \'Kingdom in the Sky\'.', '30,355 km²', '-29.610000', '28.233600', 'Southern Africa, a landlocked enclave entirely within South Africa, high in the Maloti/Drakensberg mountains.', 'Sesotho and English (official)', 'Constitutional Monarchy', 'The Basotho people\'s traditional blanket and conical mokorotlo hat are national symbols, alongside strong horseback-riding traditions suited to its mountainous terrain.', 'Textile and garment manufacturing (a major AGOA exporter to the US), water exports to South Africa, and diamond mining.', 1),
(13, 2, 'Madagascar', 'mg', 'Madagascar is the world\'s fourth-largest island, an Indian Ocean nation famed for wildlife found nowhere else on Earth.', '587,041 km²', '-18.766900', '46.869100', 'Indian Ocean, an island roughly 400 km off the southeastern coast of Africa.', 'Malagasy and French (official)', 'Unitary Semi-Presidential Republic', 'A distinctive Austronesian-African fusion culture, with famadihana (turning of the bones) ancestral traditions and over 90% of its wildlife found nowhere else on Earth.', 'Vanilla (world\'s top producer), textiles, mining (nickel, cobalt), and ecotourism drive the export economy.', 1),
(14, 2, 'Malawi', 'mw', 'Malawi is a densely populated Southern African nation on the shores of Lake Malawi, known as \'the Warm Heart of Africa\'.', '118,484 km²', '-13.254300', '34.301500', 'Southern Africa, landlocked along the Great Rift Valley, bordering Tanzania, Mozambique and Zambia, with Lake Malawi covering much of its eastern border.', 'English and Chichewa (official)', 'Unitary Presidential Republic', 'Known for the warmth of its people, vibrant Gule Wamkulu masked dance (a UNESCO-listed Chewa tradition), and Lake Malawi\'s exceptional freshwater fish diversity.', 'Predominantly agricultural, with tobacco as the leading export, alongside tea, sugar and growing tourism around Lake Malawi.', 1),
(15, 2, 'Mauritius', 'mu', 'Mauritius is an Indian Ocean island nation known for political stability, tourism, and a diversified upper-middle-income economy.', '2,040 km²', '-20.348400', '57.552200', 'Indian Ocean, an island about 2,000 km off the southeast African coast, east of Madagascar.', 'English (official), French and Mauritian Creole widely spoken', 'Unitary Parliamentary Republic', 'A multicultural mix of Indian, Creole, Chinese and French heritage, reflected in its cuisine, festivals (Diwali, Chinese New Year, Christmas) and Sega music and dance.', 'A diversified economy built on tourism, textiles/garments, financial services and offshore banking, plus sugar.', 1),
(16, 2, 'Mozambique', 'mz', 'Mozambique is a long, Indian Ocean-facing Southern African nation with major offshore natural gas discoveries.', '801,590 km²', '-18.665700', '35.529600', 'Southern Africa, a long Indian Ocean coastline, bordering Tanzania, Malawi, Zambia, Zimbabwe, South Africa and Eswatini.', 'Portuguese (official), Makhuwa and other Bantu languages', 'Unitary Presidential Republic', 'A Bantu-Portuguese fusion culture reflected in marrabenta music, wood carving (Makonde art) and a cuisine centred on seafood and peri-peri spice.', 'Aluminium smelting and coal have been core exports; major offshore natural gas projects are set to transform the economy alongside agriculture.', 1),
(17, 2, 'Namibia', 'na', 'Namibia is a vast, sparsely populated Southern African nation home to the Namib and Kalahari deserts.', '825,615 km²', '-22.957600', '18.490400', 'Southern Africa, on the Atlantic coast, bordering Angola, Zambia, Botswana and South Africa.', 'English (official), Afrikaans, Oshiwambo and other local languages', 'Unitary Presidential Republic', 'Home to the Himba, Herero and San peoples with distinct traditions amid dramatic desert landscapes, and a strong community-based wildlife conservation movement.', 'Mining (uranium, diamonds) is the leading export sector, alongside fishing, tourism and livestock farming.', 1),
(18, 2, 'Seychelles', 'sc', 'Seychelles is an Indian Ocean archipelago and Africa\'s smallest nation by population, a high-end tourism and fisheries economy.', '459 km²', '-4.679600', '55.492000', 'Indian Ocean, an archipelago of 115 islands northeast of Madagascar.', 'Seychellois Creole, English and French (official)', 'Unitary Presidential Republic', 'A Creole culture blending African, French and Indian heritage, celebrated in Kreol music and the annual Festival Kreol.', 'Tourism and tuna fishing/canning are the twin pillars of one of Africa\'s highest per-capita income economies.', 1),
(19, 2, 'South Africa', 'za', 'South Africa is the continent\'s most industrialized economy, a diverse \'Rainbow Nation\' with three capital cities.', '1,221,037 km²', '-30.559500', '22.937500', 'Southern Africa, at the continent\'s southern tip, bordering Namibia, Botswana, Zimbabwe, Mozambique, Eswatini, and surrounding Lesotho.', '12 official languages including Zulu, Xhosa, Afrikaans and English', 'Parliamentary Republic (three capitals: Pretoria, Cape Town, Bloemfontein)', 'Known as the \'Rainbow Nation\' for its ethnic and linguistic diversity (12 official languages), with a globally influential music, sport and post-apartheid democratic heritage.', 'Africa\'s most industrialized economy: mining (gold, platinum), manufacturing, finance and a well-developed services sector.', 1),
(20, 2, 'Zambia', 'zm', 'Zambia is a landlocked Southern African nation defined by the Zambezi River, Victoria Falls, and copper mining.', '752,618 km²', '-13.133900', '27.849300', 'Southern Africa, landlocked along the Zambezi River, bordering the DRC, Tanzania, Malawi, Mozambique, Zimbabwe, Botswana, Namibia and Angola.', 'English (official), Bemba, Nyanja and other local languages', 'Unitary Presidential Republic', 'Home to the Zambezi River and Victoria Falls (shared with Zimbabwe), with over 70 ethnic groups and the annual Kuomboka royal barge ceremony among its notable traditions.', 'Copper mining has long been the backbone of the economy, alongside agriculture and growing tourism around Victoria Falls.', 1),
(21, 2, 'Zimbabwe', 'zw', 'Zimbabwe is a landlocked Southern African nation home to Victoria Falls and the ancient Great Zimbabwe ruins.', '390,757 km²', '-19.015400', '29.154900', 'Southern Africa, landlocked, bordering South Africa, Botswana, Zambia and Mozambique.', 'English, Shona and Ndebele among 16 official languages', 'Presidential Republic', 'Named after the Great Zimbabwe stone ruins, a medieval Shona city, with Shona sculpture recognized as a globally significant modern art movement.', 'Mining (gold, platinum, chrome) and agriculture (tobacco) are key exports, alongside tourism around Victoria Falls.', 1),
(23, 3, 'Benin', 'bj', 'Benin is a narrow West African nation known as the birthplace of Vodun and a stable multi-party democracy.', '112,622 km²', '9.307700', '2.315800', 'West Africa, bordering Nigeria, Togo, Burkina Faso and Niger, with a short Atlantic coastline.', 'French (official), Fon, Yoruba', 'Unitary Presidential Republic', 'Widely regarded as the birthplace of Vodun (Voodoo), Benin has a rich tradition of oral history, royal art from the former Kingdom of Dahomey, and vibrant annual festivals.', 'Agriculture (cotton is the main export), regional trade via the Port of Cotonou, and a growing services sector.', 1),
(24, 3, 'Burkina Faso', 'bf', 'Burkina Faso is a landlocked West African nation of the Sahel, known for its film industry (FESPACO) and gold mining economy.', '272,967 km²', '12.238300', '-1.561600', 'West Africa, landlocked in the Sahel, bordering Mali, Niger, Benin, Togo, Ghana and Cote d\'Ivoire.', 'French (official), Moore, Dioula', 'Unitary Presidential Republic (transitional military-led government)', 'Home to FESPACO, Africa\'s largest film festival, and the Mossi kingdom\'s centuries-old traditions of masked dance, bronze casting and oral griot storytelling.', 'Gold mining is the leading export earner, alongside cotton farming and livestock; the economy remains largely agrarian and vulnerable to Sahel security instability.', 1),
(25, 3, 'Cabo Verde', 'cv', 'Cabo Verde is an Atlantic archipelago nation off West Africa, praised for stable democracy and a music-driven Creole culture.', '4,033 km²', '16.538800', '-23.041800', 'West Africa, an island archipelago in the Atlantic Ocean, about 600 km off the coast of Senegal.', 'Portuguese (official), Cabo Verdean Creole (Kriolu)', 'Unitary Semi-Presidential Republic', 'A Creole blend of African and Portuguese heritage, famous worldwide for morna music (Cesaria Evora) and a strong seafaring, diaspora-connected identity.', 'Service-based, driven by tourism, shipping services and remittances from a large emigrant diaspora; limited natural resources and arable land.', 1),
(26, 3, 'Cote d\'Ivoire', 'ci', 'Cote d\'Ivoire is West Africa\'s largest economy and the world\'s top cocoa producer, anchored by the port city of Abidjan.', '322,463 km²', '7.540000', '-5.547100', 'West Africa, on the Gulf of Guinea, bordering Liberia, Guinea, Mali, Burkina Faso and Ghana.', 'French (official), Dioula and other local languages', 'Unitary Presidential Republic', 'Over 60 ethnic groups contribute to a vibrant arts scene, including Dan masks, zouglou and coupe-decale music, and Abidjan\'s reputation as a regional cultural and nightlife hub.', 'World\'s leading cocoa producer and a major cashew and coffee exporter; regional trade and manufacturing hub for francophone West Africa.', 1),
(27, 3, 'Gambia', 'gm', 'The Gambia is Africa\'s smallest mainland country, a narrow strip of land following the Gambia River into West Africa.', '11,295 km²', '13.443200', '-15.310100', 'West Africa, a narrow strip surrounding the Gambia River, almost entirely surrounded by Senegal.', 'English (official), Mandinka, Wolof, Fula', 'Unitary Presidential Republic', 'A multi-ethnic riverine culture with strong griot storytelling and kora music traditions, and popular beach tourism along the Atlantic coast.', 'Groundnut (peanut) farming, tourism along the Atlantic coast, and remittances are the main economic pillars.', 1),
(28, 3, 'Ghana', 'gh', 'Ghana is a stable West African democracy and one of the continent\'s leading gold and cocoa producers.', '238,533 km²', '7.946500', '-1.023200', 'West Africa, on the Gulf of Guinea, bordering Cote d\'Ivoire, Burkina Faso and Togo.', 'English (official), Twi, Ewe, Ga and other local languages', 'Unitary Presidential Republic', 'Home of Kente cloth, Akan chieftaincy traditions, and a globally influential highlife and Afrobeats music scene; a major hub for the African diaspora\'s \'Year of Return\' heritage tourism.', 'Gold and cocoa exports lead, alongside growing oil production and a diversifying services sector.', 1),
(29, 3, 'Guinea', 'gn', 'Guinea is a West African nation holding some of the world\'s largest bauxite reserves, at the source of the Niger River.', '245,857 km²', '9.945600', '-9.696600', 'West Africa, on the Atlantic coast, bordering Guinea-Bissau, Senegal, Mali, Cote d\'Ivoire, Liberia and Sierra Leone.', 'French (official), Fula, Susu, Maninka', 'Unitary Presidential Republic (transitional government)', 'Renowned for its national ballet traditions and djembe drumming, rooted in Mande and Fula cultural heritage.', 'Holds the world\'s largest bauxite reserves and significant iron ore deposits; mining dominates exports alongside subsistence agriculture.', 1),
(30, 3, 'Guinea-Bissau', 'gw', 'Guinea-Bissau is a small, low-lying West African nation known for cashew farming and the Bijagos Archipelago.', '36,125 km²', '11.803700', '-15.180400', 'West Africa, on the Atlantic coast, bordering Senegal and Guinea, including the Bijagos Islands.', 'Portuguese (official), Guinea-Bissau Creole widely spoken', 'Unitary Semi-Presidential Republic', 'A Creole culture blending Balanta, Fula and Mandinka traditions with Portuguese heritage, and the UNESCO-listed Bijagos Archipelago biosphere.', 'One of the world\'s most cashew-dependent economies, with limited diversification beyond subsistence farming and fishing.', 1),
(31, 3, 'Liberia', 'lr', 'Liberia is a West African nation founded in 1847 by freed African-American and Caribbean settlers, Africa\'s oldest republic.', '111,369 km²', '6.428100', '-9.429500', 'West Africa, on the Atlantic coast, bordering Sierra Leone, Guinea and Cote d\'Ivoire.', 'English (official), and numerous indigenous languages', 'Unitary Presidential Republic', 'A unique blend of Americo-Liberian settler heritage and indigenous traditions (Kpelle, Bassa, Gio), reflected in Monrovia\'s architecture and a strong storytelling and mask-dance culture.', 'Rubber and iron ore exports, a large maritime open-registry (flag-of-convenience) shipping industry, and subsistence agriculture.', 1),
(32, 3, 'Mali', 'ml', 'Mali is a landlocked West African nation home to the ancient city of Timbuktu and the historic Mali Empire.', '1,240,192 km²', '17.570700', '-3.996200', 'West Africa, landlocked in the Sahara/Sahel, bordering Algeria, Niger, Burkina Faso, Cote d\'Ivoire, Guinea, Senegal and Mauritania.', 'French and Bambara widely used (multiple national languages)', 'Unitary Presidential Republic (transitional military-led government)', 'Heir to the medieval Mali Empire of Mansa Musa, home to Timbuktu\'s historic manuscripts and mosques, and a globally celebrated tradition of griot and desert blues music.', 'Gold mining is the top export, with cotton farming and livestock also central to an economy still recovering from Sahel instability.', 1),
(33, 3, 'Niger', 'ne', 'Niger is one of the world\'s largest landlocked Sahelian nations, a major uranium producer facing significant climate and security pressures.', '1,267,000 km²', '17.607800', '8.081700', 'West Africa, mostly Sahara/Sahel desert, bordering Algeria, Libya, Chad, Nigeria, Benin, Burkina Faso and Mali.', 'French (official), Hausa, Zarma widely spoken', 'Unitary Presidential Republic (transitional military-led government)', 'Home to Hausa, Zarma-Songhai, Tuareg and Fula pastoralist traditions, with the annual Cure Salee Tuareg/Wodaabe gathering among its notable cultural events.', 'Uranium mining is a top export; the majority of the population relies on subsistence farming and livestock herding.', 1),
(34, 3, 'Nigeria', 'ng', 'Nigeria is Africa\'s most populous country and largest economy, a West African giant known for oil wealth and a booming creative industry.', '923,768 km²', '9.082000', '8.675300', 'West Africa, on the Gulf of Guinea, bordering Benin, Niger, Chad and Cameroon.', 'English (official), Hausa, Yoruba, Igbo widely spoken', 'Federal Presidential Republic', 'Africa\'s most populous nation, home to Nollywood (the world\'s second-largest film industry by output) and Afrobeats music, with over 250 ethnic groups including Hausa, Yoruba and Igbo.', 'Oil and gas exports have long dominated; a large informal sector and fast-growing fintech and entertainment industries are diversifying the economy.', 1),
(35, 3, 'Senegal', 'sn', 'Senegal is a stable West African democracy known for its music scene, Sufi Islamic traditions, and the historic port of Dakar.', '196,722 km²', '14.497400', '-14.452400', 'West Africa, on the Atlantic coast, surrounding the Gambia, bordering Mauritania, Mali and Guinea-Bissau.', 'French (official), Wolof widely spoken', 'Unitary Presidential Republic', 'Teranga (hospitality) is a core value; the country is renowned for mbalax music (Youssou N\'Dour), Sufi Islamic brotherhoods, and the historic Goree Island slave-trade memorial.', 'Fishing, groundnuts, phosphate mining, and a growing services and tourism sector, with new offshore oil and gas development.', 1),
(36, 3, 'Sierra Leone', 'sl', 'Sierra Leone is a West African nation on the Atlantic, rebuilding its economy around diamonds, iron ore and agriculture.', '71,740 km²', '8.460600', '-11.779900', 'West Africa, on the Atlantic coast, bordering Guinea and Liberia.', 'English (official), Krio widely spoken as lingua franca', 'Unitary Presidential Republic', 'Freetown was founded by freed slaves in 1787, giving rise to a distinct Krio culture; traditional Poro and Sande society initiations remain significant among interior ethnic groups.', 'Diamond and iron ore mining are leading exports, alongside agriculture (rice, cocoa) and fishing.', 1),
(37, 3, 'Togo', 'tg', 'Togo is a narrow West African nation on the Gulf of Guinea, known for phosphate mining and Voodoo cultural heritage.', '56,785 km²', '8.619500', '0.824800', 'West Africa, a narrow strip on the Gulf of Guinea, bordering Ghana, Burkina Faso and Benin.', 'French (official), Ewe and Kabiye widely spoken', 'Unitary Presidential Republic', 'A center of Voodoo (Vodun) spiritual practice alongside Ewe and Kabiye traditions, with the Akodessewa Fetish Market in Lome among its notable cultural sites.', 'Phosphate mining, regional transit trade through the Port of Lome, and agriculture (cotton, coffee, cocoa).', 1),
(38, 4, 'Djibouti', 'dj', 'Djibouti is a small Horn of Africa nation whose strategic Red Sea location has made it a global logistics and military hub.', '23,200 km²', '11.825100', '42.590300', 'Horn of Africa, on the Bab-el-Mandeb strait linking the Red Sea and Gulf of Aden, bordering Eritrea, Ethiopia and Somalia.', 'French and Arabic (official), Somali and Afar widely spoken', 'Unitary Presidential Republic', 'A crossroads of Afar and Somali pastoralist traditions with Arab and French colonial influence, centred on the port city of Djibouti City.', 'Built almost entirely around its port and shipping services, transit trade for landlocked Ethiopia, and hosting foreign military bases.', 1),
(39, 4, 'Eritrea', 'er', 'Eritrea is a Red Sea nation in the Horn of Africa that gained independence from Ethiopia in 1993 after a long liberation struggle.', '117,600 km²', '15.179400', '39.782300', 'Horn of Africa, on the Red Sea coast, bordering Ethiopia, Sudan and Djibouti.', 'Tigrinya, Arabic and English widely used (no official state language)', 'Unitary One-Party Presidential Republic', 'A mosaic of nine recognized ethnic groups with strong Orthodox Christian and Muslim traditions, and Italian colonial-era architecture in Asmara, a UNESCO World Heritage city.', 'State-directed economy based on subsistence agriculture, mining (gold, potash), and a large national service/military sector.', 1),
(40, 4, 'Ethiopia', 'et', 'Ethiopia is one of Africa\'s oldest independent nations, a mountainous East African country with its own calendar, script and cuisine.', '1,104,300 km²', '9.145000', '40.489700', 'Horn of Africa, landlocked highlands, bordering Eritrea, Djibouti, Somalia, Kenya, South Sudan and Sudan.', 'Amharic (federal working language), Oromo, Tigrinya and others', 'Federal Parliamentary Republic', 'Never colonized, Ethiopia has its own Ge\'ez script, Julian-based calendar, ancient Orthodox Christian churches carved from rock (Lalibela), and the birthplace of coffee.', 'Agriculture-led (coffee is the top export), with rapid recent growth in manufacturing, textiles and infrastructure investment.', 1),
(41, 4, 'Somalia', 'so', 'Somalia is a Horn of Africa nation with the continent\'s longest coastline, rebuilding state institutions after decades of conflict.', '637,657 km²', '5.152100', '46.199600', 'Horn of Africa, with the longest coastline on the continent along the Indian Ocean and Gulf of Aden.', 'Somali and Arabic (official)', 'Federal Parliamentary Republic', 'One of Africa\'s most linguistically and ethnically homogeneous nations, with a rich nomadic pastoralist heritage, oral poetry tradition, and a globally dispersed diaspora.', 'Livestock exports are the backbone of the economy, alongside remittances from a large diaspora and a growing telecoms sector.', 1),
(42, 4, 'South Sudan', 'ss', 'South Sudan is the world\'s youngest country, gaining independence from Sudan in 2011, and one of Africa\'s most oil-dependent economies.', '644,329 km²', '6.877000', '31.307000', 'East-Central Africa, landlocked, bordering Sudan, Ethiopia, Kenya, Uganda, DRC and CAR.', 'English (official), Dinka, Nuer and other local languages', 'Federal Presidential Republic (transitional government)', 'Home to the Dinka, Nuer and dozens of other Nilotic peoples, with cattle-herding traditions central to social and economic life.', 'Oil exports account for the vast majority of government revenue; most of the population depends on subsistence agriculture and cattle herding.', 1),
(43, 4, 'Sudan', 'sd', 'Sudan is a large Nile Valley nation bridging North and Sub-Saharan Africa, home to more ancient pyramids than Egypt.', '1,886,068 km²', '12.862800', '30.217600', 'Northeast Africa, along the Nile River and Red Sea coast, bordering Egypt, Libya, Chad, CAR, South Sudan, Ethiopia and Eritrea.', 'Arabic and English (official)', 'Transitional government (post-conflict)', 'Home to the Nubian pyramids of Meroe (more numerous than Egypt\'s) and a heritage bridging Arab-Islamic and African Nilotic traditions.', 'Agriculture (cotton, gum arabic, sesame) and gold mining are central; the economy has been severely disrupted by prolonged conflict.', 1),
(45, 5, 'Cameroon', 'cm', 'Cameroon is a Central African nation often called \'Africa in miniature\' for its geographic and cultural diversity.', '475,442 km²', '7.369700', '12.354700', 'Central Africa, on the Gulf of Guinea, bordering Nigeria, Chad, CAR, Congo, Gabon and Equatorial Guinea.', 'French and English (official), plus over 250 local languages', 'Unitary Presidential Republic', 'Extraordinary ethnic and linguistic diversity (over 250 groups) spans Sahelian, forest and coastal traditions, with makossa and bikutsi music and the Grassfields chieftaincy traditions.', 'Diversified relative to neighbours: oil and gas, cocoa and coffee exports, timber, and agriculture underpin the economy.', 1),
(46, 5, 'Central African Republic', 'cf', 'The Central African Republic is a landlocked, resource-rich but conflict-affected nation at the heart of the continent.', '622,984 km²', '6.611100', '20.939400', 'Central Africa, landlocked, bordering Chad, Sudan, South Sudan, DRC, Congo and Cameroon.', 'French and Sango (official)', 'Unitary Presidential Republic', 'A largely rural society where Sango serves as a unifying lingua franca across dozens of ethnic groups, with strong oral storytelling and communal music traditions.', 'Subsistence farming, diamonds, gold and timber; among the world\'s least developed economies, hampered by prolonged instability.', 1),
(47, 5, 'Chad', 'td', 'Chad is a vast Sahelian nation bridging North and Central Africa, shaped by desert, Lake Chad and oil revenues.', '1,284,000 km²', '15.454200', '18.732200', 'Central Africa, landlocked, spanning the Sahara in the north to Lake Chad and savanna in the south.', 'French and Arabic (official)', 'Unitary Presidential Republic (transitional government)', 'Over 200 ethnic groups meet at the crossroads of Arab-Islamic Sahelian culture in the north and Sub-Saharan traditions in the south, historically linked by trans-Saharan trade.', 'Oil exports dominate government revenue since the 2000s; the majority of the population relies on subsistence farming and livestock herding.', 1),
(48, 5, 'Republic of the Congo', 'cg', 'The Republic of the Congo (Congo-Brazzaville) is a Central African oil producer straddling the Congo River basin rainforest.', '342,000 km²', '-0.228000', '15.827700', 'Central Africa, on the Gulf of Guinea and Congo River, bordering Gabon, Cameroon, CAR, DRC and Angola (Cabinda).', 'French (official), Lingala and Kituba widely spoken', 'Unitary Presidential Republic', 'A Bantu Kongo cultural heartland known for La Sape (Society of Elegant Persons), a distinctive fashion subculture, and Congolese rumba music.', 'Oil exports dominate the economy, supplemented by timber from extensive rainforest cover.', 1),
(49, 5, 'Democratic Republic of the Congo', 'cd', 'The Democratic Republic of the Congo is Africa\'s second-largest country by area, holding immense mineral wealth and rainforest.', '2,344,858 km²', '-4.038300', '21.758700', 'Central Africa, spanning the Congo Basin rainforest, bordering nine countries and a short Atlantic coastline.', 'French (official), Lingala, Swahili, Kikongo, Tshiluba', 'Unitary Presidential Republic', 'Home to hundreds of ethnic groups and the birthplace of Congolese rumba/soukous, a musical genre influential across the continent, alongside rich Kuba and Luba artistic traditions.', 'Vast mineral reserves (cobalt, copper, coltan, diamonds) drive exports, especially critical minerals for global battery supply chains; most citizens rely on subsistence agriculture.', 1),
(50, 5, 'Equatorial Guinea', 'gq', 'Equatorial Guinea is a small, oil-rich Central African nation, the only African country with Spanish as an official language.', '28,051 km²', '1.650800', '10.267900', 'Central Africa, comprising a mainland region (Rio Muni) and islands including Bioko, on the Gulf of Guinea.', 'Spanish and French (official), Fang and Bubi widely spoken', 'Unitary Presidential Republic', 'A unique blend of Bantu Fang and Bubi traditions with Spanish colonial heritage, uncommon elsewhere on the continent.', 'One of Africa\'s largest oil producers relative to its size, giving it a high per-capita GDP despite limited diversification.', 1),
(51, 5, 'Gabon', 'ga', 'Gabon is a densely forested Central African nation on the Gulf of Guinea, with one of the region\'s highest per-capita incomes from oil.', '267,668 km²', '-0.803700', '11.609400', 'Central Africa, on the Gulf of Guinea, bordering Cameroon, Equatorial Guinea and the Republic of the Congo.', 'French (official), Fang and other Bantu languages', 'Unitary Presidential Republic', 'Over 80% rainforest cover shapes a culture closely tied to the forest, including Bwiti spiritual traditions and extensive national park conservation (Loango, Ivindo).', 'Oil has long dominated exports, alongside manganese mining and growing timber and eco-tourism sectors.', 1),
(52, 5, 'Sao Tome and Principe', 'st', 'Sao Tome and Principe is a tiny two-island nation in the Gulf of Guinea, one of Africa\'s smallest and least-known countries.', '964 km²', '0.186400', '6.613100', 'Central Africa, two volcanic islands in the Gulf of Guinea, off the coast of Gabon.', 'Portuguese (official), Forro Creole widely spoken', 'Unitary Semi-Presidential Republic', 'A Creole culture shaped by Portuguese colonization and African plantation labourers, with ussua and socope musical traditions.', 'Cocoa has historically been the main export; tourism and emerging offshore oil prospects are growing in importance.', 1),
(60, 6, 'Algeria', 'dz', 'Algeria is Africa\'s largest country by area, a Maghreb nation shaped by Saharan desert, Mediterranean coastline, and a hydrocarbon-driven economy.', '2,381,741 km²', '28.033900', '1.659600', 'North Africa, on the Mediterranean coast, bordered by Morocco, Western Sahara, Mauritania, Mali, Niger, Libya and Tunisia.', 'Arabic and Tamazight (official), French widely used', 'Unitary Semi-Presidential Republic', 'Algerian culture blends Arab, Berber (Amazigh) and Mediterranean influences, visible in its music (rai, chaabi), cuisine (couscous, tagine) and a strong tradition of anti-colonial literature and cinema.', 'Dominated by oil and natural gas exports, which fund a large public sector; agriculture and light manufacturing play a smaller role.', 1),
(61, 6, 'Libya', 'ly', 'Libya is a large North African nation on the Mediterranean, holding Africa\'s largest proven oil reserves.', '1,759,540 km²', '26.335100', '17.228300', 'North Africa, on the Mediterranean coast, bordering Egypt, Sudan, Chad, Niger, Algeria and Tunisia.', 'Arabic (official)', 'Transitional government (divided administration)', 'A largely Arab-Berber society with a rich Ottoman and Italian colonial architectural legacy in cities like Tripoli and Benghazi, plus ancient Roman ruins at Leptis Magna.', 'Almost entirely dependent on crude oil and natural gas exports, which fund most government revenue.', 1),
(62, 6, 'Mauritania', 'mr', 'Mauritania is a vast, largely desert West African nation bridging the Arab Maghreb and Sub-Saharan Africa.', '1,030,700 km²', '21.007900', '-10.940800', 'West Africa, mostly Sahara desert, on the Atlantic coast, bordering Western Sahara, Algeria, Mali and Senegal.', 'Arabic (official), Pulaar, Soninke, Wolof', 'Unitary Presidential Republic', 'A crossroads of Arab-Berber Moor and Sub-Saharan West African traditions, with a strong nomadic Bedouin heritage and griot poetic traditions.', 'Iron ore mining and one of the world\'s richest Atlantic fishing grounds are the main export earners, alongside growing offshore gas development.', 1),
(63, 6, 'Morocco', 'ma', 'Morocco is a North African kingdom known for its imperial cities, Atlas Mountains, and growing manufacturing and tourism economy.', '446,550 km²', '31.791700', '-7.092600', 'North Africa, on the Atlantic and Mediterranean coasts, bordering Algeria and (disputed) Western Sahara.', 'Arabic and Tamazight (official), French widely used', 'Constitutional Monarchy', 'Imperial cities like Fes and Marrakech showcase centuries of Arab-Andalusian and Amazigh (Berber) architecture, cuisine (tagine, couscous) and craftsmanship.', 'Diversified: phosphate mining (world\'s largest reserves), automotive and aerospace manufacturing, agriculture, and tourism.', 1),
(64, 6, 'Tunisia', 'tn', 'Tunisia is a small North African nation on the Mediterranean, the birthplace of the 2011 Arab Spring and ancient Carthage.', '163,610 km²', '33.886900', '9.537500', 'North Africa, on the Mediterranean coast, bordering Algeria and Libya.', 'Arabic (official), French widely used', 'Unitary Semi-Presidential Republic', 'Heir to ancient Carthage and centuries of Arab-Andalusian and Ottoman heritage, with well-preserved medinas in Tunis and Kairouan and a strong Mediterranean cultural identity.', 'Diversified economy of tourism, textiles and manufacturing, phosphate mining, and olive oil and agriculture exports.', 1),
(65, 6, 'Egypt', 'eg', 'Egypt is a transcontinental North African nation along the Nile, home to one of history\'s oldest civilizations and a major regional economy.', '1,001,450 km²', '26.820600', '30.802500', 'North Africa, along the Nile River and Mediterranean/Red Sea coasts, bordering Libya, Sudan and Israel.', 'Arabic (official)', 'Unitary Semi-Presidential Republic', 'Heir to ancient Pharaonic civilization (pyramids, hieroglyphics) and a major centre of Arab and Islamic culture, cinema and literature in the modern Middle East.', 'Diversified economy driven by Suez Canal transit revenue, tourism, natural gas, agriculture along the Nile, and remittances.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbldocuments`
--

CREATE TABLE `tbldocuments` (
  `id` int(11) NOT NULL,
  `Title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DocType` enum('Statement','Letter','Report') COLLATE utf8mb4_unicode_ci NOT NULL,
  `Description` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FilePath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `UploadedBy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UploadDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `Is_Active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbldonations`
--

CREATE TABLE `tbldonations` (
  `id` int(11) NOT NULL,
  `DonorName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DonorContact` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Amount` decimal(12,2) NOT NULL,
  `PaymentMethod` enum('MTN MoMo','Airtel Money','Bank Transfer','Other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MTN MoMo',
  `Message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Status` enum('Pending','Confirmed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblevents`
--

CREATE TABLE `tblevents` (
  `id` int(11) NOT NULL,
  `Title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EventType` enum('Event','Summit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Event',
  `Description` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EventDate` date NOT NULL,
  `Location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EventImage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IsInAfricaEvent` tinyint(1) NOT NULL DEFAULT 0,
  `InAfricaAttending` tinyint(1) NOT NULL DEFAULT 0,
  `Is_Active` tinyint(1) NOT NULL DEFAULT 1,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblgallery`
--

CREATE TABLE `tblgallery` (
  `id` int(11) NOT NULL,
  `Title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MediaType` enum('Image','Video') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Image',
  `ImagePath` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `YoutubeUrl` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Is_Active` tinyint(1) NOT NULL DEFAULT 1,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblpostimages`
--

CREATE TABLE `tblpostimages` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblposts`
--

CREATE TABLE `tblposts` (
  `id` int(11) NOT NULL,
  `PostTitle` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CategoryId` int(11) DEFAULT NULL,
  `SubCategoryId` int(11) DEFAULT NULL,
  `RegionId` int(11) DEFAULT NULL,
  `CountryId` int(11) DEFAULT NULL,
  `PostDetails` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PostingDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `Is_Active` int(11) DEFAULT NULL,
  `PostUrl` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PostImage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `viewCounter` int(11) DEFAULT 0,
  `postedBy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastUpdatedBy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `share_count` int(11) DEFAULT 0,
  `Status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblposts`
--

INSERT INTO `tblposts` (`id`, `PostTitle`, `CategoryId`, `SubCategoryId`, `RegionId`, `CountryId`, `PostDetails`, `PostingDate`, `UpdationDate`, `Is_Active`, `PostUrl`, `PostImage`, `viewCounter`, `postedBy`, `lastUpdatedBy`, `share_count`, `Status`) VALUES
(5, 'Kenya Youth Innovation Hub Launches in Nairobi', NULL, NULL, 1, 2, '<p>Test post for Kenya (EAC) to verify country-level filtering on the region page.</p><p>Yes lets focus on</p>', '2026-07-18 12:47:28', '2026-07-23 11:08:13', 1, 'kenya-youth-innovation-hub-test', NULL, 22, 'admin', NULL, 0, 'Approved'),
(6, 'Nigeria Hosts ECOWAS Trade Roundtable', NULL, NULL, 3, 34, '<p>Test post for Nigeria (ECOWAS) to verify country-level filtering on the region page.</p>', '2026-07-18 12:47:28', '2026-07-21 18:45:18', 1, 'nigeria-ecowas-trade-roundtable-test', NULL, 3, 'admin', NULL, 0, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `tblquotes`
--

CREATE TABLE `tblquotes` (
  `id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Quote` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `QuoteImage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_id` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Is_Active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblregions`
--

CREATE TABLE `tblregions` (
  `id` int(11) NOT NULL,
  `RegionName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `RegionLogo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Is_Active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblregions`
--

INSERT INTO `tblregions` (`id`, `RegionName`, `RegionLogo`, `Is_Active`) VALUES
(1, 'EAC', NULL, 1),
(2, 'SADC', NULL, 1),
(3, 'ECOWAS', NULL, 1),
(4, 'IGAD', NULL, 1),
(5, 'ECCAS', NULL, 1),
(6, 'Maghreb Arab Countries Union', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblsubcategory`
--

CREATE TABLE `tblsubcategory` (
  `SubCategoryId` int(11) NOT NULL,
  `CategoryId` int(11) DEFAULT NULL,
  `Subcategory` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SubCatDescription` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PostingDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `Is_Active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gifadvert`
--
ALTER TABLE `gifadvert`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `AdminUserName` (`AdminUserName`);

--
-- Indexes for table `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblcomments`
--
ALTER TABLE `tblcomments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posturl` (`PostUrl`);

--
-- Indexes for table `tblcontactmessages`
--
ALTER TABLE `tblcontactmessages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblcountries`
--
ALTER TABLE `tblcountries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_country_name` (`CountryName`),
  ADD KEY `idx_region` (`RegionId`);

--
-- Indexes for table `tbldocuments`
--
ALTER TABLE `tbldocuments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_doctype` (`DocType`);

--
-- Indexes for table `tbldonations`
--
ALTER TABLE `tbldonations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblevents`
--
ALTER TABLE `tblevents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_date` (`EventType`,`EventDate`);

--
-- Indexes for table `tblgallery`
--
ALTER TABLE `tblgallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_media_type` (`MediaType`);

--
-- Indexes for table `tblpostimages`
--
ALTER TABLE `tblpostimages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `tblposts`
--
ALTER TABLE `tblposts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `postcatid` (`CategoryId`),
  ADD KEY `postsucatid` (`SubCategoryId`),
  ADD KEY `postregionid` (`RegionId`),
  ADD KEY `postcountryid` (`CountryId`),
  ADD KEY `subadmin` (`postedBy`),
  ADD KEY `PostUrl` (`PostUrl`);

--
-- Indexes for table `tblquotes`
--
ALTER TABLE `tblquotes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblregions`
--
ALTER TABLE `tblregions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_region_name` (`RegionName`);

--
-- Indexes for table `tblsubcategory`
--
ALTER TABLE `tblsubcategory`
  ADD PRIMARY KEY (`SubCategoryId`),
  ADD KEY `idx_category` (`CategoryId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gifadvert`
--
ALTER TABLE `gifadvert`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblcategory`
--
ALTER TABLE `tblcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblcomments`
--
ALTER TABLE `tblcomments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblcontactmessages`
--
ALTER TABLE `tblcontactmessages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblcountries`
--
ALTER TABLE `tblcountries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `tbldocuments`
--
ALTER TABLE `tbldocuments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbldonations`
--
ALTER TABLE `tbldonations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblevents`
--
ALTER TABLE `tblevents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblgallery`
--
ALTER TABLE `tblgallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblpostimages`
--
ALTER TABLE `tblpostimages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblposts`
--
ALTER TABLE `tblposts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblquotes`
--
ALTER TABLE `tblquotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblregions`
--
ALTER TABLE `tblregions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblsubcategory`
--
ALTER TABLE `tblsubcategory`
  MODIFY `SubCategoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblpostimages`
--
ALTER TABLE `tblpostimages`
  ADD CONSTRAINT `tblpostimages_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `tblposts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
