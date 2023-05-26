<?php
// 生成短链接码
function generateShortCode() {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    $length = 5; // 设置短链接码的长度

    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $code;
}

// 获取原始链接
function getOriginalLink($shortCode) {
    $file = 'links.json';

    if (file_exists($file)) {
        $json = file_get_contents($file);
        $links = json_decode($json, true);

        if (array_key_exists($shortCode, $links)) {
            return $links[$shortCode];
        }
    }

    return null;
}

// 创建短链接
function createShortLink($url) {
    $file = 'links.json';

    // 检查链接文件是否存在
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $links = json_decode($json, true);

        // 检查长链接是否已经存在
        if (in_array($url, $links)) {
            // 返回已存在的短链接码
            return array_search($url, $links);
        }
    } else {
        $links = array();
    }

    // 生成新的短链接码
    $shortCode = generateShortCode();

    // 保存新的短链接数据
    $links[$shortCode] = $url;
    file_put_contents($file, json_encode($links));

    return $shortCode;
}

// 处理短链接请求
if (isset($_GET['shortCode'])) {
    $shortCode = $_GET['shortCode'];
    $originalLink = getOriginalLink($shortCode);

    if ($originalLink) {
        // 重定向到原始链接
        header("Location: " . $originalLink);
        exit;
    } else {
        echo "<a href=index.html>" . "短链接不存在</a>";
    }
}

$servername = "https://s.lxyddice.top/";
if (isset($_POST['url'])) {
    $url = $_POST['url'];
    $existingShortLink = createShortLink($url);
    if ($_GET['do'] != "api"){
        // 检查长链接是否已经存在
        if ($existingShortLink) {
            echo "短链接生成成功：<a href=" . $servername . $existingShortLink . ">" . $servername . $existingShortLink . "</a>";
        } else {
            // 创建新的短链接
            $shortLink = createShortLink($url);
            echo "短链接生成成功：<a href=" . $servername . $shortLink . ">" . $servername . $shortLink . "</a>";
        }
    } else {
        // 检查长链接是否已经存在
        if ($existingShortLink) {
            $arr = array('code' => 0, 'shorturl' => "{$servername}{$existingShortLink}", 'longurl' => $url);
            echo json_encode($arr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $shortLink = createShortLink($url);
            $arr = array('code' => 0, 'shorturl' => "{$servername}{$shortLink}", 'longurl' => $url);
            echo json_encode($arr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }
}

?>
