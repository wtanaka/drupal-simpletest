Description
------------------
A framework for running unit tests in Drupal.

Status
------------------
One test suite has been written - 'user validation'. We need more.

Requirements
----------------

- Install the simpletest framework to a new directory called 'simpletest' right under Drupal root.
You can find it at http://www.lastcraft.com/simple_test.php

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

Author
---------------------
<Moshe Weitzman < weitzman at tejasa dot com >
