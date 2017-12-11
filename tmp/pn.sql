/*
 * examples/mysql/demo.sql
 * 
 * This file is part of EditableGrid.
 * http://editablegrid.net
 *
 * Copyright (c) 2011 Webismymind SPRL
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `master`
--

-- --------------------------------------------------------

--
-- Structure de la table `continent`
--

-- DROP TABLE IF EXISTS `continent`;
-- CREATE TABLE IF NOT EXISTS `continent` (
  -- `id` char(2) NOT NULL,
  -- `name` varchar(30) DEFAULT NULL,
  -- PRIMARY KEY (`id`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `continent`
--

-- INSERT INTO `continent` (`id`, `name`) VALUES
-- ('eu', 'Europa'),
-- ('na', 'North America'),
-- ('sa', 'South America'),
-- ('af', 'Africa'),
-- ('as', 'Asia'),
-- ('au', 'Australia');

-- --------------------------------------------------------

--
-- Structure de la table `country`
--

-- DROP TABLE IF EXISTS `country`;
-- CREATE TABLE IF NOT EXISTS `country` (
  -- `id` char(2) NOT NULL,
  -- `name` varchar(50) NOT NULL,
  -- PRIMARY KEY (`id`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `country`
--

-- INSERT INTO `country` (`id`, `name`) VALUES
-- ('ZM', 'Zambia'),
-- ('ZR', 'Zaire'),
-- ('ZW', 'Zimbabwe');

-- --------------------------------------------------------

--
-- Structure de la table `master`
--

DROP TABLE IF EXISTS `pn`;
CREATE TABLE IF NOT EXISTS `pn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pn` varchar(100) NOT NULL,
  `ctrl_id` varchar(100) NOT NULL,
  `shortage_qty` int(11) NOT NULL,
  `build_shortage_qty` int(11) DEFAULT NULL,
  `passthru_shortage_qty` int(11) DEFAULT NULL,
  `earliest_bkpl` datetime DEFAULT NULL,
  `arrival_qty` int(11) NULL,
  `eta` date DEFAULT NULL,
  `remark` varchar(100) DEFAULT NULL,
  `carrier` varchar(100) DEFAULT NULL,
  `judge_supply` varchar(100) DEFAULT NULL,
  `shortage_reason` varchar(100) DEFAULT NULL,
  `shortage_reason_detail` varchar(100) DEFAULT NULL,
  `bill_number` varchar(100) DEFAULT NULL,
  `delivery` date DEFAULT NULL,
  `delay_reason` varchar(100) DEFAULT NULL,
  `vehicle_info` varchar(100) DEFAULT NULL,
  `received` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `master`
--

-- INSERT INTO `master` (`id`, `publish`, `pn_type`, `orderdate`, `bkpl`, `rtp`, `so`, `so_item`, `product`, `product_pl`, `bpo`, `plo`, `pn`, `ctrl_id`, `sales_area`, `shortage_qty`, `required_qty`, `remark_wh`, `status`, `status_update`, `destination`, `shortage_reason`, `shortage_reason_detail`, `lastupdated`) VALUES
-- (1, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
-- (NULL, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
-- (NULL, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
-- (NULL, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
-- (NULL, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
