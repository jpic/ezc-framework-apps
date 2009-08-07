<?php
include 'config.php';
include 'framework_part1.php';

$session = ezcPersistentSessionInstance::get(  );
$persistentObjectConverter = new aiiPersistentObjectDefinitionsConverter(
    $session->definitionManager->fetchDefinition( 'aiiSitesClass' ),
    $session->definitionManager
);

class aiiStringProperty extends aiiMiddleProperty {

}

class Site extends aiiMiddleProperty {
    public function __construct() {
        $this->getOrCreateMiddleProperty( 'domain', new aiiStringProperty );
        $this->getOrCreateMiddleProperty( 'name', new aiiStringProperty );
    }
}

$middleSite = new Site(  );

$persistentObjectConverter->toMiddleProperty( $middleSite );
