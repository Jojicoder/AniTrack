
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(30)  NOT NULL UNIQUE,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('user','admin') DEFAULT 'user',
    avatar     MEDIUMTEXT DEFAULT NULL,
    bio        TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS works (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    title             VARCHAR(255) NOT NULL,
    title_jp          VARCHAR(255),
    type              ENUM('anime','manga') NOT NULL,
    genre             VARCHAR(100),
    description       TEXT,
    image_url         VARCHAR(500),
    release_year      INT,
    episodes_chapters VARCHAR(50),
    air_status        ENUM('Airing','Ongoing','Completed') DEFAULT 'Completed',
    mal_id            INT DEFAULT NULL,
    created_at        DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_library (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    work_id    INT NOT NULL,
    status     VARCHAR(50) NOT NULL DEFAULT 'Plan to Watch',
    rating     TINYINT UNSIGNED DEFAULT NULL,
    note       TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_work (user_id, work_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    work_id     INT NOT NULL,
    user_id     INT NOT NULL,
    rating      TINYINT UNSIGNED NOT NULL,
    body        TEXT NOT NULL,
    user_status VARCHAR(50) DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_work_review (user_id, work_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS friends (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    friend_id  INT NOT NULL,
    status     ENUM('pending','accepted') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_friendship (user_id, friend_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migration (run only if upgrading from old schema):
-- ALTER TABLE users ADD COLUMN role ENUM('user','admin') DEFAULT 'user' AFTER password;
-- ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL AFTER avatar;
-- ALTER TABLE works ADD COLUMN mal_id INT DEFAULT NULL AFTER air_status;
-- ALTER TABLE friends ADD COLUMN status ENUM('pending','accepted') NOT NULL DEFAULT 'pending' AFTER friend_id;
-- ALTER TABLE user_library ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
-- UPDATE friends SET status = 'accepted';
-- INSERT IGNORE INTO friends (user_id, friend_id, status)
-- SELECT friend_id, user_id, 'accepted' FROM friends WHERE status = 'accepted';
