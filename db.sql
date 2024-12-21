-- File: database.sql

CREATE DATABASE chat_application;
USE chat_application;

-- Users table
CREATE TABLE users (
    user_id VARCHAR(20) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    profile_photo VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Connections table (for user relationships)
CREATE TABLE connections (
    connection_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(20),
    connected_user_id VARCHAR(20),
    status ENUM('pending', 'accepted', 'blocked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (connected_user_id) REFERENCES users(user_id),
    UNIQUE KEY unique_connection (user_id, connected_user_id)
);

-- Messages table
CREATE TABLE messages (
    message_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sender_id VARCHAR(20),
    receiver_id VARCHAR(20),
    message_type ENUM('text', 'file', 'code', 'image', 'video', 'audio') NOT NULL,
    message_content TEXT NOT NULL,
    file_url VARCHAR(255) NULL,
    code_language VARCHAR(50) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);

-- Call logs table
CREATE TABLE call_logs (
    call_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    caller_id VARCHAR(20),
    receiver_id VARCHAR(20),
    call_type ENUM('audio', 'video') NOT NULL,
    status ENUM('missed', 'completed', 'rejected') NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    FOREIGN KEY (caller_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);