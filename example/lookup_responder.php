<?php

use Ocallit\JqGrider\Lookuper\LookupManager;
use Ocallit\Sqler\SqlExecutor;

require_once '../vendor/autoload.php';
$sqlExecutor = new SqlExecutor(['hostname'=>'localhost', 'database' => 'tester', 'username' => 'tester', 'password' => 'tester']);
$lookupManager = new LookupManager($sqlExecutor, $_REQUEST['categoria'] ?? '');
// header('Content-Type: application/json');
echo json_encode( $lookupManager->handleRequest($_REQUEST) );
