-- utf8mb4の場合、インデックスに使えるのは191まで
-- https://blog.e2info.co.jp/2017/04/17/mysql%E3%81%AE%E3%82%A4%E3%83%B3%E3%83%87%E3%83%83%E3%82%AF%E3%82%B9%E3%82%B5%E3%82%A4%E3%82%BA%E3%81%AB767byte%E3%81%BE%E3%81%A7%E3%81%97%E3%81%8B%E3%81%A4%E3%81%8B%E3%81%88%E3%81%AA%E3%81%84/
CREATE TABLE IF NOT EXISTS user
(
    user_id  INT NOT NULL AUTO_INCREMENT,
    name     VARCHAR(255),
    email    VARCHAR(191) UNIQUE,
    password VARCHAR(255),
    PRIMARY KEY (user_id)
);

CREATE TABLE IF NOT EXISTS message
(
    message_id       INT NOT NULL AUTO_INCREMENT,
    message          VARCHAR(255),
    filepath         VARCHAR(255),
    sender_user_id   INT NOT NULL,
    receiver_user_id INT NOT NULL,
    is_read          BOOLEAN,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (message_id),
    KEY sender_user_id_receiver_user_id (sender_user_id, receiver_user_id),
    FOREIGN KEY (sender_user_id)
        REFERENCES user(user_id)
        ON DELETE CASCADE,
    FOREIGN KEY (receiver_user_id)
        REFERENCES user(user_id)
        ON DELETE CASCADE
);

-- for PlanetScale
-- CREATE TABLE IF NOT EXISTS message
-- (
--     message_id       INT NOT NULL AUTO_INCREMENT,
--     message          VARCHAR(255),
--     filepath         VARCHAR(255),
--     sender_user_id   INT NOT NULL,
--     receiver_user_id INT NOT NULL,
--     is_read          BOOLEAN,
--     created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     PRIMARY KEY (message_id),
--     KEY sender_user_id_receiver_user_id (sender_user_id, receiver_user_id)
-- );
