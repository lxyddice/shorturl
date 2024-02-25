<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $data = json_decode(file_get_contents('php://input'), true);
    $url = $data['url'] ?? $_POST['url'] ?? '';
    if (empty($url)) {
        echo json_encode([
            'code' => 400,
            'msg' => '请输入链接'
        ]);
        exit;
    }
    if (!preg_match('/^http(s)?:\/\/.+$/', $url)) {
        echo json_encode([
            'code' => 400,
            'msg' => '请输入正确的链接'
        ]);
        exit;
    }
    
    $shortCode = md5($url);
    $shortCode = substr($shortCode, 0, 6);

    $serverUrl = $_SERVER['HTTP_HOST'];
    $serverHttp = "https://";

    $logData = [
        'url' => $url,
        'ip' => getIp(),
        'creatTime' => time(),
        "userAgent" => $_SERVER["HTTP_USER_AGENT"],
        "referer" => $_SERVER["HTTP_REFERER"],
        "acceptLanguage" => $_SERVER["HTTP_ACCEPT_LANGUAGE"],
        "nick" => $_SESSION['s_nick'] ?? '',
        "avatar" => $_SESSION['s_avatar'] ?? '',
        "mobile" => $_SESSION['s_mobile'] ?? '',
        "unionId" => $_SESSION['s_dingId'] ?? ''
    ];

    if (file_exists("links/{$shortCode}.json")) {
        copy("links/{$shortCode}.json", "links/history/{$shortCode}_".time().".json");
        unlink("links/{$shortCode}.json");
    }

    file_put_contents("links/{$shortCode}.json", json_encode($logData));

    echo json_encode([
        'code' => 200,
        'msg' => '生成成功',
        'shortCode' => $serverHttp.$serverUrl."/".$shortCode
    ]);
}

function getIp() {
    if (isset($_SERVER['cf-connecting-ip'])) {
        $ip = $_SERVER['cf-connecting-ip'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
