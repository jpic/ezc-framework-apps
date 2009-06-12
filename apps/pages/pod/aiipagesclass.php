<?php

$def = new ezcPersistentObjectDefinition();
$def->table = "cms_pages";
$def->class = "aiiPagesClass";

$def->idProperty = new ezcPersistentObjectIdProperty;
$def->idProperty->columnName = 'id';
$def->idProperty->propertyName = 'id';
$def->idProperty->generator = new ezcPersistentGeneratorDefinition(
    'ezcPersistentSequenceGenerator',
    array( 'sequence' => 'cms_pages_id_seq' )
);


$def->properties['contents'] = new ezcPersistentObjectProperty;
$def->properties['contents']->columnName = 'contents';
$def->properties['contents']->propertyName = 'contents';
$def->properties['contents']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['id'] = new ezcPersistentObjectProperty;
$def->properties['id']->columnName = 'id';
$def->properties['id']->propertyName = 'id';
$def->properties['id']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['siteId'] = new ezcPersistentObjectProperty;
$def->properties['siteId']->columnName = 'site_id';
$def->properties['siteId']->propertyName = 'siteId';
$def->properties['siteId']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_INT;

$def->properties['slug'] = new ezcPersistentObjectProperty;
$def->properties['slug']->columnName = 'slug';
$def->properties['slug']->propertyName = 'slug';
$def->properties['slug']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->properties['title'] = new ezcPersistentObjectProperty;
$def->properties['title']->columnName = 'title';
$def->properties['title']->propertyName = 'title';
$def->properties['title']->propertyType = ezcPersistentObjectProperty::PHP_TYPE_STRING;

$def->relations['aiiSitesClass'] = new ezcPersistentOneToManyRelation(
    'cms_sites',
    'cms_pages'
);

$def->relations['aiiSitesClass']->columnMap = array(
    new ezcPersistentSingleTableMap(
        'id',
        'site_id'
    )
);

$def->relations['aiiSitesClass']->reverse = true;

return $def;

?>
