<?php

$arr = [
      'a',
      'b',
      'c',
      'd',
      'f',
      'g',
];
$z = 0;

$proxy_list = [
    [
        'socks5_host' => '47.90.74.82',
        'socks5_port' => '29588'
    ],
    [
        'socks5_host' => '72.11.148.222',
        'socks5_port' => '56533'
    ],
    [
        'socks5_host' => '5.56.61.183',
        'socks5_port' => '51295'
    ],
    [
        'socks5_host' => '216.144.228.130',
        'socks5_port' => '15378'
    ],
    [
        'socks5_host' => '104.236.26.27',
        'socks5_port' => '38801'
    ]
];
$run_main = new Swoole\Coroutine\Scheduler;

//while($z <= 2) {

    for ($j = 0; $j < 5; $j++) {
        $proxy = $proxy_list[$j];
        $run_main->add(function () use ($proxy) {
            echo 'Run worker' . PHP_EOL;
            for ($i = 0; $i < 11; $i++) {
                go(function () use ($proxy) {
                    $client = new Swoole\Coroutine\Http\Client('crator.z4c.ru', 80);
                    $client->set($proxy);
                    $client->set(['timeout' => 10]);
                    $client->get('/e/call_me?phone=45345345');
                    echo 'Proxy ' . $proxy['socks5_host'] . ' response ' . $client->body . PHP_EOL;
                    $client->close();
                });
            }
            echo 'End worker' . PHP_EOL;
        });
    }
    $run_main->start();
    $z++;
//}

