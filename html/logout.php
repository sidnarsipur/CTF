<?php
session_name("HHN");
session_start();
session_unset();
session_destroy();
header("Location: ./");
?>
