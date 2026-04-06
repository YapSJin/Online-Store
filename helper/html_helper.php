<?php

function encode($value) {
    return htmlentities($value);
}

if (!function_exists('html_search')) {
    function html_search($name, $placeholder = 'Search...', $attrs = '') {
        $value = '';
        if (isset($_GET[$name])) {
            $value = (string)$_GET[$name];
        }
        echo '<input type="text" name="' . htmlspecialchars((string)$name) . '" placeholder="' . htmlspecialchars((string)$placeholder) . '" value="' . htmlspecialchars($value) . '" ' . $attrs . '>';
    }
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];

    if (is_string($url) && str_starts_with($url, '/')) {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $appRoot = '';
        $pos = strpos($scriptName, '/public/');
        if ($pos !== false) {
            $appRoot = substr($scriptName, 0, $pos);
        } else {
            $pos = strpos($scriptName, '/admin/');
            if ($pos !== false) {
                $appRoot = substr($scriptName, 0, $pos);
            }
        }

        if ($appRoot === '') {
            $parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
            $knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
            if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
                $appRoot = '/' . $parts[0];
            }
        }

        $knownPrefixes = ['/public/', '/admin/', '/assets/', '/component/', '/config/', '/models/', '/helper/', '/lib/'];
        foreach ($knownPrefixes as $prefix) {
            if (str_starts_with($url, $prefix)) {
                if ($appRoot !== '' && !str_starts_with($url, $appRoot . $prefix)) {
                    $url = $appRoot . $url;
                }
                break;
            }
        }
    }

    header("Location: $url");
    exit();
}

function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

if (!function_exists('getUserPasswordById')) {
    function getUserPasswordById($user_id) {
        global $_db;

        $stmt = $_db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}
