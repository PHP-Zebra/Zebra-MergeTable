<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 14-11-28
 * Time: 下午9:34
 */


define('TEST_ROOT', dirname(__FILE__));

require_once TEST_ROOT . DIRECTORY_SEPARATOR . 'vendor' .DIRECTORY_SEPARATOR . 'autoload.php';

$query = 'SELECT count(*) as cnt, field, count(*) FROM test GROUP BY cnt ORDER BY column desc, test, DD ASC';
$parser = new \PHPSQL\Parser($query, true);
print_r($parser->parsed);