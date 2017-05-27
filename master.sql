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

DROP TABLE IF EXISTS `master`;
CREATE TABLE IF NOT EXISTS `master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publish` datetime DEFAULT NULL,
  `pn_type` varchar(100) DEFAULT NULL,
  `orderdate` date DEFAULT NULL,
  `bkpl` datetime DEFAULT NULL,
  `rtp` datetime DEFAULT NULL,
  `so` varchar(100) DEFAULT NULL,
  `so_item` int(11) DEFAULT NULL,
  `product` varchar(100) DEFAULT NULL,
  `product_pl` varchar(100) DEFAULT NULL,
  `bpo` varchar(100) DEFAULT NULL,
  `plo` varchar(100) DEFAULT NULL,
  `pn` varchar(100) NOT NULL,
  `ctrl_id` varchar(100) DEFAULT NULL,
  `sales_area` varchar(100) DEFAULT NULL,
  `shortage_qty` int(11) NOT NULL,
  `required_qty` int(11) DEFAULT NULL,
  `remark_wh` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `status_update` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `shortage_reason` varchar(100) DEFAULT NULL,
  `shortage_reason_detail` varchar(100) DEFAULT NULL,
  `lastupdated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `master`
--

INSERT INTO `master` (`id`, `publish`, `pn_type`, `orderdate`, `bkpl`, `rtp`, `so`, `so_item`, `product`, `product_pl`, `bpo`, `plo`, `pn`, `ctrl_id`, `sales_area`, `shortage_qty`, `required_qty`, `remark_wh`, `status`, `status_update`, `destination`, `shortage_reason`, `shortage_reason_detail`, `lastupdated`) VALUES
(1, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(NULL, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(NULL, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(NULL, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(NULL, '2017-04-24 21:06:00', 'Build', '2017-04-15', '2017-04-21 11:10:00', '2017-04-21 11:23:00', '7700474981', 10, '846162-B21#AKM', 'SY', 'G50679270-000000', '5001899708', '013607-001', '7XA', 'CN01', 4, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
