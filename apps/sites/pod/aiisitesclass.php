<?php

$def = new ezcPersistentObjectDefinition();
$def->table = "cms_sites";
$def->class = "aiiSitesClass";

$def->idProperty = new ezcPersistentObjectIdProperty;
$def->idProperty->columnName = 'id';
$def->idProperty->propertyName = 'id';
$def->idProperty->generator = new ezcPersistentGeneratorDefinition(
    'ezcPersistentSequenceGenerator',
    array( 'sequence' => 'cms_sites_id_seq' )
);


$def->properties['host'] = new ezcPersistentObjectProperty;
$def->properties['host']->columnName = 'host';
$def->properties['host']->propertyName = 'host';
$def->properties['host']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['id'] = new ezcPersistentObjectProperty;
$def->properties['id']->columnName = 'id';
$def->properties['id']->propertyName = 'id';
$def->properties['id']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['name'] = new ezcPersistentObjectProperty;
$def->properties['name']->columnName = 'name';
$def->properties['name']->propertyName = 'name';
$def->properties['name']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

return $def;

?>
