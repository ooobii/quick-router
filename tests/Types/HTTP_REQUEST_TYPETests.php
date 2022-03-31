<?php 
declare(strict_types=1);
ini_set('xdebug.mode', "coverage");

use PHPUnit\Framework\TestCase;

/**
 * HTTP_REQUEST_TYPETests
 * @group Helpers
 */
class HTTP_REQUEST_TYPETests extends TestCase {

    /** @test */
    public function constructNew() {
        $this->assertInstanceOf(
            '\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE',
            new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('GET'),
            'Failed to create new class instance from string "GET"'
        );

        $this->assertInstanceOf(
            '\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE',
            new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('POST'),
            'Failed to create new class instance from string "POST"'
        );

        $this->assertInstanceOf(
            '\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE',
            new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('PUT'),
            'Failed to create new class instance from string "PUT"'
        );

        $this->assertInstanceOf(
            '\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE',
            new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('DELETE'),
            'Failed to create new class instance from string "DELETE"'
        );

        $this->assertInstanceOf(
            '\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE',
            new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('PATCH'),
            'Failed to create new class instance from string "PATCH"'
        );

        $this->assertInstanceOf(
            '\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE',
            new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('HEAD'),
            'Failed to create new class instance from string "HEAD"'
        );

        $this->assertInstanceOf(
            '\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE',
            new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('OPTIONS'),
            'Failed to create new class instance from string "OPTIONS"'
        );
    }

    /** @test */
    public function constructInvalid() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid HTTP request type: "INVALID"');
        new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('INVALID');
    }

    /** @test */
    public function stringConversion() {
        $this->assertEquals(
            'GET',
            (string) new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('GET'),
            'Failed to convert HTTP_REQUEST_TYPE to string'
        );
    }

    /** @test */
    public function invocation() {
        $this->assertEquals(
            'GET',
            (new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('GET'))(),
            'Failed to invoke HTTP_REQUEST_TYPE'
        );
    }

    /** @test */
    public function propertyIntercept() {
        $this->assertEquals(
            'GET',
            (new \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE('GET'))->value,
            'Failed to get HTTP_REQUEST_TYPE'
        );
    }

}