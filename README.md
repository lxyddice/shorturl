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
