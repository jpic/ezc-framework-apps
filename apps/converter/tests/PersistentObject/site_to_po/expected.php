<?php return ezcPersistentObjectDefinition::__set_state(array(
   'propertyArray' => 
  array (
    'table' => 'cms_sites',
    'class' => 'aiiSitesClass',
    'idProperty' => 
    ezcPersistentObjectIdProperty::__set_state(array(
       'properties' => 
      array (
        'columnName' => 'id',
        'propertyName' => 'id',
        'propertyType' => 2,
        'generator' => 
        ezcPersistentGeneratorDefinition::__set_state(array(
           'class' => 'ezcPersistentSequenceGenerator',
           'params' => 
          array (
            'sequence' => 'cms_sites_id_seq',
          ),
        )),
        'visibility' => NULL,
        'databaseType' => 2,
      ),
    )),
    'properties' => 
    ezcPersistentObjectProperties::__set_state(array(
       'host' => 
      ezcPersistentObjectProperty::__set_state(array(
         'properties' => 
        array (
          'columnName' => 'host',
          'propertyName' => 'host',
          'propertyType' => 1,
          'converter' => NULL,
          'databaseType' => 2,
        ),
      )),
       'id' => 
      ezcPersistentObjectProperty::__set_state(array(
         'properties' => 
        array (
          'columnName' => 'id',
          'propertyName' => 'id',
          'propertyType' => 1,
          'converter' => NULL,
          'databaseType' => 2,
        ),
      )),
       'name' => 
      ezcPersistentObjectProperty::__set_state(array(
         'properties' => 
        array (
          'columnName' => 'name',
          'propertyName' => 'name',
          'propertyType' => 1,
          'converter' => NULL,
          'databaseType' => 2,
        ),
      )),
    )),
    'columns' => 
    ezcPersistentObjectColumns::__set_state(array(
       'host' => 
      ezcPersistentObjectProperty::__set_state(array(
         'properties' => 
        array (
          'columnName' => 'host',
          'propertyName' => 'host',
          'propertyType' => 1,
          'converter' => NULL,
          'databaseType' => 2,
        ),
      )),
       'id' => 
      ezcPersistentObjectIdProperty::__set_state(array(
         'properties' => 
        array (
          'columnName' => 'id',
          'propertyName' => 'id',
          'propertyType' => 2,
          'generator' => 
          ezcPersistentGeneratorDefinition::__set_state(array(
             'class' => 'ezcPersistentSequenceGenerator',
             'params' => 
            array (
              'sequence' => 'cms_sites_id_seq',
            ),
          )),
          'visibility' => NULL,
          'databaseType' => 2,
        ),
      )),
       'name' => 
      ezcPersistentObjectProperty::__set_state(array(
         'properties' => 
        array (
          'columnName' => 'name',
          'propertyName' => 'name',
          'propertyType' => 1,
          'converter' => NULL,
          'databaseType' => 2,
        ),
      )),
    )),
    'relations' => 
    ezcPersistentObjectRelations::__set_state(array(
    )),
  ),
)); ?>