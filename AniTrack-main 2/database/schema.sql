-- AniTrack Database Schema
-- phpMyAdmin で anitrack データベースを作成してからこのファイルを実行してください

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(30)  NOT NULL UNIQUE,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS works (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    title              VARCHAR(255) NOT NULL,
    title_jp           VARCHAR(255),
    type               ENUM('anime','manga') NOT NULL,
    genre              VARCHAR(100),
    description        TEXT,
    image_url          VARCHAR(500),
    release_year       INT,
    episodes_chapters  VARCHAR(50),
    air_status         ENUM('Airing','Ongoing','Completed') DEFAULT 'Completed',
    created_at         DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_library (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    work_id    INT NOT NULL,
    status     VARCHAR(50) NOT NULL DEFAULT 'Plan to Watch',
    rating     TINYINT UNSIGNED DEFAULT NULL,
    note       TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (work_id) REFERENCES works(id)  ON DELETE CASCADE,
    UNIQUE KEY unique_user_work (user_id, work_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
