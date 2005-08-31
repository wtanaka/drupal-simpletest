Description
-----------------------
A framework for running unit tests in Drupal.

Status
-----------------------
Some core tests have been written including:

We need more, especially for contributed modules.

Requirements
-----------------------

- Install the simpletest framework to a new directory Drupal/modules/simpletest/simpletest directory.
  You can find it at https://sourceforge.net/project/showfiles.php?group_id=76550
- At least Version 1.0.1 alpta of the simpletest framework is required (for upload tests).
  Other Versions of simpletest have not been tested yet, 1.0.0 does not support browser upload.

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


First aid coding kit
-----------------------
If you are a beginner in drupal developing, we provide you with several pieces of information which will help you avoid ironic comments from more experienced programmers.

First of all, you have to know you will share your code with the community, so make shure that your code is well written. You know how hard is to read not your own code. Go to the Drupal Coding Standards[1] to see more.

Secondly, the better of writing code is to use Drupal API[2] than your own functions, for example if you want to add a new user use user_save function instead of sending query to the database. That is why you should be familiar with Drupal API Look into t()[3] and url()[4] carefully. 

Furthermore, we give you specific DrupalTest API, which helps you to write the tests. All the time we are considering adding new functions to our API so look for the version of simpletest module.

[1] http://drupal.org/node/318
[2] http://drupaldocs.org/api/head
[3] http://drupaldocs.org/api/head/function/t
[4] http://drupaldocs.org/api/head/function/url


Authors
-----------------------
Moshe Weitzman < weitzman at tejasa dot com >
Kuba Zygmunt   < kuba.zygmunt at gmail dot com >
Thomas Ilsche  < ThomasIlsche at gmx dot de >

Thanks
-----------------------
to Google for sponsoring the testsuite Summer of Code project.
see http://code.google.com/summerofcode.html