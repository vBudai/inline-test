<?php

use Symfony\Component\Dotenv\Dotenv;

if (file_exists("vendor/autoload.php"))
    require "vendor/autoload.php";
else
    die("Vendor package wasn't found in load_data.php");

// Подключение формы поиска
require "template/search_page.php";