-- Thor-Nament MySQL schema
-- Run this file in your MySQL client (e.g., phpMyAdmin) to create tables

CREATE DATABASE IF NOT EXISTS `e-sports` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `e-sports`;

CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(50) NOT NULL UNIQUE,
	email VARCHAR(120) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teams (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL UNIQUE,
	owner_user_id INT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS players (
	id INT AUTO_INCREMENT PRIMARY KEY,
	team_id INT NOT NULL,
	nickname VARCHAR(100) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- game_type: 'BR' (Battle Royale) or 'VS' (Versus)
CREATE TABLE IF NOT EXISTS tournaments (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(150) NOT NULL,
	game_type ENUM('BR','VS') NOT NULL,
	description TEXT,
	created_by INT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tournament_teams (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tournament_id INT NOT NULL,
	team_id INT NOT NULL,
	UNIQUE KEY uniq_tournament_team (tournament_id, team_id),
	FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
	FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- For 'VS' matches, team_a_id/team_b_id and scores are used
-- For 'BR' matches, results are stored in match_results (one per team)
CREATE TABLE IF NOT EXISTS matches (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tournament_id INT NOT NULL,
	match_date DATETIME DEFAULT CURRENT_TIMESTAMP,
	team_a_id INT NULL,
	team_b_id INT NULL,
	score_a INT NULL,
	score_b INT NULL,
	FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
	FOREIGN KEY (team_a_id) REFERENCES teams(id) ON DELETE SET NULL,
	FOREIGN KEY (team_b_id) REFERENCES teams(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- For Battle Royale results
CREATE TABLE IF NOT EXISTS match_results (
	id INT AUTO_INCREMENT PRIMARY KEY,
	match_id INT NOT NULL,
	team_id INT NOT NULL,
	placement INT NULL,
	kills INT NULL,
	FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
	FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Denormalized standings table recalculated after each result
CREATE TABLE IF NOT EXISTS standings (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tournament_id INT NOT NULL,
	team_id INT NOT NULL,
	points INT NOT NULL DEFAULT 0,
	wins INT NOT NULL DEFAULT 0,
	losses INT NOT NULL DEFAULT 0,
	draws INT NOT NULL DEFAULT 0,
	kills INT NOT NULL DEFAULT 0,
	placement_points INT NOT NULL DEFAULT 0,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY uniq_tournament_team (tournament_id, team_id),
	FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
	FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;


