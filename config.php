<?php
// Set the include path to the eZ Components location, and bootstrap the
// library. The two lines below assume that you're using eZ Components from
// SVN -- see the installation guide at http://ezcomponents.org/docs/install.
ini_set( 'include_path', '/home/php/lib/:.' );
require 'Base/src/ezc_bootstrap.php';

// Add the class repository containing our application's classes. We store
// those in the /lib directory and the classes have the "aii" prefix.
ezcBase::addClassRepository( dirname( __FILE__ ) . '/lib', null, 'aii' );

// Configure the template system by telling it where to find templates, and
// where to put the compiled templates.
$tc = ezcTemplateConfiguration::getInstance();
$tc->templatePath = dirname( __FILE__ ) . '/templates';
$tc->compilePath = dirname( __FILE__ ) . '/cache';

ezcDbInstance::set( ezcDbFactory::create( 'mysql://mvctools:Yqtl6ngFuxOBoG3g0oGa@localhost/mvctools' ) );
?>
