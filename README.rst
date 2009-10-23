eZ Components Framework Research
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

by James Pic with the kind help of Derick Rethans

Introduction
============

No discussion was actively followed on the eZ Components mailing list, so this
stands for my personnal beleifs.

There are differences between the framework and libraries:

- the framework defines application configuration, to make applications to play
  nice together in the scope of a project.
- the framework ties in loosely coupled libraries, to convert configuration for
  one lib into the configuration for another.
- including relevant quality insurrance tools in a framework is important to
  keep users happy.

Part 0: application reusability
===============================

In framework_part0.php

Application configuration
-------------------------

An application only needs two parameters:

- a path to the root dir,
- a namespace.

Application configuration details is encapsulated in aiiAppConfiguration, which
extends ezcMvcDispatcherConfiguration.

In the purpose of allowing a user to value convention over configuration in
early application development stages, aiiAppConfiguration does not need to be
defined for each application and is based on sensible defaults.

The difference between an application and a project is that a project
encapsulates several applications, it's configuration class
aiiProjectConfiguration extends aiiAppConfiguration and has an additionnal
property "apps" which is an array of aiiAppConfiguration instances.

Project configuration
---------------------

The project configuration class has a getComponentConfiguration() method which
is may be used instead of hardcoding a component configuration.

Features
--------

Currently, the poc framework features:

- overloadable application layout conventions aiiAppConfiguration and the
  applications encapsulator aiiProjectConfiguration,
- aiiTemplateLocation which tries template paths from the most specific to the
  least specific (ie. project template path first then app template path),
- aiiProjectRouter which includes the routes from project apps after prefixing
  them with the app namespace,
- aiiProjectView which figures which app view is appropriate for the passed
  result,
- lazy configuration initializer for Template that loads all project apps
  extensions and sets up aiiTemplateLocation,
- lazy configuration initializer for PersistentObject that make all project
  apps persistent object definitions usable,

Apps
----

Currently, the poc framework includes apps:

Core
    Reverse routing template custom block.

Dev
    Scripts to make app development easier.

Admin
    Crud for persistent objects.

Sites
    Make a project multi-site.

Pages
    Simple cms.

Part 1: component conversion
============================

In framework_part1.php

POC: Planned support for JFPersistentObject from the Jetfuel_ framework.

The intermediary class into which definitions for components PersistentObject,
UserInput and DatabaseSchema is aiiMiddleProperty.

The name "Middle" was choosen because that is what all converters go through to
produce a definition.

It is the relation between definitions of all components. The missing link :)

Middle properties are caracterized by:

- the "definitions" array property ( componentName => definitionObject ),
- the ability to overload a component specific definition property in magic methods,
- the ability to have subproperties in property "middleProperties",
- the ability to hold values and convert them between components.

.. _Jetfuel: http://code.google.com/p/jetfuel/

Methods
-------

getComponentConfig( $componentName, $variableName )
    Like for aiiProjectConfig, it returns a config variable for a component.
    Example usage::

        $middleProperty->getComponentConfig( "PersistentObject", "columnName" )
    
    If method getPersistentObjectColumnName() is part $middleProperty then it
    will be called for return value.

getOrCreateMiddleProperty( $name, $default = null )
    Returns a child property instance, use $default if it does not exist.

Converters
----------

The DatabaseSchema and PersistentObject converter only take care of definitions.
In addition to definitions, the UserInput converter also takes care of values.

Examples
--------

Getting a database table definition for a peristent object definition::

    $session = ezcPersistentSessionInstance::get(  );
    $persistentObjectConverter = new aiiPersistentObjectDefinitionsConverter(
        $session->definitionManager->fetchDefinition( 'aiiSitesClass' ),
        $session->definitionManager
    );

    $middle = new aiiMiddleProperty(  );

    $persistentObjectConverter->toMiddleProperty( $middle );

    $dbTableConverter = aiiDatabaseSchemaTableConverter( new ezcDbSchemaTable );
    $table = $dbTableConverter->fromMiddleProperty( $middle );

Getting a persistent object definition for a middle property::

    $session = ezcPersistentSessionInstance::get(  );
    $persistentObjectConverter = new aiiPersistentObjectDefinitionsConverter(
        $session->definitionManager->fetchDefinition( 'aiiSitesClass' ),
        $session->definitionManager
    );

    class Site extends aiiMiddleProperty {
        public function __construct() {
            $this->getOrCreateMiddleProperty( 'domain', new aiiStringProperty );
            $this->getOrCreateMiddleProperty( 'name', new aiiStringProperty );
        }
    }
    
    $middleSite = new Site(  );
    
    $definition = $persistentObjectConverter->toMiddleProperty( $middleSite );

Part 2: Quality insurrance
==========================

A framework with good QA tools should theorically produce higher quality apps
than frameworks who don't include it.

A QA application should contain filters that record request/result/response
objects into a suitable format for a smoke test runner.

The converter lib can be tested with::

    phpunit apps/converter/formatsTest.php

The following file system layout is expected in apps/converters/tests::

    ComponentName/
        test_name/
            run.php
            expected.php

Run.php is expected to set the $expected variable. The $expected variable is
then tested against the return value of include expected.php

I'm not polishing until i'm sure what i want to do with this sources. It might
end in a commercial framework after all (not to be mistaken with the open
source framework Jetfuel_ by Blend Interactive).

Appendix
========

Issues
------

Namespacing isn't actually avalaible upstream
(http://issues.ez.no/IssueView.php?Id=15185)

The hack in app/core/template_extensions/route.php
(http://issues.ez.no/IssueView.php?Id=15614).

The dispatcher should be instanciated in the configuration.

Links
-----

Django framework short review:
http://blog.chocolatpistache.com/blog/2009/06/05/about-django-articles/
