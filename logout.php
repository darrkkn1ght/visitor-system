<?php
// logout.php
require_once 'security_headers.php';
session_start();
session_destroy();
header('Location: index.php');
exit;
