<?php

require_once 'load.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = DB::signup($_POST['name'] ?: $_POST['email'], $_POST['email'], $_POST['password']);
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
    <title>LINEライク👍</title>
    <style>
        html {
            height: 100%;
        }

        body {
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            align-items: center;
            zoom: 2;
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
    </style>
</head>

<body>
    <img src="https://gong-cha.github.io/WINE.svg" alt="logo" class="logo">
    <form method="post">
        <label>
            <input type="text" name="name" maxlength="255" placeholder="名前" autocomplete="name" autofocus>
        </label>
        <label>
            <input type="email" name="email" required maxlength="191" placeholder="メール">
        </label>
        <label>
            <input type="password" name="password" required placeholder="パスワード">
        </label>
        <button>登録</button>
    </form>
</body>

</html>
