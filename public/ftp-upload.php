<?php

$allowedHosts = ['127.0.0.1', '::1', 'localhost'];
$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';

if (!in_array($remoteAddress, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('This uploader is local-only. Open it from the same computer running XAMPP.');
}

$message = '';
$messageType = '';

function clean_ftp_path(string $path): string
{
    $path = str_replace('\\', '/', trim($path));
    $path = preg_replace('#/+#', '/', $path);

    if ($path === '') {
        return '/';
    }

    return '/' . ltrim($path, '/');
}

function join_ftp_path(string $left, string $right): string
{
    return rtrim(clean_ftp_path($left), '/') . '/' . ltrim($right, '/');
}

function ensure_ftp_directory($connection, string $directory): void
{
    $directory = clean_ftp_path($directory);
    $parts = array_filter(explode('/', trim($directory, '/')));
    $current = '';

    foreach ($parts as $part) {
        $current .= '/' . $part;

        if (@ftp_chdir($connection, $current)) {
            continue;
        }

        if (!@ftp_mkdir($connection, $current) && !@ftp_chdir($connection, $current)) {
            throw new RuntimeException("Could not create remote directory: {$current}");
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? '');
    $port = (int) ($_POST['port'] ?? 21);
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $remotePath = clean_ftp_path($_POST['remote_path'] ?? '/public_html');
    $useFtps = isset($_POST['use_ftps']);

    try {
        if ($host === '' || $username === '' || $password === '') {
            throw new RuntimeException('Host, username, and password are required.');
        }

        if (!isset($_FILES['upload_file']) || $_FILES['upload_file']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Please choose a valid file to upload.');
        }

        $originalName = basename($_FILES['upload_file']['name']);
        $temporaryFile = $_FILES['upload_file']['tmp_name'];

        if ($originalName === '') {
            throw new RuntimeException('The selected file has no valid name.');
        }

        $connection = $useFtps
            ? @ftp_ssl_connect($host, $port, 30)
            : @ftp_connect($host, $port, 30);

        if (!$connection) {
            throw new RuntimeException($useFtps ? 'Could not connect using FTPS.' : 'Could not connect using FTP.');
        }

        try {
            if (!@ftp_login($connection, $username, $password)) {
                throw new RuntimeException('FTP login failed. Check your username and password.');
            }

            ftp_pasv($connection, true);
            ensure_ftp_directory($connection, $remotePath);

            $destination = join_ftp_path($remotePath, $originalName);

            if (!@ftp_put($connection, $destination, $temporaryFile, FTP_BINARY)) {
                throw new RuntimeException("Upload failed for {$destination}.");
            }

            $message = "Uploaded {$originalName} to {$destination}.";
            $messageType = 'success';
        } finally {
            ftp_close($connection);
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Local FTP Uploader</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #10131a;
            color: #eef2ff;
            font-family: Arial, sans-serif;
            padding: 24px;
        }

        main {
            width: min(100%, 520px);
            background: #181d28;
            border: 1px solid #2c3446;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.35);
        }

        h1 {
            margin: 0 0 6px;
            font-size: 24px;
        }

        p {
            margin: 0 0 20px;
            color: #aeb8cc;
            line-height: 1.5;
        }

        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 700;
            color: #dbe5ff;
        }

        input[type="text"],
        input[type="number"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            border: 1px solid #3a465d;
            background: #0f131c;
            color: #eef2ff;
            border-radius: 8px;
            padding: 12px;
            font-size: 15px;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 110px;
            gap: 12px;
        }

        .check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 16px 0;
            color: #dbe5ff;
            font-weight: 700;
        }

        .check input {
            width: 18px;
            height: 18px;
        }

        button {
            width: 100%;
            border: 0;
            border-radius: 8px;
            padding: 14px 16px;
            background: #3b82f6;
            color: white;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
        }

        .message {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .success {
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.45);
            color: #86efac;
        }

        .error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.45);
            color: #fca5a5;
        }
    </style>
</head>
<body>
    <main>
        <h1>Local FTP Uploader</h1>
        <p>Use this only for hosting accounts you own or manage. FTPS is enabled by default.</p>

        <?php if ($message !== ''): ?>
            <div class="message <?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div>
                    <label for="host">FTP Host</label>
                    <input id="host" name="host" type="text" placeholder="cfschools.net.ng" required>
                </div>
                <div>
                    <label for="port">Port</label>
                    <input id="port" name="port" type="number" value="21" min="1" max="65535" required>
                </div>
            </div>

            <label for="username">Username</label>
            <input id="username" name="username" type="text" autocomplete="username" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>

            <label for="remote_path">Remote Folder</label>
            <input id="remote_path" name="remote_path" type="text" value="/public_html" required>

            <label for="upload_file">File</label>
            <input id="upload_file" name="upload_file" type="file" required>

            <label class="check">
                <input name="use_ftps" type="checkbox" checked>
                Use FTPS
            </label>

            <button type="submit">Upload File</button>
        </form>
    </main>
</body>
</html>
