<?php

// $Id: run_all_tests.php,v 1.3 2006/04/05 00:44:32 thomasilsche Exp $



/**

 * @file

 * Run all unit tests for all enabled modules.

 */
chdir(realpath(dirname(__FILE__) . '/../../'));

require_once './includes/bootstrap.inc';

drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);



// If not in 'safe mode', increase the maximum execution time:

if (!ini_get('safe_mode')) {

  set_time_limit(360);

}



simpletest_run_tests();

