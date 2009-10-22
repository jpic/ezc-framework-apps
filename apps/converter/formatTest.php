<?php
require_once 'config.php';
require_once 'PHPUnit/Framework.php';

class formatTest extends PHPUnit_Framework_TestCase
{
    public function testFail(  ) {
        $this->fail( 'test' );
    }
}
