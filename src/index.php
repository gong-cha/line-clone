<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'load.php';
$_SESSION['nonce'] = uniqid();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LINEライク👍</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            zoom: 2;
        }

        a {
            text-decoration: none;
        }

        main>a {
            display: flex;
            align-items: center;
            padding: 8px;
            gap: 8px;
            color: black;
        }

        main>a:hover {
            background: #F5F5F5;
        }

        img {
            width: 48px;
        }

        .name-container {
            display: flex;
            flex-direction: column;
        }

        .name {
            font-weight: bold;
        }

        .message {
            font-size: 13px;
        }

        .right-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: auto;
        }

        .unread_count {
            border-radius: 11px;
            width: 21px;
            color: white;
            background: #AA00FF;
            text-align: center;
            font-family: system-ui;
        }
    </style>
</head>

<body>
    <header>
        <a href="logout.php?nonce=<?php echo $_SESSION['nonce']; ?>">ログアウト</a>
        <a href="delete-account.php?nonce=<?php echo $_SESSION['nonce']; ?>" onclick="return confirm('本当に退会しますか？')">退会</a>
    </header>
    <main>
        <?php foreach (DB::get_users($_SESSION['user_id']) as $user) : ?>
            <a href="conversation.php?receiver_user_id=<?php echo $user['user_id']; ?>">
                <img src="https://api.dicebear.com/7.x/thumbs/svg?radius=30&seed=<?php echo $user['user_id']; ?>" alt="">
                <div class="name-container">
                    <span class="name"><?php echo $user['name']; ?></span>
                    <span class="message"><?php echo $user['message']; ?></span>
                </div>
                <div class="right-container">
                    <small><?php echo $user['readable_created']; ?></small>
                    <span class="unread_count"><?php echo $user['unread_count']; ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </main>
</body>

</html>