<?php

if (file_exists('dotenv.php')) {
    require_once('dotenv.php');
} else {
    // localhostを使うと遅くなります。127.0.0.1を使ってください。
    // See: https://stackoverflow.com/a/9800798/5602117
    // https://www.phpmyadmin.co/index.php?db=sql12650040&target=db_structure.php
    define('DB_HOST', getenv('DB_HOST') ?: 'sql12.freemysqlhosting.net');
    define('DB_USERNAME', getenv('DB_USERNAME') ?: 'sql12650040');
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'qUhFDwRMSf');
    define('DB_DATABASE', getenv('DB_DATABASE') ?: 'sql12650040');
}

class DB
{
    private static ?PDO $pdo = null;

    private static function getPdo(): PDO
    {
        if (empty(self::$pdo)) {
            try {
                $options = [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone='+09:00'"
                ];
                if ( defined('DB_HOST') && strpos(DB_HOST, 'psdb') !== false ) {
                    // for PlanetScale
                    $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
                }
                self::$pdo = new PDO("mysql:host=" . DB_HOST . ';port=3306;dbname=' . DB_DATABASE . ';charset=utf8mb4', DB_USERNAME, DB_PASSWORD, $options);
            } catch (PDOException $error) {
                echo $error->getMessage();
            }
        }
        return self::$pdo;
    }

    static function signup($name, $email, $password)
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = self::getPdo()->prepare('INSERT INTO user (name, email, password) VALUES (:name, :email, :password)');
        try {
            $stmt->execute(['name' => $name, 'email' => $email, 'password' => $password]);
        } catch (PDOException $error) {
            echo $error->getMessage();
        }
        return self::getPdo()->lastInsertId();
    }

    static function login($email, $password)
    {
        $stmt = self::getPdo()->prepare('SELECT * FROM user WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }
        return $user['user_id'];
    }

    static function get_user($user_id)
    {
        $stmt = self::getPdo()->prepare('SELECT * FROM user WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetch();
    }

    static function delete_account($user_id)
    {
        $stmt = self::getPdo()->prepare('DELETE FROM user WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $user_id]);
    }

    static function get_users($user_id)
    {
        $stmt = self::getPdo()->prepare("SELECT * FROM user LEFT JOIN (SELECT sender_user_id, count(*) unread_count FROM message WHERE is_read IS NULL AND receiver_user_id = :user_id GROUP BY sender_user_id) unread ON user.user_id = unread.sender_user_id LEFT JOIN (SELECT sender_user_id, MAX(created_at) latest_created_at FROM message WHERE receiver_user_id = :user_id GROUP BY sender_user_id) latest_created ON user.user_id = latest_created.sender_user_id LEFT JOIN (SELECT (CASE WHEN sender_user_id = :user_id THEN receiver_user_id ELSE sender_user_id END) except_user_id, (CASE WHEN message <> '' THEN message WHEN filepath IS NOT NULL THEN '画像を送信しました' END) message, (CASE TIMESTAMPDIFF(DAY, created_at, CURRENT_TIMESTAMP) WHEN 0 THEN TIME_FORMAT(created_at, '%k:%i') WHEN 1 THEN '昨日' ELSE DATE_FORMAT(created_at, '%c/%e') END) readable_created FROM message WHERE message_id IN (SELECT MAX(message_id) FROM (SELECT (CASE WHEN sender_user_id = :user_id THEN receiver_user_id ELSE sender_user_id END) user_id, message_id, message FROM message WHERE sender_user_id = :user_id OR receiver_user_id = :user_id) flat_messages GROUP BY flat_messages.user_id)) user_messages ON user.user_id = user_messages.except_user_id WHERE user.user_id != :user_id ORDER BY latest_created.latest_created_at DESC");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    static function get_messages($sender_user_id, $receiver_user_id)
    {
        $stmt = self::getPdo()->prepare('SELECT * FROM message WHERE sender_user_id = :sender_user_id AND receiver_user_id = :receiver_user_id OR sender_user_id = :receiver_user_id AND receiver_user_id = :sender_user_id ORDER BY message_id');
        $stmt->execute(['sender_user_id' => $sender_user_id, 'receiver_user_id' => $receiver_user_id]);
        return $stmt->fetchAll();
    }

    static function get_latest_message($sender_user_id, $receiver_user_id)
    {
        $stmt = self::getPdo()->prepare('SELECT * FROM message WHERE sender_user_id = :sender_user_id AND receiver_user_id = :receiver_user_id ORDER BY message_id DESC LIMIT 1');
        $stmt->execute(['sender_user_id' => $sender_user_id, 'receiver_user_id' => $receiver_user_id]);
        return $stmt->fetch();
    }

    static function post_message($message, $filepath, $sender_user_id, $receiver_user_id)
    {
        $stmt = self::getPdo()->prepare('INSERT INTO message (message, filepath, sender_user_id, receiver_user_id) VALUES (:message, :filepath, :sender_user_id, :receiver_user_id)');
        try {
            $stmt->execute(['message' => $message, 'filepath' => $filepath, 'sender_user_id' => $sender_user_id, 'receiver_user_id' => $receiver_user_id]);
        } catch (PDOException $error) {
            echo $error->getMessage();
        }
        return self::getPdo()->lastInsertId();
    }

    static function set_is_read($sender_user_id, $receiver_user_id)
    {
        $stmt = self::getPdo()->prepare('UPDATE message SET is_read = 1 WHERE sender_user_id = :sender_user_id AND receiver_user_id = :receiver_user_id');
        try {
            $stmt->execute(['sender_user_id' => $sender_user_id, 'receiver_user_id' => $receiver_user_id]);
        } catch (PDOException $error) {
            echo $error->getMessage();
        }
    }
}

function get_filepath(): ?string
{
    $available_extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'apng', 'webp', 'bmp', 'avif', 'ico'];
    if (
        array_key_exists('file', $_FILES)
        && !$_FILES['file']['error']
        && strpos($_FILES['file']['type'], 'image/') === 0
        && in_array(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION), $available_extensions)
    ) {
        $dirname = 'uploads' . DIRECTORY_SEPARATOR . uniqid();
        $filepath = $dirname . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
        // データベースのカラムサイズより大きいとき
        if (strlen($filepath) > 255) {
            return null;
        }
        // ディレクトリの作成に失敗したとき
        if (!mkdir($dirname, 0777, true)) {
            return null;
        }
        // ファイルの移動に失敗したとき
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
            rmdir($dirname);
            return null;
        }
        return $filepath;
    }
    return null;
}
