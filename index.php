<?php
// Fungsi untuk mendapatkan access token Spotify
function getSpotifyAccessToken()
{
    $client_id = '2a7adc3b5b184172bdda963735513637'; // Ganti dengan Client ID Anda
    $client_secret = '0372bdcd32b64233a55e60ae8a6463b5'; // Ganti dengan Client Secret Anda

    $url = "https://accounts.spotify.com/api/token";
    $data = array(
        'grant_type' => 'client_credentials'
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic " . base64_encode("$client_id:$client_secret")
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);
    return $response_data['access_token'];
}

// Fungsi untuk mencari lagu berdasarkan judul dan artis
function searchSpotifyTrack($title, $artist)
{
    $access_token = getSpotifyAccessToken();

    $url = "https://api.spotify.com/v1/search?q=track:" . urlencode($title) . "+artist:" . urlencode($artist) . "&type=track&limit=1";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $access_token"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

$data = null;
if (isset($_GET['title']) && isset($_GET['artist'])) {
    $title = htmlspecialchars($_GET['title']);
    $artist = htmlspecialchars($_GET['artist']);
    $data = searchSpotifyTrack($title, $artist);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ISRC Search</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header>
        <h1>ISRC Search</h1>
    </header>

    <form method="get" onsubmit="showLoading()">
        <label for="title">Song Title:</label>
        <input type="text" name="title" placeholder="Enter song title" value="<?= isset($title) ? $title : '' ?>" required>
        <br>
        <label for="artist">Artist Name:</label>
        <input type="text" name="artist" placeholder="Enter artist name" value="<?= isset($artist) ? $artist : '' ?>" required>
        <br>
        <input type="submit" value="Search">
    </form>

    <div class="loading" id="loading">
        <img src="https://i.gifer.com/YCZH.gif" alt="loading">
        <p>Loading...</p>
    </div>

    <?php if ($data) : ?>
        <div class="result">
            <img src="<?= isset($data['tracks']['items'][0]['album']['images'][0]['url']) ? $data['tracks']['items'][0]['album']['images'][0]['url'] : 'https://via.placeholder.com/150' ?>" alt="Album Cover">
            <div class="text">
                <h2>Results:</h2>
                <?php
                if (isset($data['tracks']['items'][0])) {
                    $track = $data['tracks']['items'][0];
                    $isrc = $track['external_ids']['isrc'] ?? 'ISRC not found';

                    echo "<p><strong>Track:</strong> " . htmlspecialchars($track['name']) . "</p>";
                    echo "<p><strong>Artist:</strong> " . htmlspecialchars($track['artists'][0]['name']) . "</p>";
                    echo "<p><strong>ISRC:</strong> $isrc</p>";
                    echo "<p><strong>Duration:</strong> " . round($track['duration_ms'] / 1000) . " seconds</p>";
                } else {
                    echo "<p>No results found.</p>";
                }
                ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="footer">
        <p>&copy; 2025 ISRC Search | Powered by Spotify API</p>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }
    </script>

</body>

</html>