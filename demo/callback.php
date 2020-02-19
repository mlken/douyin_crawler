<?php

//这里一个使用 swoole 异步处理回调的演示过程
//由于每次消息都需要进行除重和更新的处理，比较耗时
//回调请求默认为10秒超时，超过10秒将视为失败
//所以要求必须先将消息存起来，再进行异步处理，提高回调接口响应速度

if (PHP_SAPI !== 'cli') {
    exit("必须命令行启动本服务");
}
if (version_compare(PHP_VERSION, '7.1', '<')) {
    exit("需要PHP 7.1 及以上版本");
}
if (!defined('SWOOLE_VERSION')) {
    exit("必须安装 swoole 4.4+ 扩展，pecl install swoole");
}
if (version_compare(SWOOLE_VERSION, '4.4.12', '<')) {
    exit("必须安装 Swoole v4.4.12 及以上版本");
}
if (!class_exists('\\Swoole\\Server', false)) {
    exit("必须开启 swoole 的命名空间模式, 修改或者添加配置: swoole.use_namespace = true");
}

$http = new Swoole\Http\Server("0.0.0.0", 9501);

$http->set([
    'reactor_num'   => 2,
    'worker_num'    => 4,
    'backlog'       => 128,
    'max_request'   => 50,
    'dispatch_mode' => 1,
    'daemonize'     => 0,
    'pid_file'      => __DIR__ . '/yund.pid',
    'log_file'      => __DIR__ . '/yund.log',
]);

$http->on('request', function ($request, $response) {
    if ($request->server['request_method'] == 'OPTIONS') {
        $response->status(200);
        $response->end();
        return;
    };

    $data = $request->rawContent();

    if (empty($data) || !$json = json_decode($data, true)) {
        $msg = json_encode(["code" => 200, "msg" => "no data"]);
        $response->end($msg);

        return;
    }

    if (!isset($json["type"])) {
        $msg = json_encode(["code" => 200, "msg" => "bad data type"]);
        $response->end($msg);

        return;
    }

    //这里添加自己的处理逻辑
    file_put_contents(microtime(true) . ".json", var_export($json, true));

    //最优处理流程是先写入队列，再进行异步消费
    //redis()->lpush("callback", $json);

    printf("[%s] %s : %s\n", date("Y-m-d H:i:s"), $json["type"], $json["task"] ?? "");

    $msg = json_encode(["code" => 200, "msg" => "ok"]);
    $response->end($msg);

});

$http->start();
