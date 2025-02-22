<?php
// File ini hanya berisi definisi fungsi, tidak melakukan pengecekan langsung
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($allowed_roles) {
    return in_array($_SESSION['role'], $allowed_roles);
}
?> 