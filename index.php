<?php
// index.php

function fetchMusicbrainzData($query)
{
    $url = "https://musicbrainz.org/ws/2/recording?query=$query&fmt=json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: ISRCWebApp/1.0 (youremail@example.com)'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

$data = null;
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
    if ($mode === 'by_isrc' && isset($_GET['isrc'])) {
        $isrc = htmlspecialchars($_GET['isrc']);
        $query = "isrc:" . urlencode($isrc);
        $data = fetchMusicbrainzData($query);
    } elseif ($mode === 'by_song' && isset($_GET['title'], $_GET['artist'])) {
        $title = htmlspecialchars($_GET['title']);
        $artist = htmlspecialchars($_GET['artist']);
        $query = "recording:\"$title\" AND artist:\"$artist\"";
        $data = fetchMusicbrainzData(urlencode($query));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ISRC Lookup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        input[type="text"] { width: 300px; padding: 5px; }
        input[type="submit"] { padding: 5px 10px; }
        .recording { border: 1px solid #ccc; margin-bottom: 20px; padding: 10px; }
        pre { background-color: #f5f5f5; padding: 10px; }
        .section { margin-bottom: 30px; }
    </style>
</head>
<body>
    <h1>ISRC Lookup (MusicBrainz)</h1>

    <div class="section">
        <h2>Cari Berdasarkan ISRC</h2>
        <form method="get">
            <input type="hidden" name="mode" value="by_isrc">
            <input type="text" name="isrc" placeholder="Masukkan ISRC" required>
            <input type="submit" value="Cari">
        </form>
    </div>

    <div class="section">
        <h2>Cari ISRC dari Judul & Artis</h2>
        <form method="get">
            <input type="hidden" name="mode" value="by_song">
            <input type="text" name="title" placeholder="Judul Lagu" required>
            <input type="text" name="artist" placeholder="Nama Artis" required>
            <input type="submit" value="Cari">
        </form>
    </div>

    <?php if ($data) : ?>
        <h2>Hasil:</h2>
        <?php if (!empty($data['recordings'])) : ?>
            <?php foreach ($data['recordings'] as $recording) : ?>
                <div class="recording">
                    <strong>Judul:</strong> <?= htmlspecialchars($recording['title']) ?><br>
                    <strong>Artis:</strong>
                    <?php foreach ($recording['artist-credit'] as $artist) {
                        echo htmlspecialchars($artist['name']) . ' ';
                    } ?><br>
                    <?php if (!empty($recording['isrcs'])) : ?>
                        <strong>ISRC:</strong> <?= implode(', ', $recording['isrcs']) ?><br>
                    <?php endif; ?>
                    <?php if (!empty($recording['length'])) : ?>
                        <strong>Durasi:</strong> <?= round($recording['length'] / 1000) ?> detik<br>
                    <?php endif; ?>
                    <strong>ID:</strong> <?= htmlspecialchars($recording['id']) ?><br>
                    <strong>Detail JSON:</strong>
                    <pre><?= json_encode($recording, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>Tidak ditemukan hasil.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
