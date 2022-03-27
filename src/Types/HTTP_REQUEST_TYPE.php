<?php

namespace ooobii\QuickRouter\Types;

class HTTP_REQUEST_TYPE {

    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    public const PATCH = 'PATCH';
    public const HEAD = 'HEAD';
    public const OPTIONS = 'OPTIONS';

    private $_selected = self::GET;

    public function __construct(string $name) {
        $this->_selected = $name;
    }

    public function __toString() {
        return $this->_selected;
    }

    public function __invoke() {
        return $this->_selected;
    }

    public function __get($name) {
        return $this->_selected;
    }
}



