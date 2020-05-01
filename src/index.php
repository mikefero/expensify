<?php

require 'vendor/autoload.php';
include 'Stats/NoStats.php';

use Expensify\Bedrock\BedrockError;
use Expensify\Bedrock\Client;
use Expensify\Bedrock\DB;
use Expensify\Bedrock\Stats\NoStats;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Psr\Log\NullLogger;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\ConsoleOutput;

// Handle both CLI and webserver
$is_cli = ((php_sapi_name() === 'cli') ? true : false);
$eol = ($is_cli ? PHP_EOL : '<br/>');
$space = ($is_cli ? ' ' : '&nbsp;');
if ($is_cli) {
    $is_logging = boolval(getenv('logging'));
} else {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $is_logging = boolval($query['logging']);
}

/**
 * Print all unhandled exceptions
 *
 * @param mixed $exception Exception that occurred
 */
function unhandled_exception_handler($exception) {
    print 'Unhandled Exception: ' . $exception->getMessage() . $eol
        . $space . $space
        . str_replace(PHP_EOL,
                      $eol . $space . $space,
                      $exception->getTraceAsString());
}
set_exception_handler('unhandled_exception_handler');

/**
 * Utility to "pretty print" strings for CLI and webserver displays
 *
 * @param string $string String to use for display
 */
function pretty_print(String $string) {
    // TODO: Inefficient test and conversion; this would need to be improved if used in production
    $decoded = json_decode($string);
    $encoded = json_last_error() == JSON_ERROR_NONE ? json_encode($decoded, JSON_PRETTY_PRINT)
                                                    : $string;
    $result = str_replace(' ', $space, str_replace(PHP_EOL, $eol, $encoded));

    print $result . $eol;
}

// Setup a file logger for debugging
$logger = new NullLogger();
if ($is_logging) {
    $logger = new Logger('bredrock-php-bindings');
    $logger->pushHandler(new StreamHandler('/logs/bindings.log', Logger::DEBUG));
}

// Configure the Bedrock PHP binding client to use the Docker service 'bedrock'
$config = [
    'clusterName' => 'fero',
    'mainHostConfigs' => [
        'bedrock' => [
            'port' => 8888
        ]
    ],
    'failoverHostConfigs' => [
        'bedrock' => [
            'port' => 8888
        ]
    ],
    'stats' => new NoStats(), // NullStats does not execute callback function
    'logger' => $logger
];

// Connect and run example query
Client::configure($config);
$bedrock = Client::getInstance();
$stats = $bedrock->getStats();
$db = new DB($bedrock);
$results = $db->run('SELECT 1 AS foo, 2 AS bar', true);

// Decode and display results; columns and rows
$decoded = json_decode($results, true);
if ($is_cli) {
    $table = new Table(new ConsoleOutput());
} else {
    print '<table style="border: 1px solid black; text-align: center;"><tr>';
}
$headers = $decoded['body']['headers'];
if ($is_cli) {
    $table->setHeaders($headers);
} else {
    foreach($headers as $header) {
        print '<th style="border: 1px solid black; padding: 10px;">' . $header . '</th>';
    }
}
$rows = $decoded['body']['rows'];
if ($is_cli) {
    $table->setRows($rows);
    $table->render();
} else {
    foreach($rows as $row) {
        print '</tr><tr>';
        foreach($row as $value) {
            print '<td style="border: 1px solid black; padding: 10px;">' . $value . '</td>';
        }
    }
    print '</tr></table>';
}

?>