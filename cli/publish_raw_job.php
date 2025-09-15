<?php
require_once __DIR__ . '/../include/connexionbd.php';

 = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../services/service-jobs/vendor/autoload.php',
];
foreach ( as ) {
    if (file_exists()) {
        require_once ;
        break;
    }
}

if (!class_exists('PhpAmqpLib\\Connection\\AMQPStreamConnection')) {
    fwrite(STDERR, "PhpAmqpLib not found. Install via Composer: composer require php-amqplib/php-amqplib\n");
    exit(2);
}

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

 = 'jobs';
 = 'raw_jobs';
 = 'job.raw';

System.Management.Automation.Internal.Host.InternalHost = getenv('RABBITMQ_HOST') ?: '127.0.0.1';
 = (int)(getenv('RABBITMQ_PORT') ?: 5672);
 = getenv('RABBITMQ_USER') ?: 'guest';
 = getenv('RABBITMQ_PASS') ?: 'guest';

try {
     = new AMQPStreamConnection(System.Management.Automation.Internal.Host.InternalHost, , , );
     = ->channel();

    ->exchange_declare(, 'topic', false, true, false);
    ->queue_declare(, false, true, false, false);
    ->queue_bind(, , );

     = json_encode([
        'source' => 'sample',
        'title' => 'Sample PHP Job',
        'url' => 'https://example.com/job/1',
        'posted_at' => date('c'),
    ], JSON_UNESCAPED_SLASHES);

     = new AMQPMessage(, [
        'content_type' => 'application/json',
        'delivery_mode' => 2,
    ]);

    ->basic_publish(, , );
    echo "Published: \n";

    ->close();
    ->close();
} catch (Throwable ) {
    fwrite(STDERR, 'Publish error: ' . ->getMessage() . "\n");
    exit(1);
}
