<?php
session_start();

unset($_SESSION['authenticated']);

header('Location: login.php');