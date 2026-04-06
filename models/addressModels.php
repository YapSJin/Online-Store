<?php
include '../config/db.php';
include '../helper/dbHelper.php';

function getAllUserAddressesById($user_id) {
    global $_db;
    $stm = $_db->prepare("SELECT * FROM address WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $stm->execute([$user_id]);
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}

function getUserAddressesById($id) {
    global $_db;
    $stm = $_db->prepare("SELECT * FROM address WHERE id = ?");
    $stm->execute([$id]);
    return $stm->fetch(PDO::FETCH_ASSOC);
}

function addAddress($user_id, $full_name, $address_line, $city, $postcode, $phone) {
    global $_db;
    $table='address';
    $field='user_id';
    
    if(!is_exists($user_id,$table,$field)){
        $is_default = 1 ;
    }else{
        $is_default = 0 ;
    }

    $stm = $_db->prepare("INSERT INTO address (user_id, full_name, address_line, city, postcode, phone, is_default) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stm->execute([$user_id, $full_name, $address_line, $city, $postcode, $phone, $is_default]);
}

function updateAddress($id, $full_name, $address_line, $city, $postcode, $phone) {
    global $_db;
    $stm = $_db->prepare("UPDATE address SET full_name = ?, address_line = ?, city = ?, postcode = ?, phone = ? WHERE id = ?");
    $stm->execute([$full_name, $address_line, $city, $postcode, $phone, $id]);
}

function deleteAddress($id) {
    global $_db;
    $stm = $_db->prepare("DELETE FROM address WHERE id = ?");
    $stm->execute([$id]);
}

function setDefaultAddress($user_id, $id) {
    global $_db;
    
    $stm=$_db->prepare("UPDATE address SET is_default = 0 WHERE user_id = ?");
    $stm->execute([$user_id]);

    $stm=$_db->prepare("UPDATE address SET is_default = 1 WHERE id = ?");
    $stm->execute([$id]);
}

function getDefaultAddress($user_id){
    global $_db;

    $stm = $_db->prepare("SELECT * FROM address WHERE user_id = ? AND is_default = 1");
    $stm->execute([$user_id]);
    return $stm->fetch(PDO::FETCH_ASSOC);
}

function getAddressById($id) {
    return getUserAddressesById($id);
}


?>
