Description
-----------------------
A framework for running unit tests in Drupal.

Status
-----------------------
Some tests have been written
We need more, especially for contributed modules.

Requirements
-----------------------

- Install the simpletest framework to a new directory called 'simpletest' right under Drupal/modules directory.
You can find it at http://www.lastcraft.com/simple_test.php
- At least Version 1.0.1 of the simpletest framework is required (for upload tests).

Install
-----------------------

- Copy this module package to your /modules directory
- Activate the simpletest.module
- Visit the admin/simpletest page

Simpletest hook
-----------------------

This module offers a new 'simpletest' hook. Modules implementing this hook should return an array of paths which
point to test files. These paths should be relative to the /simpletest directory.

Writing Tests
-----------------------
Please write some tests.
See http://drupal.org/simpletest

Authors
-----------------------
Moshe Weitzman < weitzman at tejasa dot com >
Kuba Zygmunt   < kuba.zygmunt at gmail dot com >
Thomas Ilsche  < ThomasIlsche at gmx dot de >

Thanks
-----------------------
to Google for sponsoring the testsuite Summer of Code project.