<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'load.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['nonce'] === $_POST['nonce']) {
    $message = $_POST['message'];
    $filepath = get_filepath();
    if ($message || $filepath) {
        $message_id = DB::post_message($message, $filepath, $_POST['sender_user_id'], $_POST['receiver_user_id']);
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '#' . $message_id);
        exit;
    }
    $error_message = '投稿できませんでした。';
}
$_SESSION['nonce'] = uniqid();
$user = DB::get_user($_GET['receiver_user_id']);
DB::set_is_read($_GET['receiver_user_id'], $_SESSION['user_id']);
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
            display: flex;
            flex-direction: column;
            margin: 0 auto;
            max-width: 512px;
            height: 100%;
        }

        a {
            text-decoration: none;
        }

        h1 {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar {
            width: 64px;
        }

        .messages {
            flex: 1;
            overflow: auto;
        }

        .message {
            display: inline-block;
            margin: 4px 0;
            border-radius: 16px;
            padding: 8px 16px;
            background: #86d97b;
        }

        .message-left {
            display: inline-flex;
            flex-direction: column;
            vertical-align: bottom;
        }

        .message-right {
            vertical-align: bottom;
        }

        img {
            display: block;
            margin: 4px 0;
            width: 256px;
        }

        form {
            margin: 16px 0;
            text-align: center;
        }
    </style>
</head>

<body>
    <h1>
        <a href=".">＜</a><img class="avatar" src="https://api.dicebear.com/7.x/thumbs/svg?radius=30&seed=<?php echo $user['user_id']; ?>" alt=""><?php echo $user['name']; ?></h1>
    <div class="messages">
        <?php foreach (DB::get_messages($_SESSION['user_id'], $_GET['receiver_user_id']) as $message) : ?>
            <div id="<?php echo $message['message_id']; ?>" title="<?php echo $message['created_at']; ?>" align="<?php echo $message['sender_user_id'] == $_SESSION['user_id'] ? 'right' : 'left'; ?>">
                <?php if ($message['sender_user_id'] == $_SESSION['user_id']) : ?>
                    <div class="message-left">
                        <?php if($message['is_read']): ?>
                            <span>既読</span>
                        <?php endif; ?>
                        <span><?php echo substr($message['created_at'], 11, 5); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($message['message']) : ?>
                    <span class="message"><?php echo $message['message']; ?></span>
                <?php endif; ?>
                <?php if ($message['sender_user_id'] != $_SESSION['user_id']) : ?>
                    <span class="message-right"><?php echo substr($message['created_at'], 11, 5); ?></span>
                <?php endif; ?>
                <?php if ($message['filepath']) : ?>
                    <img src="<?php echo $message['filepath']; ?>" alt="" loading="lazy">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        const messages = document.querySelector('.messages')
        messages.scrollTo(0, messages.scrollHeight)
        setTimeout(() => messages.scrollTo(0, messages.scrollHeight), 500)
        requestIdleCallback(() => document.querySelector('[autofocus]').focus())
        setTimeout(() => {
            const es = new EventSource('sse.php?receiver_user_id=<?php echo $_GET['receiver_user_id']; ?>', {
                withCredentials: true
            });
            es.addEventListener('message', e => {
                const {
                    message_id,
                    message,
                    filepath
                } = JSON.parse(e.data)
                const div = document.createElement('div')
                div.id = message_id
                div.align = 'left'
                if (message) {
                    const span = div.appendChild(document.createElement('span'))
                    span.classList.add('message')
                    span.textContent = message
                }
                if (filepath) {
                    const img = div.appendChild(document.createElement('img'))
                    img.src = filepath
                    img.addEventListener('load', () => messages.scrollTo(0, messages.scrollHeight))
                }
                messages.appendChild(div)
                messages.scrollTo(0, messages.scrollHeight)
            });
            // 移動を感知して切断
            addEventListener('beforeunload', () => es.close())
            addEventListener('unload', () => es.close())
            addEventListener('close', () => es.close())
        }, <?php echo php_sapi_name() === 'cli-server' ? 1000 : 1000; ?>)
    </script>
    <?php if (isset($error_message)) : ?>
        <div><?php echo $error_message; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="nonce" value="<?php echo $_SESSION['nonce']; ?>">
        <input type="hidden" name="sender_user_id" value="<?php echo $_SESSION['user_id']; ?>">
        <input type="hidden" name="receiver_user_id" value="<?php echo $_GET['receiver_user_id']; ?>">
        <input type="file" name="file" accept="image/*">
        <label>
            <input type="text" maxlength="255" name="message" autofocus>
        </label>
        <button>送信</button>
    </form>
</body>

</html>