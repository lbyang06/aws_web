<?php
// -----------------------------
// MySQL Session Handler
// -----------------------------
$mysqli = new mysqli(
    'web-db.c6k7dftmrrpw.us-east-1.rds.amazonaws.com', // RDS Endpoint
    'admin',                                           // RDS User
    'your-password',                                   // RDS Password
    'php_session_db'                                   // Session DB
);

if ($mysqli->connect_error) {
    die("Session DB connection failed: " . $mysqli->connect_error);
}

class MySQLSessionHandler implements SessionHandlerInterface {
    private $db;

    public function __construct(mysqli $mysqli) {
        $this->db = $mysqli;
    }

    public function open(string $savePath, string $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string {
        $stmt = $this->db->prepare("SELECT data FROM sessions WHERE id=?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $stmt->bind_result($data);
        $stmt->fetch();
        $stmt->close();
        return $data ?? '';
    }

    public function write(string $id, string $data): bool {
        $stmt = $this->db->prepare(
            "REPLACE INTO sessions (id, data, last_access) VALUES (?, ?, NOW())"
        );
        $stmt->bind_param('ss', $id, $data);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    public function destroy(string $id): bool {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id=?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    public function gc(int $maxlifetime): int {
        $stmt = $this->db->prepare(
            "DELETE FROM sessions WHERE last_access < NOW() - INTERVAL ? SECOND"
        );
        $stmt->bind_param('i', $maxlifetime);
        $stmt->execute();
        $stmt->close();
        return $maxlifetime;
    }
}


// 设置自定义 Session Handler
$handler = new MySQLSessionHandler($mysqli);
session_set_save_handler($handler, true);

// 开始 Session
session_start();

// ============================================================================
// HTTP Method Helpers
// ============================================================================
function is_get()  { return $_SERVER['REQUEST_METHOD'] === 'GET'; }
function is_post() { return $_SERVER['REQUEST_METHOD'] === 'POST'; }

// ============================================================================
// Parameter Helpers (Safe GET/POST/REQUEST)
// ============================================================================
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}
function req($key, $default = null) {
    return $_REQUEST[$key] ?? $default;
}

// ============================================================================
// Redirect & Flash Messages
// ============================================================================
function redirect($url = null) {
    $url = $url ?? $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit;
}

// Flash message (better than temp)
function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION["flash_$key"] = $message;
    } else {
        $message = $_SESSION["flash_$key"] ?? null;
        unset($_SESSION["flash_$key"]);
        return $message;
    }
}

// Backward compatibility: keep your old temp() function
function temp($key, $value = null) {
    return flash($key, $value);
}

// ============================================================================
// Validation Helpers
// ============================================================================
function is_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function is_money($value) {
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value) === 1;
}

// ============================================================================
// HTML Output Helpers (Safe)
// ============================================================================
function encode($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function html_hidden($key, $value = '', $attr = '') {
    echo '<input type="hidden" name="' . $key . '" value="' . encode($value) . '" ' . $attr . '>';
}

function html_name($key, $attr = '') {
    echo '<input type="text" placeholder="Enter name" id="' . $key . '" name="' . $key . '" value="' . encode($GLOBALS[$key] ?? '') . '" ' . $attr . '>';
}

function html_email($key, $attr = '') {
    echo '<input type="email" placeholder="Email address" id="' . $key . '" name="' . $key . '" value="' . encode($GLOBALS[$key] ?? '') . '" ' . $attr . '>';
}

function html_pass($key, $attr = '') {
    echo '<input type="password" placeholder="Password" id="' . $key . '" name="' . $key . '" value="' . encode($GLOBALS[$key] ?? '') . '" ' . $attr . '>';
}

function html_number($key, $min = '', $max = '', $step = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type=\"number\" id=\"$key\" name=\"$key\" value=\"$value\" min=\"$min\" max=\"$max\" step=\"$step\" $attr>";
}

function html_radios($key, $items, $br = false) {
    $current = $GLOBALS[$key] ?? '';
    echo '<div class="radio-group">';
    foreach ($items as $value => $text) {
        $checked = ($value == $current) ? 'checked' : '';
        $id = $key . '_' . $value;
        echo "<label><input type=\"radio\" id=\"$id\" name=\"$key\" value=\"" . encode($value) . "\" $checked> $text</label>";
        if ($br) echo '<br>';
    }
    echo '</div>';
}

function html_file($key, $accept = '', $attr = '') {
    echo "<input type=\"file\" id=\"$key\" name=\"$key\" accept=\"$accept\" $attr>";
}

// ============================================================================
// File Upload Helper (Photo)
// ============================================================================
function save_photo($file_obj, $folder = 'product_photo', $width = 300, $height = 300) {
    if (!$file_obj || $file_obj->error !== 0) return null;

    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    $ext = strtolower(pathinfo($file_obj->name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed)) return null;

    $photo = uniqid('photo_') . '.' . $ext;
    $target = "$folder/$photo";

    require_once 'lib/SimpleImage.php';
    try {
        $img = new SimpleImage();
        $img->fromFile($file_obj->tmp_name)
            ->thumbnail($width, $height, 'center')
            ->toFile($target, 'image/jpeg', 85);
        return $photo;
    } catch (Exception $e) {
        error_log("Image save failed: " . $e->getMessage());
        return null;
    }
}

function get_file($key) {
    $f = $_FILES[$key] ?? null;
    return ($f && $f['error'] === 0) ? (object)$f : null;
}

// ============================================================================
// Error Handling
// ============================================================================
$_err = [];
function err($key) {
    global $_err;
    if (!empty($_err[$key])) {
        echo "<span class='err'>" . encode($_err[$key]) . "</span>";
    }
}

// ============================================================================
// Authentication
// ============================================================================
$_user = $_SESSION['user'] ?? null;

function login($user, $url = '/') {
    $_SESSION['user'] = $user;
    redirect($url);
}

function logout($url = '/') {
    unset($_SESSION['user']);
    redirect($url);
}

function auth(...$roles) {
    global $_user;
    if (!$_user || ($roles && !in_array($_user->role ?? '', $roles))) {
        redirect('/login.php');
    }
}

// ============================================================================
// Shopping Cart Helpers
// ============================================================================
function get_cart() {
    return $_SESSION['cart'] ?? [];
}

function set_cart($cart = []) {
    $_SESSION['cart'] = $cart;
}

function update_cart($id, $unit = 1) {
    $cart = get_cart();
    $unit = (int)$unit;

    if ($unit >= 1 && is_exists($id, 'product', 'id')) {
        $cart[$id] = $unit;
    } else {
        unset($cart[$id]);
    }
    ksort($cart);
    set_cart($cart);
}

// ============================================================================
// Database Connection (PDO)
// ============================================================================


// Global PDO object
$_db = new PDO(
    "mysql:host=web-db.c6k7dftmrrpw.us-east-1.rds.amazonaws.com;dbname=chaagee;charset=utf8mb4",
    "admin",
    "MyPassword1212",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ]
);

// Is unique?
function is_unique($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}
