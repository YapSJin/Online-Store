<?php
require "../config/db.php";

function getUserRole($user_id) {
    global $_db;

    $stm=$_db->prepare("SELECT role from users WHERE id=?");
    $stm->execute([$user_id]);
    $result=$stm->fetch(PDO::FETCH_OBJ);
    return $result ? $result->role : null;
}
function getUserEmailById($user_id) {
    global $_db;

    $stm=$_db->prepare("SELECT email from users WHERE id=?");
    $stm->execute([$user_id]);
    return $stm->fetch(PDO::FETCH_OBJ);
}

function getAllUser($role, $search = '') {
    global $_db;

    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];

    if ($role !== 'all') {
        $sql .= " AND role = ?";
        $params[] = $role;
    }

    if (!empty($search)) {
        $sql .= " AND (username LIKE ? OR email LIKE ? OR phone_num LIKE ?)";
        $likeSearch = "%$search%";
        $params[] = $likeSearch;
        $params[] = $likeSearch;
        $params[] = $likeSearch;
    }

    $stm = $_db->prepare($sql);
    $stm->execute($params);

    return $stm->fetchAll(PDO::FETCH_OBJ);
}

if (!function_exists('getUserPasswordById')) {
    function getUserPasswordById($user_id){
        global $_db;
        $stm=$_db->prepare("SELECT password FROM users WHERE id =?");
        $stm->execute([$user_id]);
        return $stm->fetch(PDO::FETCH_OBJ);
    }
}

function deleteUserById($user_id){
    global $_db;
    $stm=$_db->prepare("DELETE FROM users WHERE id=?");
    $stm->execute([$user_id]);
}

function getUserById($user_id){
    global $_db;
    $stm=$_db->prepare("SELECT * from users WHERE id=?");
    $stm->execute([$user_id]);
    return $stm->fetch(PDO::FETCH_ASSOC);
}

function updateUserProfile($user_id, $username, $email, $phone, $profile_image, $password_hashed = null) {
    global $_db;
    if ($password_hashed) {
        $sql = "UPDATE users SET username = ?, email = ?, phone_num = ?, profile_image = ?, password = ? WHERE id = ?";
        $params = [$username, $email, $phone, $profile_image, $password_hashed, $user_id];
    } else {
        $sql = "UPDATE users SET username = ?, email = ?, phone_num = ?, profile_image = ? WHERE id = ?";
        $params = [$username, $email, $phone, $profile_image, $user_id];
    }
    $stm = $_db->prepare($sql);
    return $stm->execute($params);
}

function getUserImageById($user_id){
    global $_db;
    $stm=$_db->prepare("SELECT profile_image FROM users WHERE id =?");
    $stm->execute([$user_id]);
    return $stm->fetch(PDO::FETCH_COLUMN);
}
?>
