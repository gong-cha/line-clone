<?php
require_once 'load.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = DB::login($_POST['email'], $_POST['password']);
    if ($user_id) {
        session_start();
        $_SESSION['user_id'] = $user_id;
        header('Location: .');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="https://gong-cha.github.io/favicon.ico" />
    <title>LINEライク👍</title>
    <style>
        html {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0;
            height: 100%;
            zoom: 2;
        }

        a {
            text-decoration: none;
        }

        .logo {
            width: 128px;
            margin-bottom: 32px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        hr {
            width: 100%;
        }
    </style>
</head>

<body>
    <img src="https://gong-cha.github.io/WINE.svg" alt="logo" class="logo">
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') : ?>
        ログインできませんでした。
    <?php endif; ?>
    <form method="post">
        <label>
            <input type="email" name="email" required maxlength="191" placeholder="メールアドレス" autofocus>
        </label>
        <label>
            <input type="password" name="password" required placeholder="パスワード">
        </label>
        <button>ログイン</button>
        <hr>
    </form>
    <a href="signup.php">新規登録</a>
</body>

</html>
