<?php
if (!isset($_GET['file'])) {
    die("File tidak ditemukan.");
}

$file = basename($_GET['file']);
$path = __DIR__ . "/ebooks/" . $file;

if (!file_exists($path)) {
    die("File tidak tersedia.");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Viewer - <?php echo htmlspecialchars($file); ?></title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f5;
        }

        .viewer-container {
            width: 100%;
            height: 100vh;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>

<body>
    <div class="viewer-container">
        <iframe src="ebooks/<?php echo urlencode($file); ?>"></iframe>
    </div>
</body>

</html>