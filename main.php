<?php

use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as HttpAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory as MessageFactory;
use Vantoozz\ProxyScraper\HttpClient\Psr18HttpClient;
use Vantoozz\ProxyScraper\Scrapers;

require_once __DIR__ . '/vendor/autoload.php';

$httpClient = new Psr18HttpClient(
    new HttpAdapter(new GuzzleClient([
        'connect_timeout' => 2,
        'timeout' => 3,
    ])),
    new MessageFactory
);
$run_main = new Swoole\Coroutine\Scheduler;
$compositeScraper = new Scrapers\CompositeScraper;

$compositeScraper->addScraper(new Scrapers\SocksProxyScraper($httpClient));

$proxy_list = [];
while (true) {
    echo 'Load proxy list' . PHP_EOL;
    foreach ($compositeScraper->get() as $proxy) {
        // echo $proxy . "\n";
        $proxy = explode(':', $proxy);
        $proxy_list[] = [
            'socks5_host' => $proxy[0],
            'socks5_port' => $proxy[1]
        ];
    }
    echo 'Proxy list loaded'.PHP_EOL;
    sleep(5);



    $proxy_count = count($proxy_list) - 1;
    echo 'Proxy count ' . $proxy_count . PHP_EOL;
    sleep(5);
    echo 'Work started '.PHP_EOL;
    sleep(5);
    while ($proxy_count > 0) {

        for (; $proxy_count > 0; $proxy_count--) {
            $proxy = $proxy_list[$proxy_count];
            $run_main->add(function () use ($proxy) {
                // echo 'Run worker' . PHP_EOL;
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
                // echo 'End worker' . PHP_EOL;
            });
        }
        $run_main->start();
    }
    echo 'Work ended '.PHP_EOL;
    sleep(5);
    unset($proxy_list);
}

