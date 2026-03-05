<?php
require_once __DIR__ . '/auth.php';

// Always allow logout, even if no user is logged in.
logout_user();

// After logout, send the user back to the home page.
header('Location: index.php');
exit;

