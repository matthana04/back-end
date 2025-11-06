<?php
session_start();

// การตั้งค่าฐานข้อมูล
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_shop');

// เชื่อมต่อฐานข้อมูล
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}

// การตั้งค่าเว็บไซต์
define('SITE_NAME', 'ร้านค้าออนไลน์');
define('SITE_URL', 'http://localhost/online-shop/');
?>

inc/functions.php

<?php
// ฟังก์ชันตรวจสอบการเข้าสู่ระบบ
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ฟังก์ชันตรวจสอบสิทธิ์
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// ฟังก์ชันป้องกัน SQL Injection
function clean($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// ฟังก์ชันแสดงข้อความแจ้งเตือน
function showAlert($message, $type = 'success') {
    $colors = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700'
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    
    return "<div class='border-l-4 p-4 mb-4 {$color}' role='alert'>
                <p>{$message}</p>
            </div>";
}

// ฟังก์ชันจัดรูปแบบราคา
function formatPrice($price) {
    return number_format($price, 2) . ' ฿';
}

// ฟังก์ชันนับจำนวนสินค้าในตะกร้า
function getCartCount($conn, $user_id) {
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// ฟังก์ชันเปลี่ยนเส้นทาง
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// ฟังก์ชันตรวจสอบสิทธิ์และเปลี่ยนเส้นทาง
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . 'login.php');
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirect(SITE_URL . 'index.php');
    }
}
?>

inc/header.php

<?php
if (!isset($conn)) {
    require_once 'config.php';
    require_once 'functions.php';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }
        .bg-maroon { background-color: #800020; }
        .text-maroon { color: #800020; }
        .border-maroon { border-color: #800020; }
        .hover\:bg-maroon:hover { background-color: #800020; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-maroon text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="<?php echo SITE_URL; ?>" class="text-2xl font-bold">
                    🛒 <?php echo SITE_NAME; ?>
                </a>
                
                <div class="flex items-center space-x-6">
                    <a href="<?php echo SITE_URL; ?>" class="hover:text-gray-200">หน้าแรก</a>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>cart.php" class="hover:text-gray-200 relative">
                            🛒 ตะกร้า
                            <?php 
                            $cart_count = getCartCount($conn, $_SESSION['user_id']);
                            if ($cart_count > 0): 
                            ?>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    <?php echo $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <a href="<?php echo SITE_URL; ?>orders.php" class="hover:text-gray-200">คำสั่งซื้อของฉัน</a>
                        
                        <?php if (hasRole('seller')): ?>
                            <a href="<?php echo SITE_URL; ?>seller/" class="hover:text-gray-200">จัดการร้านค้า</a>
                        <?php endif; ?>
                        
                        <?php if (hasRole('admin')): ?>
                            <a href="<?php echo SITE_URL; ?>admin/" class="hover:text-gray-200">ผู้ดูแลระบบ</a>
                        <?php endif; ?>
                        
                        <div class="relative group">
                            <button class="hover:text-gray-200">
                                👤 <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden group-hover:block z-10">
                                <a href="<?php echo SITE_URL; ?>logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">ออกจากระบบ</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>login.php" class="hover:text-gray-200">เข้าสู่ระบบ</a>
                        <a href="<?php echo SITE_URL; ?>register.php" class="bg-white text-maroon px-4 py-2 rounded-lg hover:bg-gray-100">สมัครสมาชิก</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">

inc/footer.php

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">เกี่ยวกับเรา</h3>
                    <p class="text-gray-400">ระบบร้านค้าออนไลน์ที่ครบครันและใช้งานง่าย</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">ลิงก์ด่วน</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="<?php echo SITE_URL; ?>" class="hover:text-white">หน้าแรก</a></li>
                        <li><a href="<?php echo SITE_URL; ?>cart.php" class="hover:text-white">ตะกร้าสินค้า</a></li>
                        <li><a href="<?php echo SITE_URL; ?>orders.php" class="hover:text-white">คำสั่งซื้อ</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">ติดต่อเรา</h3>
                    <p class="text-gray-400">
                        📧 info@shop.com<br>
                        📞 02-123-4567<br>
                        📍 กรุงเทพมหานคร ประเทศไทย
                    </p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. สงวนลิขสิทธิ์.</p>
            </div>
        </div>
    </footer>

    <script>
        // ฟังก์ชันปิด modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // ฟังก์ชันเปิด modal
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        // ปิด modal เมื่อคลิกนอก modal
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-backdrop')) {
                event.target.classList.add('hidden');
            }
        }
    </script>
</body>
</html>