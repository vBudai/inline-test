<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Поиск</title>

    <link href="../style/reset.css" rel="stylesheet" type="text/css">
    <link href="../style/index.css" rel="stylesheet" type="text/css">
</head>
<body>

<form action="/" method="get">
    <textarea id="input-body" name="body" placeholder="Введите запрос"></textarea>
    <input id="input-submit" type="submit" value="НАЙТИ">
</form>

<div class="search">
    <div class="search_result">

        <?php

        use Symfony\Component\Dotenv\Dotenv;

        if(isset($_GET['body']) && strlen($_GET['body'])){
            $searchedData = $_GET['body'];

            // Подключение к БД
            $dotenv = new Dotenv();
            $conn = null;

            if(file_exists(__DIR__ . './../.env'))
                $dotenv->load(__DIR__ . './../.env');
            else
                die("Env file wasn't found in search_page.php");

            try{
                $conn = new PDO(
                    "$_ENV[dbtype]:host=$_ENV[dbhost];
                    dbname=$_ENV[dbname]",
                    $_ENV['dbuser'],
                    $_ENV['dbpassword']);
            } catch (PDOException $e){
                echo 'Ошибка подключения к БД: ' . $e->getMessage() . '<br>';
            }

            /** Я решил объединить все комментарии по их постам, то есть в массив вида:
             * 0 => [
             *      ['id'] => "id поста",
             *      ['title'] => "",
             *      ['comments'] => [] (тут body всех комментариев)
             * ]
             * 1 => ...
             */

            $posts = [];
            $comments = searchInDatabase($conn, 'comments', 'body', $searchedData);
            if(is_array($comments)){
                $foundPosts = []; // Массив для хранения индексов считанных постов

                $commentsSize = count($comments);
                for($i = 0; $i < $commentsSize; $i++) {
                    // Был ли запрос в БД на пост с таким ID
                    $postOrder = array_search($comments[$i]['postId'], $foundPosts);

                    // Если пост с этим id не был считан, то он записывается в общий массив posts, иначе комментарий записывается в posts в ячейку комментариев
                    if($postOrder === false){
                        $post = select($conn, 'posts', ['id' => $comments[$i]['postId']]);
                        $posts[] = [
                            'id' => $post[0]['id'],
                            'title' => $post[0]['title'],
                            'comments' => [
                                    $comments[$i]['body']
                            ],
                        ];
                        $foundPosts[] = '' . $comments[$i]['postId'];
                    }
                    else
                        $posts[$postOrder]['comments'][] = $comments[$i]['body'];
                }

                echo 'Вывод: <br>';
                echo '<pre>';
                echo json_encode($posts, JSON_PRETTY_PRINT);
                echo '</pre>';
            }

        }
        ?>

    </div>
</div>

<script src="../script/index.js"></script>
</body>
</html>


<?php

// Поиск в БД
function searchInDatabase(PDO $conn, string $table, string $searchedField, string $searchedData): bool|array {
    $sql = "SELECT * FROM $table WHERE $searchedField LIKE '%$searchedData%'";
    $query = $conn->prepare($sql);
    $query->execute();

    return $query->fetchAll();
}

function select($conn, $table, $params){

    $sql = "SELECT * from $table";

    if(!empty($params)){
        $i = 0;
        foreach ($params as $key => $value){
            if(!is_numeric($value))
                $value = "'" . $value . "'";
            if($i === 0)
                $sql = $sql . " WHERE $key = $value";
            else
                $sql = $sql . " AND $key = $value";

            $i++;
        }
    }

    $query = $conn->prepare($sql);
    $query->execute();

    return $query->fetchAll();
}