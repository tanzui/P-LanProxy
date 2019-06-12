<?php
    Workerman\Protocols\Http::sessionStart();
    $_SESSION['webox_username'] = null;
    Workerman\Protocols\Http::header("location:login.php");
