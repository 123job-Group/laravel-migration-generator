<?php
namespace Tests\Unit\Generators;

use Illuminate\Support\Str;
use LaravelMigrationGenerator\Generators\MySQLTableGenerator;
use Tests\TestCase;

class MySQLTableGeneratorTest extends TestCase {
    private function assertSchemaHas($str, $schema){
        $this->assertStringContainsString($str, $schema);
    }

    public function test_runs_correctly(){
        $generator = new MySQLTableGenerator('table', [
            '`id` int(9) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
            '`user_id` int(9) unsigned NOT NULL',
            '`note` varchar(255) NOT NULL',
            'KEY `fk_user_id_idx` (`user_id`)',
            'CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'
        ]);

        $generator->parse();
        $generator->finalPass();
        $schema = $generator->getSchema();
        $this->assertSchemaHas('$table->increments(\'id\');', $schema);
        $this->assertSchemaHas('$table->unsignedInteger(\'user_id\', 9);', $schema);
        $this->assertSchemaHas('$table->string(\'note\', 255);', $schema);
        $this->assertSchemaHas('$table->foreign(\'user_id\', \'fk_user_id\')->references(\'id\')->on(\'users\')->onDelete(\'cascade\')->onUpdate(\'cascade\');', $schema);
    }

    private function cleanUpMigrations($path){
        foreach(glob($path.'/*.php') as $file){
            unlink($file);
        }
        rmdir($path);
    }

    public function test_writes()
    {
        $generator = new MySQLTableGenerator('table', [
            '`id` int(9) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
            '`user_id` int(9) unsigned NOT NULL',
            '`note` varchar(255) NOT NULL',
            'KEY `fk_user_id_idx` (`user_id`)',
            'CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'
        ]);

        $generator->parse();
        $generator->finalPass();

        $path = __DIR__.'/../../migrations';

        if(!is_dir($path)){
            mkdir($path);
        }
        $generator->write($path);

        $this->assertFileExists($path.'/0000_00_00_000000_create_test_table_table.php');

        $this->cleanUpMigrations($path);
    }
}