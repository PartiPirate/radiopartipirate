SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données :  `radio`
--

-- --------------------------------------------------------

--
-- Structure de la table `exceptional_programs`
--

CREATE TABLE `exceptional_programs` (
  `epr_id` int(11) NOT NULL,
  `epr_date` date NOT NULL,
  `epr_start` time NOT NULL,
  `epr_end` time NOT NULL,
  `epr_program_entry_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Holds the exceptional program informations';

-- --------------------------------------------------------

--
-- Structure de la table `program_entries`
--

CREATE TABLE `program_entries` (
  `pen_id` int(11) NOT NULL,
  `pen_title` varchar(255) NOT NULL,
  `pen_parameters` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Holds program entries information (and parameters)';

-- --------------------------------------------------------

--
-- Structure de la table `template_programs`
--

CREATE TABLE `template_programs` (
  `tpr_id` int(11) NOT NULL,
  `tpr_day` smallint(6) NOT NULL,
  `tpr_start` time NOT NULL,
  `tpr_end` time NOT NULL,
  `tpr_program_entry_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='The templates programs for normal program';

-- --------------------------------------------------------

--
-- Structure de la table `tracks`
--

CREATE TABLE `tracks` (
  `tra_id` int(11) NOT NULL,
  `tra_url` varchar(255) NOT NULL,
  `tra_title` varchar(255) NOT NULL,
  `tra_author` varchar(255) NOT NULL,
  `tra_album` varchar(255) NOT NULL,
  `tra_duration` int(11) NOT NULL,
  `tra_genres` varchar(2048) NOT NULL,
  `tra_free` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Holding tracks information';

-- --------------------------------------------------------

--
-- Structure de la table `track_logs`
--

CREATE TABLE `track_logs` (
  `tlo_id` int(11) NOT NULL,
  `tlo_track_id` int(11) NOT NULL,
  `tlo_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='holds playing information';

--
-- Index pour les tables exportées
--

--
-- Index pour la table `exceptional_programs`
--
ALTER TABLE `exceptional_programs`
  ADD PRIMARY KEY (`epr_id`),
  ADD KEY `epr_date` (`epr_date`),
  ADD KEY `epr_start` (`epr_start`),
  ADD KEY `epr_end` (`epr_end`),
  ADD KEY `epr_program_entry_id` (`epr_program_entry_id`);

--
-- Index pour la table `program_entries`
--
ALTER TABLE `program_entries`
  ADD PRIMARY KEY (`pen_id`),
  ADD KEY `pen_title` (`pen_title`);

--
-- Index pour la table `template_programs`
--
ALTER TABLE `template_programs`
  ADD PRIMARY KEY (`tpr_id`),
  ADD KEY `tpr_day` (`tpr_day`),
  ADD KEY `tpr_start` (`tpr_start`),
  ADD KEY `tpr_end` (`tpr_end`),
  ADD KEY `tpr_program_entry_id` (`tpr_program_entry_id`);

--
-- Index pour la table `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`tra_id`),
  ADD UNIQUE KEY `tra_url` (`tra_url`),
  ADD KEY `tra_genres` (`tra_genres`(767)),
  ADD KEY `tra_free` (`tra_free`),
  ADD KEY `tra_album` (`tra_album`),
  ADD KEY `tra_author` (`tra_author`),
  ADD KEY `tra_title` (`tra_title`);

--
-- Index pour la table `track_logs`
--
ALTER TABLE `track_logs`
  ADD PRIMARY KEY (`tlo_id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `exceptional_programs`
--
ALTER TABLE `exceptional_programs`
  MODIFY `epr_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `program_entries`
--
ALTER TABLE `program_entries`
  MODIFY `pen_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `template_programs`
--
ALTER TABLE `template_programs`
  MODIFY `tpr_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `tracks`
--
ALTER TABLE `tracks`
  MODIFY `tra_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `track_logs`
--
ALTER TABLE `track_logs`
  MODIFY `tlo_id` int(11) NOT NULL AUTO_INCREMENT;