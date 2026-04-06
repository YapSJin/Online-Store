<?php 
include '../config/db.php'; 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/admin_login.php");
    exit;
}
include 'header.php'; 
include '../models/homeModels.php';

$content = getHomeScreenContent();
if(is_post()){
    if(isset($_POST['change-title'])){
        $title = $_POST['title'] ?? '';
        if (!empty($title)) {
            updateTitle($title); 
        }
        temp("info","✅ Update Successful !");
        redirect();
    }elseif(isset($_POST['change-image'])){
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imageName = basename($_FILES['image']['name']);
            $targetDir = '../assets/image/homeScreen/';
            $targetPath = $targetDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                updateHomeContent($targetDir.$imageName, 'image');
            }
            temp("info","✅ Update Successful !");
            redirect();
        }
    }else{
        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            $videoName = basename($_FILES['video']['name']);
            $targetDir = '../assets/image/homeScreen/';
            $targetPath = $targetDir . $videoName;

            if (move_uploaded_file($_FILES['video']['tmp_name'], $targetPath)) {
                updateHomeContent($targetDir.$videoName, 'video'); 
            }
            temp("info","✅ Update Successful !");
            redirect();
        }
    }
}
?>

<link rel="stylesheet" href="../assets/css/homePosterChange.css">

<div class="container">
    <div class="left">
<?php
$homeScreenPath = '../public/homeScreen.php';
if (file_exists($homeScreenPath)) {
    include $homeScreenPath;
} else {
    include '../public/home.php';
}
?>
</div>
    <div class="right">
        <button type="button" class="change-title">Change Title</button>
        <button type="button" class="change-image">Change Image</button>
        <button type="button" class="change-video">Change Video</button>
        <!-- Title Change Section -->
<div id="changes-title" class="form-section" style="display:none;">
    <form method="post">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title">
        <button type="submit" name="change-title">Submit</button>
    </form>
</div>

<!-- Image Change Section -->
<div id="changes-image" class="form-section" style="display:none;">
    <form method="post" enctype="multipart/form-data">
        <label for="image">Upload Image:</label>
        <input type="file" name="image" id="image" accept="image/*">
        <button type="submit" name="change-image">Submit</button>
    </form>
</div>

<!-- Video Change Section -->
<div id="changes-video" class="form-section" style="display:none;">
    <form method="post" enctype="multipart/form-data">
        <label for="video">Upload Video:</label>
        <input type="file" name="video" id="video" accept="video/*">
        <button type="submit" name="change-video">Submit</button>
    </form>
</div>

    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/homePosterChange.js"></script>

<?php include "../public/footer.php" ?>
