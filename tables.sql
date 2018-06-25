CREATE TABLE `LoginAttempts` (
  `username` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `logincount` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `SessionData`
--

CREATE TABLE `SessionData` (
  `sessionid` varchar(40) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `csrf` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `LoginAttempts`
--
ALTER TABLE `LoginAttempts`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `SessionData`
--
ALTER TABLE `SessionData`
  ADD UNIQUE KEY `sessionid` (`sessionid`,`ip`);
COMMIT;