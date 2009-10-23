<?php
require_once 'config.php';
require_once 'source.php';
require_once 'PHPUnit/Framework.php';

# define( 'WRITE_ALL' , 1);

class formatTest extends PHPUnit_Framework_TestCase
{
    protected $fixturePath = '';

    public function setUp()
    {
        $this->fixturePath = join( DIRECTORY_SEPARATOR, array( 
            dirname( __FILE__ ),
            'tests'
        ));
    }

    public function testConverter()
    {
        $directoryIterator = new DirectoryIterator( $this->fixturePath );
        foreach( $directoryIterator as $testDirectory ) {
            if ( $testDirectory->isdot(  ) ) continue;

            $testIterator = new DirectoryIterator( $testDirectory->getPath(  ) . DIRECTORY_SEPARATOR . ( string )$testDirectory );
            foreach( $testIterator as $test ) {
                if ( $test->isdot(  ) ) continue;

                $converter = call_user_func( array( 
                    $this,
                    'get' . ( string )$testDirectory . 'Converter'
                ) );

                $testPath = $test->getPath(  ) . DIRECTORY_SEPARATOR . ( string ) $test;
                
                include $testPath . DIRECTORY_SEPARATOR . 'run.php';

                $expected = include $testPath . DIRECTORY_SEPARATOR . 'expected.php';

                $this->assertEquals( $expected, $result, "$test failed" );
            }
        }
    }

    public function getPersistentObjectConverter(  ) {
        $session = ezcPersistentSessionInstance::get(  );
        $persistentObjectConverter = new aiiPersistentObjectDefinitionsConverter(
            $session->definitionManager->fetchDefinition( 'aiiSitesClass' ),
            $session->definitionManager
        );
        return $persistentObjectConverter;
    }

}
