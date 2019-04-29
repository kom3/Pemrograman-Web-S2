<?php
// echo readfile('data.json'); exit;

// Reading a file
$myFile = fopen("directionary.txt", "r") or die("Unable to open file!");
// Reading entire file
echo fread($myFile, filesize("directionary.txt"));
// Reading 1 line from file
echo fgets($myFile);
// Looping sampai akhir file
while (!feof($myFile)) {
    // Mengambil satu baris
    echo fgets($myFile) . "<br />";
    // Mengambil satu karakter
    echo fgetc($myFile) . "<br />";
}

fclose($myFile); 

// Creating a file

$myFile = fopen("testfile.txt", "a") or die("Unable to open file!");

// Writing to a file
$write = "Berubah\n";
fwrite($myFile, $write);

// Closing a pointer
fclose($myFile);
exit;
?>
<html>
<body>
    <h1>Pemrograman Web B</h1>
    <p>Informatika 11</p>

    <?php require 'footer.php'; ?>

    <p>Saya berada di kelas <?php echo $kelas[1]; ?></p>

    <?php
    echo readfile('directionary.txt');
    ?>
</body>
</html>