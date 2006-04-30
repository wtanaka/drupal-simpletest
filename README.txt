Description
-----------------------
A framework for running unit tests in Drupal.

USAGE WARNING
-----------------------
DO NOT USE THIS MODULE IN PRODUCTION ENVIRONMENTS! NEVER!
During running a test this module may (completely) change your Drupal setup.
It usually recovers the orignal setup at the end of a of the test but there
is no warranty that it will work, for example if an error occurs or timeout happens.

Since all registered users have the registered users role this role
MUST have the default settings for current tests to run correct.
This also applies for anonymous user.


Status
-----------------------
Some core tests have been written - see the /tests subdirectory. We need more,
especially for contributed modules.

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
see http://code.google.com/summerofcode.html