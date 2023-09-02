<?php

use Symfony\Component\Dotenv\Dotenv;

if (file_exists("vendor/autoload.php"))
    require "vendor/autoload.php";
else
    die("Vendor package wasn't found in load_data.php");

// Подключение к БД
$dotenv = new Dotenv();
$conn = null;

if(file_exists(__DIR__ . '/.env'))
    $dotenv->load(__DIR__ . '/.env');
else
    die("Env file wasn't found in load_data.php");

try{
    $conn = new PDO(
        "$_ENV[dbtype]:host=$_ENV[dbhost];
                dbname=$_ENV[dbname]",
        $_ENV['dbuser'],
        $_ENV['dbpassword']);
} catch (PDOException $e){
    echo 'Ошибка подключения к БД: ' . $e->getMessage() . '<br>';
}



// Загрузка постов с API
$link = "https://jsonplaceholder.typicode.com/posts";
$posts = getDataFromApi($link);

// Загрузка постов в БД
$loadedPostsCount = insertToDatabase($conn, 'posts', $posts);


// Загрузка комментариев с API
$link = "https://jsonplaceholder.typicode.com/comments";
$comments = getDataFromApi($link);

// Загрузка комментариев в БД
$commentsCount = count($comments);

$loadedCommentsCount = insertToDatabase($conn, 'comments', $comments);


// Сообщение о загрузке
echo "Загружено $loadedPostsCount постов и $loadedCommentsCount комментариев";


// Получение данных с API
function getDataFromApi(string $link){
    if(!$link)
        return null;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $link);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}


// Добавление данных в БД
function insertToDatabase(PDO $conn, string $table, array $data = []): int {
    if($table == "" || empty($data))
        return 0;

    $i = 0;
    $dataSize = count($data);
    for($i = 0; $i < $dataSize; ++$i){
        $j = 0;
        $coll = '';
        $mask = '';
        foreach ($data[$i] as $key => $value){
            if($j===0){
                $coll = $coll ."$key";
                $mask = $mask . "'" . "$value" . "'";
            }
            else{
                $coll = $coll .", $key";
                $mask = $mask . ", '" . "$value" . "'";
            }
            $j++;
        }

        $sql = "INSERT INTO $table ($coll) VALUES ($mask)";
        $query = $conn->prepare($sql);
        $query->execute();
    }

    return $i;
}

