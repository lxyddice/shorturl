<?php
$url = $_SERVER['REQUEST_URI'];
session_start();
if (strpos($url, '/index.php') !== false) {
    $url = str_replace('/index.php', '', $url);
}
$url = str_replace('/', '', $url);
if (preg_match('/^[0-9a-zA-Z]{6}$/', $url)) {
    if (file_exists("links/{$url}.json")) {
        $f = json_decode(file_get_contents("links/{$url}.json"), true);
        header("Location: {$f['url']}");
    } else {
        echo 'shortLink Not Found';
    }
}

if (isset($_POST['url'])) {
    $url = $_POST['url'];
    $f = json_decode(file_get_contents('links.json'), true);
    $key = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
    $f[$key] = $url;
    file_put_contents('links.json', json_encode($f));
    echo 'Your link: ' . $_SERVER['HTTP_HOST'] . '/' . $key;
}
/*
if (isset($_GET['login'])) {
    session_start();
    $randId = md5(uniqid());
    $_SESSION['randId'] = $randId;
    header("Location: https://api.lxyddice.top/dingbot/php/?client_id=&state={$randId}&redirect_uri=".urlencode("https://".$_SERVER['HTTP_HOST']."/"));
}
*/
/*
if (isset($_GET['DingraiaPHPState']) && isset($_GET['state'])) {
    $t = time();
    $sign = hash('sha256', $_GET['DingraiaPHPState'].$_GET['state'].$t."key");
    $url = "https://api.lxyddice.top/dingbot/php/api/get.php?type=oauth2Get&DingraiaPHPState={$_GET['DingraiaPHPState']}&state={$_GET['state']}&timeStamp={$t}&sign={$sign}";
    $res = requests("GET",$url)['body'];
    $res = json_decode($res, true);
    if ($res['success'] == true && isset($res['data']['mobile'])) {
        $data = $res['data'];
        setcookie("dingId", $data['unionId'], time()+86400);
        $_SESSION['s_dingId'] = $data['unionId'];
        $_SESSION['s_mobile'] = $data['mobile'];
        $_SESSION['s_nick'] = $data['nick'];
        $_SESSION['s_avatar'] = $data['avatar'];
        $_SESSION['s_logoutTime'] = time() + 86400;
        header("Location: /?loginOk");
    } else {
        DingraiaPHPResponseExit(400, "登录失败");
    }
}

if ($_SESSION['s_logoutTime'] < time()) {
    setcookie("dingId", "", time()-3600);
    session_destroy();
}

if (!isset($_SESSION['s_dingId'])) {
    echo "<h2><a href='/?login'>使用此服务需要您使用钉钉登录...</a></2>";
    exit();
}

if (isset($_GET['logout'])) {
    setcookie("dingId", "", time()-3600);
    session_destroy();
    header("Location: /");
}

if (isset($_GET['loginOk'])) {
    if (!isset($_SESSION['s_dingId'])) {
        DingraiaPHPResponseExit(403, "Forbidden", "登录失败，请重试<a href='/?login'>登录</a>");
    }
    echo "登录成功，欢迎您，{$_SESSION['s_nick']}";
}

function requests($method, $url, $data = null, $header = [], $timeout = 20) {
    $ch = curl_init();
    array_merge($header,["Host"=>""]);

    if (strtoupper($method) == "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($data)) {
            if ($header["Content-Type"] == 'application/json') {
                $data = json_encode($data);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }  elseif (strtoupper($method) === "PUT") {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        // 设置PUT数据
        if ($data) {
            if ($header["Content-Type"] == 'application/json') {
                $data = json_encode($data);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

    if ($header != null) {
        $header_arr = array();
        foreach ($header as $key => $value) {
            $header_arr[] = $key . ': ' . $value;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
    }

    try {
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        curl_close($ch);

        return array(
            'status_code' => $status_code,
            'body' => $response
        );
    } catch (Exception $e) {
        curl_close($ch);
        return array(
            'status_code' => 500,
            'body' => $e->getMessage()
        );
    }
}
function DingraiaPHPResponseExit($errCode, $message = "Unkown Error", $m = null,$stop = true, $json = false) {
    if ($m == null)  {
        if ($errCode == 403) {
            $m = "Forbidden";
        } elseif ($errCode == 500) {
            $m = "Internal Srver Error";
        } elseif ($errCode == 400) {
            $m = "Bad Request";
        } elseif ($errCode == 405) {
            $m = "Method Not Allowed";
        }
    }
    http_response_code($errCode);
    if ($_GET['format'] == "json" || $json) {
        header('Content-Type:application/json; charset=utf-8');
        $exitMes = json_encode(["success"=>false, "code"=>$errCode,"message"=>$message],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        if ($stop) {
            exit($exitMes);
        } else {
            echo $exitMes;
        }
    } else {
        if ($stop == true) {
            exit("<h1><html lang=en><title>lxyの短链接服务-{$errCode} {$m}</title><h1>{$errCode} {$m}</h1><p>{$message}</p><small>lxyの短链接服务</small></h1>");
        } else {
            echo("<h1><html lang=en><title>lxyの短链接服务-{$errCode} {$m}</title><h1>{$errCode} {$m}</h1><p>{$message}</p><small>lxyの短链接服务</small></h1>");
        }
    }
}
*/
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="短链接,短网址,短网址生成,短网址服务,短网址转换,短网址生成器,短网址生成网站">
    <script src="https://cdn.bootcdn.net/ajax/libs/vue/2.7.16/vue.min.js"></script>
    <title>lxyの短链接服务</title>
    <style>
        .container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
        }
        .form-control {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            box-sizing: border-box;
        }
        .okUrl {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .okUrl input {
            width: 80%;
        }
        .okUrl button {
            width: 18%;
            padding: 10px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
<div class="container" id="app">
    <h1>lxyの短链接服务</h1>
    <div class="container">
        <input type="text" name="url" placeholder="输入链接" v-model="url" class="form-control">
        <input type="button" value="生成" @click="getShortUrl" id="createBtn" class="form-control">
    </div>
    <div class="okUrl">
        <input type="text" name="okUrl" placeholder="生成的短链接" disabled class="form-control" v-model="okUrl">
        <button @click="copyUrl">复制</button>
    </div>
</div>

<script>
    new Vue({
        el: '#app',
        data: {
            url: '',
            okUrl: ''
        },
        methods: {
            getShortUrl() {
                let url = this.url;
                if (url === '') {
                    alert('请输入链接');
                    return;
                }
                if (!url.startsWith('http://') && !url.startsWith('https://')) {
                    alert('请输入正确的链接');
                    return;
                }
                this.okUrl = '';
                document.querySelector('#createBtn').value = '生成中...';
                // post请求，解决跨域问题
                fetch('sl.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        url: url
                    })
                }).then(res => res.json()).then(res => {
                    console.log(res);
                    if (res.code === 200) {
                        this.okUrl = res.shortCode;
                    } else {
                        alert(res.msg);
                    }
                    document.querySelector('#createBtn').value = '生成';
                });
            },
            copyUrl() {
                let input = document.querySelector('input[name="okUrl"]');
                input.select();
                navigator.clipboard.writeText(input.value)
                    .then(() => {
                        alert('复制成功');
                    })
                    .catch(err => {
                        console.error('复制失败:', err);
                        alert('复制失败，请手动复制链接');
                    });
            }
        }
    });
</script>

    </script>
</body>
</html>
