-- phpMyAdmin SQL Dump
-- version 2.10.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jan 11, 2008 at 05:56 PM
-- Server version: 5.0.45
-- PHP Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `wordpress`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `wp_piplus`
-- 

CREATE TABLE `wp_piplus` (
  `id` mediumint(9) NOT NULL auto_increment,
  `user` smallint(2) DEFAULT '1' NOT NULL,
  `time` bigint(11) NOT NULL default '0',
  `title` text NOT NULL,
  `description` text NOT NULL,
  `keywords` text NOT NULL,
  `urls` text NOT NULL,
  `priority` smallint(2) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `wp_btpiplus`
-- 

INSERT INTO `wp_piplus` (`id`, `user`, `time`, `title`, `description`, `keywords`, `urls`, `priority`) VALUES 
(2, 1, 1199885113, 'My great idea for a blog post', 'This will be a blog post that changes the world.', 'amazing, witty, funny, not, at, all, boring', 'http://www.google.com/, http://www.bing.com/', 5);
