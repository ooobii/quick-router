<?php 
declare(strict_types=1);
ini_set('xdebug.mode', "coverage");

use PHPUnit\Framework\TestCase;

/**
 * SetupHtaccessTests
 * @group Helpers
 */
class SetupHtaccessTests extends TestCase {

    public function tearDown(): void {

        if(is_file('.htaccess')) {
            unlink('.htaccess');
        }

    }

    /** @test */
    public function createHtaccessFile() {

        \ooobii\QuickRouter\Helpers\SetupHtaccess::createHtaccessFile();
        $this->assertFileExists('.htaccess', 'Helper failed to create .htaccess file.');

    }

    /** @test */
    public function updateHtaccessFile() {

        \ooobii\QuickRouter\Helpers\SetupHtaccess::createHtaccessFile();
        $this->assertFileExists('.htaccess', 'Helper failed to update .htaccess file.');

        //add content to the beginning of the file.
        $currentContents = file_get_contents('.htaccess');
        $newContents = '# This is a comment.' . PHP_EOL . $currentContents . PHP_EOL . '# This is another comment but at the end.';
        file_put_contents('.htaccess', $newContents);

        \ooobii\QuickRouter\Helpers\SetupHtaccess::createHtaccessFile();
        $this->assertFileExists('.htaccess', 'Helper failed to update .htaccess file.');
        
        //assert that the file contents ends with the config end tag.
        $this->assertStringEndsWith(
            \ooobii\QuickRouter\Helpers\SetupHtaccess::TAG_END . PHP_EOL,
            file_get_contents('.htaccess'),
            'Helper failed to update .htaccess file.'
        );
    }

}