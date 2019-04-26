<?php

session_start();

$protected_mode = true;
$protected_key = "123456";

$locked = true;

if ((!empty($_SESSION['unlocked']) && $_SESSION['unlocked'] == $protected_key) || $protected_mode == false) {
    $locked = false;
}

define('BASE_FOLDER', __DIR__ . DIRECTORY_SEPARATOR . 'base_folder');

if (!file_exists(BASE_FOLDER)) {
    mkdir(BASE_FOLDER);
}

$root = '';

if ($locked == false) {
    
    if (!empty($_GET['p']) && $_GET['p'] != '/') {
        $root = $_GET['p'];
    }

    define('BASE_FOLDER_CURRENT', BASE_FOLDER . DIRECTORY_SEPARATOR . $root);

    if (!file_exists(BASE_FOLDER_CURRENT) || strpos(BASE_FOLDER_CURRENT, '..') > -1) {
        echo 'Sorry not found!'; exit;
    }

    $err = null;
    $success = null;

    if (!empty($_POST['directory'])) {
        $dir_name = $_POST['directory'];

        if (preg_match("#^[^\\\/?%*:|\"<>\.]+$#", $dir_name) == true) {
            if (!file_exists(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $dir_name)) {
                mkdir(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $dir_name);

                $success = 'Folder dengan nama <strong>' . $dir_name . '</strong> berhasil dibuat.';
            } else {
                $err = 'Nama folder telah digunakan, silahkan gunakan nama folder lainnya!';
            }
        } else {
            $err = 'Nama folder yang kamu masukkan tidak memenuhi syarat! Pastikan tidak mengandung huruf yang tidak diperbolehkan.';
        }
    }

    if (!empty($_GET['act']) && $_GET['act'] == 'lock') {
        unset($_SESSION['unlocked']);

        header("Location: index.php"); exit;
    }

    if (!empty($_FILES['file'])) {
        $target_file = BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_FILES['file']['name'];

        if (file_exists($target_file)) {
            $err = 'Berkas telah ada, silahkan upload berkas lainnya!';
        } else {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                $success = 'Berkas <strong>' . basename($_FILES['file']['name']) . '</strong> berhasil diunggah.';
            } else {

                $err = 'Terdapat kesalahan ketika melakukan unggah berkas!';
            }
        }
    }

    if (!empty($_GET['delete']) && !empty($_GET['type'])) {
        if (file_exists(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_GET['delete'])) {
            switch ($_GET['type']) {
                case 'folder':
                    $remove = @rmdir(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_GET['delete']);
        
                    if ($remove) {
                        $success = 'Folder dengan nama <strong>' . $_GET['delete'] . '</strong> berhasil dihapus.';
                    } else {
                        $err = 'Terjadi kesalahan ketika mencoba menghapus folder. Kemungkinan folder tidak kosong!';
                    }
                break;
                case 'file':
                    unlink(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_GET['delete']);
                    
                    $success = 'Berkas <strong>' . $_GET['delete'] . '</strong> berhasil dihapus.';
                break;
            }
        }
        
    }

    if (!empty($_POST['path']) && !empty($_POST['rename'])) {
        if (file_exists(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_POST['path'])) {
            if (file_exists(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_POST['rename'])) {
                $err = 'Target name tidak tersedia, kemungkinan sudah terpakai!';
            } else {
                $ren = @rename(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_POST['path'], BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_POST['rename']);

                if ($ren) {
                    $success = 'Folder atau berkas berhasil di ubah namanya!';
                } else {
                    $err = 'Terjadi kesalahan ketika mencoba mengubah berkas!';
                }
            }
            
        }
    }

    if (!empty($_GET['download']) && file_exists(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_GET['download']) && is_file(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_GET['download'])) {
        $filepath = BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_GET['download'];

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        flush();
        readfile($filepath);
        exit;
    }

    if (!empty($_GET['duplicate']) && file_exists(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_GET['duplicate'])) {
        $list = explode('.', $_GET['duplicate']);
        $po = array_pop($list);

        $new_name_file = implode('.', $list) . '.'.time().'.' . $po;
        if (copy(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_GET['duplicate'], BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $new_name_file)) {
            $success = 'Berkas berhasil di duplikat!';
        } else {
            $err = 'Terjadi kesalahan ketika mencoba melakukan duplikasi.';
        }
    }

    if (!empty($_POST['new-name'])) {
        $target_path = BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $_POST['new-name'];

        if (!file_exists($target_path)) {
            $fp = fopen($target_path, 'w');
            fwrite($fp, empty($_POST['new-body']) ? '' : $_POST['new-body']);
            fclose($fp);

            $success = 'Berkas dengan nama <strong>'.$_POST['new-name'].'</strong> berhasil dibuat!';
        } else {
            $err = 'Berkas telah tersedia, tidak dapat membuat berkas dengan nama yang sama!';
        }
    }

    $ignore_dir = ['.', '..'];
    $dir_listing = [
        'folder' => [],
        'file' => []
    ];

    if ($handle = opendir(BASE_FOLDER_CURRENT)) {
        while (false !== ($e = readdir($handle))) {
            if (!in_array($e, $ignore_dir)) {
                array_push($dir_listing[is_dir(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $e) ? 'folder' : 'file'], $e);
            }
        }
        closedir($handle);
    }

    sort($dir_listing['folder']);
    sort($dir_listing['file']);

    $current_path = 'index.php?p=' . (!empty($_GET['p']) ? $_GET['p'] : '/');

    $new_file = false;

    if (!empty($_GET['act'])) {
        switch ($_GET['act']) {
            case 'new':
                $new_file = true;
            break;
        }
    }
} else {
    if (!empty($_POST['unlock']) && $_POST['unlock'] == $protected_key) {
        $_SESSION['unlocked'] = $protected_key;

        header("Location: index.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Management File</title>
    <link rel="icon" type="image/x-icon" class="js-site-favicon" href="https://github.githubassets.com/favicon.ico">

    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/4.5.6/css/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.10/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.10/css/skins/skin-black-light.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <style>
    tr.active-row>td>a.btn-primary {
        background-color: #f4f4f4;
        color: #444;
        border-color: #ddd;
    }
    </style>
</head>

<body class="hold-transition skin-black-light layout-top-nav">
    <div class="wrapper">
        <header class="main-header">
            <nav class="navbar navbar-static-top">
                <div class="container">
                    <div class="navbar-header">
                        <a href="index.php" class="navbar-brand" style="border-right: 0px;"><i class="fa fa-folder"></i> <b>File</b> Manager</a>
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>
                </div>
            </nav>
        </header>
        <div class="content-wrapper">
            <div class="container">
                <section class="filemanager-control">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-solid">
                                <?php if ($locked): ?>
                                    <div class="box-body">
                                        <form action="index.php?p=/" method="POST">
                                            <h4><strong>Protected Mode</strong></h4>
                                            <p>Resource yang akan kamu akses berada pada mode terkunci, masukkan kunci keamanan kamu untuk membuka resource</p>
                                            <div class="input-group">                     
                                                <input type="password" name="unlock" class="form-control" placeholder="Masukkan password disini">
                                                <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-primary">Unlock!</button>
                                                </span>
                                            </div>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="box-body">
                                        <?php if ($new_file){ ?>
                                            <a href="<?php echo $current_path; ?>" class="btn btn-primary"><i class="fa fa-arrow-left"></i></a>
                                        <?php } else { ?>
                                            <a href="<?php echo $current_path; ?>&act=new" class="btn btn-primary"><i class="fa fa-file"></i></a>
                                        <?php } ?>
                                    
                                        <form id="form-file" action="<?php echo $current_path; ?>" method="POST" style="display: inline-block;" enctype="multipart/form-data">
                                            <button type="button" class="file btn btn-primary" style="position: relative; overflow: hidden;" <?php echo !$new_file ?: 'disabled'; ?>>
                                                <i class="fa fa-cloud-upload"></i> Unggah
                                                <input style="position: absolute; opacity: 0; right: 0; top: 0; font-size: 50px;" type="file" name="file" <?php echo !$new_file ?: 'disabled'; ?> />
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-primary" id="btn-create-directory" <?php echo !$new_file ?: 'disabled'; ?>><i class="fa fa-folder"></i> Buat Directory</button>
                                        <button type="button" class="btn btn-danger" id="btn-create-cancel" style="display: none;"><i class="fa fa-times"></i></button>

                                        <div class="pull-right">
                                            <a href="<?php echo $current_path; ?>" class="btn btn-primary"><i class="fa fa-refresh"></i></a>

                                            <?php if ($protected_mode == true): ?>
                                                <a href="?act=lock" class="btn btn-danger"><i class="fa fa-sign-out"></i> Lock</a>
                                            <?php endif; ?>
                                        </div>

                                        <form id="form-directory" action="<?php echo $current_path; ?>" method="POST" style="display: none;">
                                            <p class="margin" style="margin-left: 0px;">Masukkan nama directory:</p>
                                            <div class="input-group">
                                                <input type="text" name="directory" class="form-control" placeholder="Masukkan nama directory disini!">
                                                <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-primary">Buat Sekarang!</button>
                                                </span>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
                <?php if ($locked): ?>
                    <!-- Nothing Show -->
                <?php else: ?>
                    <section class="content-header">
                        <?php if ($new_file){ ?>
                            <h1>
                                Dictionary Listing
                                <small>Buat sebuah berkas baru</small>
                            </h1>
                        <?php } else { ?>
                            <h1>
                                Dictionary Listing
                                <small>Version 1.0</small>
                            </h1>
                        <?php } ?>
                    </section>
                    <section class="content">
                        <?php
                        if ($err) {
                            echo '<div class="callout callout-warning"><h4>Kesalahan!</h4><p>'.$err.'</p></div>';
                        }

                        if ($success) {
                            echo '<div class="callout callout-success"><h4>Sukses!</h4><p>'.$success.'</p></div>';
                        }
                        ?>
                        
                        <?php if ($new_file){ ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <form action="<?php echo $current_path; ?>" method="post">
                                        <div class="box box-solid">    
                                            <div class="box-body no-padding">
                                                <input type="text" class="form-control" id="new-name" name="new-name" placeholder="Masukkan nama berkas" style="border-bottom: none;">                           
                                            </div>
                                            <div class="box-body no-padding">
                                                <textarea class="form-control" rows="20" name="new-body" placeholder="Masukkan isi berkas dalam bentuk apa saja" style="border-top: none;"></textarea>                        
                                            </div>                                      
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-file"></i> Buat Berkas</button>
                                        </div> 
                                    </form>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="box box-solid">
                                        <div class="box-body">
                                            <?php
                                            if ($root != '') {
                                                $exp = explode('/', $root);
                                                array_pop($exp);

                                                echo '<a href="?p='.implode('/', $exp).'" class="btn btn-sm btn-primary"><i class="fa fa-arrow-left"></i></a>';
                                            }
                                            ?>
                                            <button type="button" class="btn btn-sm btn-primary" id="act-search"><i class="fa fa-search"></i></button>
                                            <button type="button" class="btn btn-sm btn-primary" id="act-rename" disabled><i class="fa fa-text-height"></i> Rename</button>
                                            <button type="button" class="btn btn-sm btn-danger" id="act-rename-cancel" style="display: none;"><i class="fa fa-times"></i></button>
                                            <a href="#" class="btn btn-sm btn-primary" id="act-duplicate" disabled><i class="fa fa-files-o"></i> Duplicate</a>
                                            <a href="#" class="btn btn-sm btn-primary" id="act-download" disabled><i class="fa fa-download"></i> Download Berkas</a>
                                            <a href="#" class="btn btn-sm btn-primary" id="act-enter" disabled><i class="fa fa-external-link-square"></i> Enter</a>

                                            <form id="form-rename" action="<?php echo $current_path; ?>" method="POST" style="display: none;">
                                                <input type="hidden" name="path" value="">
                                                <p class="margin" style="margin-left: 0px;">Nama baru:</p>
                                                <div class="input-group">
                                                    <input type="text" name="rename" class="form-control" placeholder="Masukkan nama baru disini!">
                                                    <span class="input-group-btn">
                                                        <button type="submit" class="btn btn-primary">Ubah Sekarang!</button>
                                                    </span>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="box box-solid">
                                        <div class="box-body no-padding">
                                            <ol class="breadcrumb" style="margin-bottom: 0px;">
                                                <?php
                                                $root_c = $root;

                                                if ($root_c == '') $root_c .= '/';
                                                $exp = explode('/', $root_c);

                                                $cur = [];

                                                foreach ($exp as $i => $ex) {
                                                    array_push($cur, $ex);
                                                    $el = '<li class="'.($i == count($exp) - 1 ? 'active' : '') .'">';
                                                    if ($i != count($exp) - 1) $el .= '<a href="index.php?p='.implode('/', $cur).'">';
                                                    if ($i == 0) $el .= '<i class="fa fa-folder-open-o"></i> ';
                                                    $el .= $ex;
                                                    if ($i != count($exp) - 1) $el .= '</a>';
                                                    $el .= '</li>';

                                                    echo $el;
                                                }
                                                ?>
                                            </ol>
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>Name</th>
                                                        <th>Date Modified</th>
                                                        <th>Type</th>
                                                        <th>Permissions</th>
                                                        <th>Size</th>
                                                        <th class="col-md-1">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="search-bar" style="display: none;">
                                                        <td colspan="7" style="">
                                                            <input class="form-control" id="search-bar-data" placeholder="Cari berdasarkan nama..." style="border: none;">
                                                        </td>
                                                    </tr>
                                                    <?php

                                                    function get_type($f, $d_type) {
                                                        $type_arr = [
                                                            'php' => 'PHP: Hypertext Preprocessor',
                                                            'css' => 'Cascading Style Sheet',
                                                            'xls' => 'Excel 2007 Document',
                                                            'xlsx' => 'Excel Document',
                                                            'pptx' => 'PowerPoint Document',
                                                            'txt' => 'Text Document',
                                                            'png' => 'Portable Network Graphics'
                                                        ];

                                                        switch ($d_type) {
                                                            case 'folder':
                                                                return 'File Folder';
                                                            case 'file':
                                                                $f_ex = explode('.', $f);

                                                                return !empty($type_arr[$f_ex[count($f_ex) - 1]]) ? $type_arr[$f_ex[count($f_ex) - 1]] : 'Unknown File Type';
                                                        }
                                                    }

                                                    function human_filesize($bytes, $decimals = 2) {
                                                        $sz = 'BKMGTP';
                                                        $factor = floor((strlen($bytes) - 1) / 3);
                                                        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
                                                    }

                                                    function get_size($f, $d_type) {
                                                        if ($d_type == 'folder') return;

                                                        return human_filesize(filesize(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $f));
                                                    }

                                                    function dir_type_get($dir, $f) {
                                                        $ar_data = [
                                                            'file-pdf-o' => ['pdf'],
                                                            'file-excel-o' => ['xls', 'xlsx'],
                                                            'file-picture-o' => ['png', 'jpg', 'jpeg', 'gif'],
                                                            'file-code-o' => ['js', 'php', 'html', 'css'],
                                                            'file-powerpoint-o' => ['ppt', 'pptx'],
                                                            'file-text-o' => ['txt']
                                                        ];

                                                        $f_ex = explode('.', $f);
                                                        $ex = $f_ex[count($f_ex) - 1];

                                                        $ch = null;
                                                        foreach ($ar_data as $k => $v) {
                                                            if (in_array($ex, $v)) {
                                                                $ch = $k; break;
                                                            }
                                                        }

                                                        if ($ch == null || $dir == 'folder') {
                                                            return $dir == 'folder' ? 'folder' : 'file-o';
                                                        }

                                                        return $ch;
                                                    }

                                                    $row = 0;
                                                    foreach($dir_listing as $dir_type => $dir_list) {
                                                        foreach($dir_list as $dir) {
                                                            $row++;

                                                            echo '<tr data-type="'.$dir_type.'" data-name="'.$dir.'"><td><i class="fa fa-'.dir_type_get($dir_type, $dir).'"></i></td><td>'.$dir.'</td><td>'.date("d F Y H:i:s", filemtime(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $dir)).'</td><td>'.get_type($dir, $dir_type).'</td><td>'.substr(sprintf('%o', fileperms(BASE_FOLDER_CURRENT . DIRECTORY_SEPARATOR . $dir)), -4).'</td><td>'.get_size($dir, $dir_type).'</td><td><a href="'.$current_path.'&delete='.urlencode($dir).'&type='.urlencode($dir_type).'" class="btn btn-primary btn-xs"><i class="fa fa-trash"></i> Delete</a></td></tr>';
                                                        }
                                                    }

                                                    echo '<tr class="not-found" style="display: none;"><td colspan="5">Tidak ada berkas atau folder yang dapat ditampilkan.</td></tr>';

                                                    if ($row === 0) {
                                                        echo '<tr><td colspan="5">Tidak ada berkas atau folder yang dapat ditampilkan.</td></tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="box box-solid">
                                        <div class="box-body">
                                        <p><strong>Disk Space Left</strong></p>
                                        <?php
                                        $rootp = substr(__FILE__, 0, 1);
                                        if ($rootp != '/') {
                                            $rootp .= ':';
                                        }

                                        $free = disk_free_space($rootp);
                                        $used = disk_total_space($rootp);

                                        $rt =  ceil(100 * $free / ($free + $used));
                                        ?>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="<?php echo $rt; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $rt; ?>%"></div>                             
                                        </div>
                                        <p class="help-block"><?php echo human_filesize($free); ?> / <?php echo human_filesize($used + $free); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </section>
                <?php endif; ?>
            </div>
        </div>
       
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.10/js/adminlte.min.js"></script>
    <script>
        $(document).ready(function() {

            let root = '<?php echo $root; ?>';

            let ren = $('#act-rename');
            let dup = $('#act-duplicate');
            let infor = $('#act-download');
            let enter = $('#act-enter');

            $('input[name="file"]').on('change', function() {
                $('#form-file').submit();
            })
            
            ren.on('click', function() {
                $('#form-rename').slideDown();

                ren.attr('disabled', true);
                $("#act-rename-cancel").show();
            })

            $("#act-rename-cancel").on('click', function() {
                $('#form-rename').slideUp();

                ren.attr('disabled', false);
                $("#act-rename-cancel").hide();
            })

            $('#btn-create-directory').on('click', function() {
                $('#form-directory').slideDown();

                $("#btn-create-directory").attr('disabled', true);
                $("#btn-create-cancel").show();
            })

            $("#btn-create-cancel").on('click', function() {
                $('#form-directory').slideUp();

                $("#btn-create-directory").attr('disabled', false);
                $("#btn-create-cancel").hide();
            })
            
            $('#act-search').on('click', function() {
                $('.search-bar').toggle();
            })

            $("#search-bar-data").on("keyup", function() {
                var search = $(this).val();

                $("tbody > tr:not('.search-bar')").hide();
                var len = $('table tbody tr:not(.search-bar):not(.not-found) td:nth-child(2):contains("'+search+'")').length;
                if (len > 0) {
                    $('table tbody tr:not(.search-bar):not(.not-found) td:contains("'+search+'")').each(function(){
                        $(this).closest('tr').show();
                    });
                } else {
                    $('tr.not-found').show();
                }
            })
            
            $("tbody > tr:not('.search-bar')").on("click", function() {
                let el = $(".active-row");

                $("#act-rename-cancel").click();

                el.removeClass('active-row bg-light-blue-active');

                if (!$(this).is(el)) {
                    $(this).addClass("active-row bg-light-blue-active");
                    
                    $('input[name="path"]').val($(this).data('name'));
                    $('input[name="rename"]').val($(this).data('name'));

                    if ($(this).data('type') == 'folder') {
                        ren.attr('disabled', false);
                        enter.attr('disabled', false);
                        dup.attr('disabled', true);
                        infor.attr('disabled', true);

                        enter.attr('href', '?p=' + root + '/' + $(this).data('name'));
                    } else {
                        ren.attr('disabled', false);
                        enter.attr('disabled', true);
                        dup.attr('disabled', false);
                        infor.attr('disabled', false);

                        dup.attr('href', '?p=' + root + '&duplicate=' + $(this).data('name'));
                        infor.attr('href', '?p=' + root + '&download=' + $(this).data('name'));
                    }
                } else {
                    $('input[name="path"]').val('');
                    $('input[name="rename"]').val('');

                    enter.attr('disabled', true);
                    ren.attr('disabled', true);
                    dup.attr('disabled', true);
                    infor.attr('disabled', true);
                }
            });
        });
    </script>
</body>

</html>