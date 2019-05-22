<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <?php
    if (isset($_COOKIE['user']))
        echo "Selamat berbuka! " . $_COOKIE['user'];
    else
        echo "Done!";
    ?>
</body>
</html>