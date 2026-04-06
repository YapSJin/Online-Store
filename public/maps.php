<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once "header.php";
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="../assets/css/maps.css">

<main class="map-page-wrapper">
    <div class="store-container">
        <aside class="store-sidebar">
            <div class="sidebar-header">
                <h2>Our Stores</h2>
                <p>Find the nearest street style hub.</p>
            </div>
            <div id="store-list" class="store-list-scroll"></div>
        </aside>

        <div id="leafletMap" class="map-display"></div>
    </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../assets/js/maps.js"></script>

<?php include_once "footer.php"; ?>
