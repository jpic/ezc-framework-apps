<?php
$session = ezcPersistentSessionInstance::get(  );

$persistentObjectConverter = new aiiPersistentObjectDefinitionsConverter(
    $session->definitionManager->fetchDefinition( 'aiiSitesClass' ),
    $session->definitionManager
);

$middle = new aiiMiddleProperty();

$persistentObjectConverter->toMiddleProperty( $middle );
$result = $persistentObjectConverter->fromMiddleProperty( $middle );

if ( defined( 'WRITE_ALL' ) ) {
    file_put_contents( 
        dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'expected.php',
        '<?php return ' . var_export( $result, true ) . '; ?>'
    );
}

return $result;
