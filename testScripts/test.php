#!/usr/bin/php -q
<?php
/**
 * php script to used for testing
 *
 **/
$loader = require '../vendor/autoload.php';
$loader->add('dbTable\\', '../src/');
$loader->add('stringTemplate\\', '../src/');

echo "current directory: " . __DIR__ ."\n";
echo "internal encoding: " . mb_internal_encoding();

class Test {
    public static function Run(){
        // configure
        $schemaFile = __DIR__ .'/'. '../schema.xml';
        $phpGen = new phpGenerator\PhpClassGenerator($schemaFile);
        $phpGen->generate();
    }
}

Test::Run();
