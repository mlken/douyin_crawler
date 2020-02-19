<?php

//仅支持 Linux 和 Mac 系统
//如果请求量较大，建议使用 swoole 的多线程或者协程模式提交任务

$post = [
    "token"  => "36ea7692e261cc32f593b2cd7eb7dc6c",
    "type"   => "crawler_search_user",
    "search" => "面膜",
    "num"    => 20,
    "task"   => ["user_token"=>"deb783c7418428601e63f5df23ba4519"]
];

$api = "https://service.yundou.me/";

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "不支持 Windows 服务器\n";
    exit;
}

$json = json_encode($post);
$curl = `curl -s -m 10 -XPOST -d '$json' '$api'`;

$data = json_decode($curl, true);
if (empty($data) || !isset($data["code"])) {
    echo "请求超时\n";
    exit;
}

if ($data["code"] != 200) {
    echo "发生错误:" . ($data["code"] ?? "未知") . PHP_EOL;
    exit;
} else {
    echo "成功\n";
}
