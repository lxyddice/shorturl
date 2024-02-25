# shorturl
一个简单的短链接生成器，使用json储存短链接记录，支持API

需要在sl.php把servername换成你的

API使用方法为https://xxx.com/sl.php?do=api ，POST方式传入url

防止滥用建议套速率限制的cdn或nginx配置

建议手动设置禁止访问links.json

伪静态配置（nginx）

<code>
location / {
    rewrite ^/([a-zA-Z0-9]+)$ /sl.php?shortCode=$1 last;
  }
</code>

演示站点：[https://s.lxyddice.top/](https://s.lxyddice.top/)

# 我去，我居然更新它了！快看我！

###### 求求有大佬帮我写个前端喵（

## 在/v2里，down下来丢上服务器配置伪静态就能用了（应该

### sl.php是API主文件，请尝试添加密钥验证防止被炸，比如：

<code>
if ($_GET["key"] != "114514") {
    exit();
}
</code>

<code>
location /
{
	 try_files $uri $uri/ /index.php?$args;
}
location ~* ^/.*.(json)$ {
    deny all;
}
</code>
