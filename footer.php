<?php
// Fetch Bible verse using cURL as a fallback
function getBibleVerse() {
    $apiUrl = 'https://beta.ourmanna.com/api/v1/get/?format=json&order=daily';

    // Use cURL to fetch the data
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $apiUrl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FAILONERROR, true);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        return "Unable to retrieve the verse. Please try again later.";
    }

    curl_close($curl);

    // Parse the JSON response
    $verseData = json_decode($response, true);
    if (isset($verseData['verse']['details'])) {
        $verseText = $verseData['verse']['details']['text'];
        $verseReference = $verseData['verse']['details']['reference'];
        return "<em>\"$verseText\"</em> - <strong>$verseReference</strong>";
    } else {
        return "Unable to retrieve the verse. Please try again later.";
    }
}
?>

<!-- Footer content -->
<footer class="bg-dark text-white py-3 mt-1 w-100" style="width: 40vw; margin-left: -1px;">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Left Side - Bible Verse (3/4 of the page) -->
            <div class="text-start" style="flex: 3;">
                <p id="bibleVerse" class="mb-0" style="font-size: 12px;">
                <strong>Bible Verse (NIV):</strong>
                    <?php
                    echo getBibleVerse();
                    ?>
                </p>
            </div>

            <!-- Right Side - Church Name (1/4 of the page) -->
            <div class="text-end" style="flex: 1;">
                <h6 class="mb-0" style="font-size: 12px;">&copy; <?= date("Y"); ?> PCEA MUKINYI </h6>
            </div>
        </div>
    </div>
</footer>

<style>
    footer {
        background: linear-gradient(135deg, #343a40, #212529); /* Gradient for a modern look */
        box-shadow: 0px -3px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
        margin-left: 0; /* Remove any extra margin */
        width: 100vw; /* Full width to stretch end to end */
    }

    footer p {
        font-size: 12px; /* Reduced font size for better fit */
        font-style: italic;
    }

    footer h6 {
        font-weight: bold;
        font-size: 12px; /* Reduced font size for better fit */
    }
</style>

<!-- Add FontAwesome for icons -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
