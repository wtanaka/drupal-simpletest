<?php
/* $Id: drupal_test_case.php,v 1.2 2005/08/25 13:27:38 thomasilsche Exp $ */

/**
 * Test case for typical Drupal tests.
 * Extends WebTestCase for comfortable browser usage
 * but also implements all UnitTestCase methods, I wish
 * WebTestCase would do this.
 */
class DrupalTestCase extends WebTestCase {
  var $_content;
  var $_cleanupModules   = array();
  var $_cleanupVariables = array();


  function DrupalTestCase($label = NULL) {
    if (! $label) {
      if (method_exists($this, 'get_info')) {
        $info  = $this->get_info();
        $label = $info['name'];
      }
    }
    $this->WebTestCase($label);
  }

  /**
   * Do a post request on a drupal page.
   * It will be done as usual post request with SimpleBrowser
   * By $reporting you specify if this request does assertations or not
   * Warning: empty ("") returns will cause fails with $reporting
   *
   * @param string  $path      location of the post form
   * @param array   $edit      field data
   * @param string  $submit    name of the submit button, untranslated
   * @param boolean $reporting assertations or not
   */
  function drupalPostRequest($path, $edit, $submit, $reporting = TRUE) {
    $url = url($path, NULL, NULL, TRUE);
    $ret = $this->_browser->get($url);
    if ($reporting) {
      $this->assertTrue($ret, " [browser] GET $url");
    }
    foreach ($edit as $field_name => $field_value) {
      $ret = $this->_browser->setField("edit[$field_name]", $field_value);
      if ($reporting) {
        $this->assertTrue($ret, " [browser] Setting edit[$field_name]=\"$field_value\"");
      }
    }
    $ret = $this->_browser->clickSubmit(t($submit));
    if ($reporting) {
      $this->assertTrue($ret, ' [browser] POST by click on ' . t($submit));
    }
    $this->_content = $this->_browser->getContent();
  }

  /**
   *    Follows a link by name. Will click the first link
   *    found with this link text by default, or a later
   *    one if an index is given. Match is case insensitive
   *    with normalised space.
   *    Does make assertations if the click was sucessful or not
   *    and it does translate label.
   *    WARNING: Assertation fails on empty ("") output from the clicked link
   *
   *    @param string $label      Text between the anchor tags.
   *    @param integer $index     Link position counting from zero.
   *    @param boolean $reporting Assertations or not
   *    @return boolean/string    Page on success.
   *
   *    @access public
   */
  function clickLink($label, $index = 0, $reporting = true) {
    if ($reporting) {
      $url_before = $this->getUrl();
      $urls = $this->_browser->_page->getUrlsByLabel($label);
      if (count($urls) < $index + 1) {
        $url_target = 'URL NOT FOUND!';
      } else {
        $url_target = $urls[$index]->asString();
      }
    }
    
    $ret = parent::clickLink(t($label), $index);
    
    if ($reporting) {
      $this->assertTrue($ret, ' [browser] clicked link '. t($label) . " ($url_target) from $url_before");
    }
    
    return $ret;
  }
  
  /**
   * @TODO: needs documention
   */
  function drupalGetContent() {
    return $this->_content;
  }

  /**
   * Generates a random string, to be used as name or whatever
   * @param integer $number   number of characters
   * @return ransom string
   */
  function randomName($number = 4, $ret = 'simpletest_') {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
    for ($x = 0; $x < $number; $x++) {
        $ret .= $chars{mt_rand(0, strlen($chars)-1)};
        if ($x == 0) {
            $chars .= '0123456789';
        }
    }
    return $ret;
  }

  /**
   * Just some info for the reporter
   */
  function run(&$reporter) {
    $arr = array('class' => get_class($this));
    if (method_exists($this, 'get_info')) {
      $arr = array_merge($arr, $this->get_info());
    }
    $reporter->test_info_stack[] = $arr;
    parent::run($reporter);
    array_pop($reporter->test_info_stack);
  }

  
  /**
   * Enables a drupal module
   * @param string $name name of the module
   * @return boolean success
   */
  function drupalModuleEnable($name) {
    if (module_exist($name)) {
      $this->pass(" [module] $name already enabled");
      return TRUE; 
    }
    /* Refreshes the system table */
    system_module_listing();
    /* Update table */
    db_query("UPDATE {system} SET status = 1 WHERE name = '%s' AND type = 'module'", $name);
    if (db_affected_rows()) {
      /* Make sure not overwriting when double switching */
      if (!isset($this->_cleanupModules[$name])) {
        $this->_cleanupModules[$name] = 0;
      }
      /* refresh module_list */
      module_list(TRUE);
      $this->pass(" [module] $name enabled");
      return TRUE; 
    }
    $this->fail(" [module] $name could not be enbled, probably file not exists");
    return FALSE;
  }

  
  /**
   * Disables a drupal module
   * @param string $name name of the module
   * @return boolean success
   */
  function drupalModuleDisable($name) {
    if (!module_exist($name)) {
      $this->pass(" [module] $name already disabled");
      return TRUE; 
    }
    /* Update table */
    db_query("UPDATE {system} SET status = 0 WHERE name = '%s' AND type = 'module'", $name);
    if (db_affected_rows()) {
      /* Make sure not overwriting when double switching */
      if (!isset($this->_cleanupModules[$name])) {
        $this->_cleanupModules[$name] = 1;
      }
      /* refresh module_list */
      module_list(TRUE);
      $this->pass(" [module] $name disabled");
      return TRUE; 
    }
    $this->fail(" [module] $name could not be disabled for unknown reason");
    return FALSE;
  }
  
  
  /**
   * Set a druapl variable and keep track of the changes for tearDown()
   * @param string $name name of the value
   * @param mixed  $value value 
   */
  function drupalVariableSet($name, $value) {
    /* NULL variables would anyways result in default because of isset */
    $old_value = variable_get($name, NULL);
    if ($value !== $old_value) {
      variable_set($name, $value);
      /* Use array_key_exists instead of isset so NULL values do not get overwritten */
      if (!array_key_exists($name, $this->_cleanupVariables)) {
        $this->_cleanupVariables[$name] = $old_value;
      }
    }
  }
  

  /**
   * tearDown implementation, setting back switched modules etc
   */
  function tearDown() {
    foreach ($this->_cleanupModules as $name => $status) {
      db_query("UPDATE {system} SET status = %d WHERE name = '%s' AND type = 'module'", $status, $name); 
    }
    $this->_cleanupModules = array();
    
    foreach ($this->_cleanupVariables as $name => $value) {
      if (is_null($value)) {
        variable_del($name); 
      } else {
        variable_set($name, $value);
      }
    }
    $this->_cleanupVariables = array();
  }
  
  /* Taken from UnitTestCase */
        /**
         *    Will be true if the value is null.
         *    @param null $value       Supposedly null value.
         *    @param string $message   Message to display.
         *    @return boolean                        True on pass
         *    @access public
         */
        function assertNull($value, $message = "%s") {
            $dumper = &new SimpleDumper();
            $message = sprintf(
                    $message,
                    "[" . $dumper->describeValue($value) . "] should be null");
            return $this->assertTrue(! isset($value), $message);
        }

        /**
         *    Will be true if the value is set.
         *    @param mixed $value           Supposedly set value.
         *    @param string $message        Message to display.
         *    @return boolean               True on pass.
         *    @access public
         */
        function assertNotNull($value, $message = "%s") {
            $dumper = &new SimpleDumper();
            $message = sprintf(
                    $message,
                    "[" . $dumper->describeValue($value) . "] should not be null");
            return $this->assertTrue(isset($value), $message);
        }

        /**
         *    Type and class test. Will pass if class
         *    matches the type name or is a subclass or
         *    if not an object, but the type is correct.
         *    @param mixed $object         Object to test.
         *    @param string $type          Type name as string.
         *    @param string $message       Message to display.
         *    @return boolean              True on pass.
         *    @access public
         */
        function assertIsA($object, $type, $message = "%s") {
            return $this->assertExpectation(
                    new IsAExpectation($type),
                    $object,
                    $message);
        }

        /**
         *    Type and class mismatch test. Will pass if class
         *    name or underling type does not match the one
         *    specified.
         *    @param mixed $object         Object to test.
         *    @param string $type          Type name as string.
         *    @param string $message       Message to display.
         *    @return boolean              True on pass.
         *    @access public
         */
        function assertNotA($object, $type, $message = "%s") {
            return $this->assertExpectation(
                    new NotAExpectation($type),
                    $object,
                    $message);
        }

        /**
         *    Will trigger a pass if the two parameters have
         *    the same value only. Otherwise a fail.
         *    @param mixed $first          Value to compare.
         *    @param mixed $second         Value to compare.
         *    @param string $message       Message to display.
         *    @return boolean              True on pass
         *    @access public
         */
        function assertEqual($first, $second, $message = "%s") {
            return $this->assertExpectation(
                    new EqualExpectation($first),
                    $second,
                    $message);
        }

        /**
         *    Will trigger a pass if the two parameters have
         *    a different value. Otherwise a fail.
         *    @param mixed $first           Value to compare.
         *    @param mixed $second          Value to compare.
         *    @param string $message        Message to display.
         *    @return boolean               True on pass
         *    @access public
         */
        function assertNotEqual($first, $second, $message = "%s") {
            return $this->assertExpectation(
                    new NotEqualExpectation($first),
                    $second,
                    $message);
        }

        /**
         *    Will trigger a pass if the two parameters have
         *    the same value and same type. Otherwise a fail.
         *    @param mixed $first           Value to compare.
         *    @param mixed $second          Value to compare.
         *    @param string $message        Message to display.
         *    @return boolean               True on pass
         *    @access public
         */
        function assertIdentical($first, $second, $message = "%s") {
            return $this->assertExpectation(
                    new IdenticalExpectation($first),
                    $second,
                    $message);
        }

        /**
         *    Will trigger a pass if the two parameters have
         *    the different value or different type.
         *    @param mixed $first           Value to compare.
         *    @param mixed $second          Value to compare.
         *    @param string $message        Message to display.
         *    @return boolean               True on pass
         *    @access public
         */
        function assertNotIdentical($first, $second, $message = "%s") {
            return $this->assertExpectation(
                    new NotIdenticalExpectation($first),
                    $second,
                    $message);
        }

        /**
         *    Will trigger a pass if both parameters refer
         *    to the same object. Fail otherwise.
         *    @param mixed $first           Object reference to check.
         *    @param mixed $second          Hopefully the same object.
         *    @param string $message        Message to display.
         *    @return boolean               True on pass
         *    @access public
         */
        function assertReference(&$first, &$second, $message = "%s") {
            $dumper = &new SimpleDumper();
            $message = sprintf(
                    $message,
                    "[" . $dumper->describeValue($first) .
                            "] and [" . $dumper->describeValue($second) .
                            "] should reference the same object");
            return $this->assertTrue(
                    SimpleTestCompatibility::isReference($first, $second),
                    $message);
        }

        /**
         *    Will trigger a pass if both parameters refer
         *    to different objects. Fail otherwise.
         *    @param mixed $first           Object reference to check.
         *    @param mixed $second          Hopefully not the same object.
         *    @param string $message        Message to display.
         *    @return boolean               True on pass
         *    @access public
         */
        function assertCopy(&$first, &$second, $message = "%s") {
            $dumper = &new SimpleDumper();
            $message = sprintf(
                    $message,
                    "[" . $dumper->describeValue($first) .
                            "] and [" . $dumper->describeValue($second) .
                            "] should not be the same object");
            return $this->assertFalse(
                    SimpleTestCompatibility::isReference($first, $second),
                    $message);
        }

        /**
         *    Will trigger a pass if the Perl regex pattern
         *    is found in the subject. Fail otherwise.
         *    @param string $pattern    Perl regex to look for including
         *                              the regex delimiters.
         *    @param string $subject    String to search in.
         *    @param string $message    Message to display.
         *    @return boolean           True on pass
         *    @access public
         */
        function assertWantedPattern($pattern, $subject, $message = "%s") {
            return $this->assertExpectation(
                    new WantedPatternExpectation($pattern),
                    $subject,
                    $message);
        }

        /**
         *    Will trigger a pass if the perl regex pattern
         *    is not present in subject. Fail if found.
         *    @param string $pattern    Perl regex to look for including
         *                              the regex delimiters.
         *    @param string $subject    String to search in.
         *    @param string $message    Message to display.
         *    @return boolean           True on pass
         *    @access public
         */
        function assertNoUnwantedPattern($pattern, $subject, $message = "%s") {
            return $this->assertExpectation(
                    new UnwantedPatternExpectation($pattern),
                    $subject,
                    $message);
        }

        /**
         *    Confirms that no errors have occoured so
         *    far in the test method.
         *    @param string $message    Message to display.
         *    @return boolean           True on pass
         *    @access public
         */
        function assertNoErrors($message = "%s") {
            $queue = &SimpleErrorQueue::instance();
            return $this->assertTrue(
                    $queue->isEmpty(),
                    sprintf($message, "Should be no errors"));
        }

        /**
         *    Confirms that an error has occoured and
         *    optionally that the error text matches exactly.
         *    @param string $expected   Expected error text or
         *                              false for no check.
         *    @param string $message    Message to display.
         *    @return boolean           True on pass
         *    @access public
         */
        function assertError($expected = false, $message = "%s") {
            $queue = &SimpleErrorQueue::instance();
            if ($queue->isEmpty()) {
                $this->fail(sprintf($message, "Expected error not found"));
                return;
            }
            list($severity, $content, $file, $line, $globals) = $queue->extract();
            $severity = SimpleErrorQueue::getSeverityAsString($severity);
            return $this->assertTrue(
                    ! $expected || ($expected == $content),
                    "Expected [$expected] in PHP error [$content] severity [$severity] in [$file] line [$line]");
        }

        /**
         *    Confirms that an error has occoured and
         *    that the error text matches a Perl regular
         *    expression.
         *    @param string $pattern   Perl regular expresion to
         *                              match against.
         *    @param string $message    Message to display.
         *    @return boolean           True on pass
         *    @access public
         */
        function assertErrorPattern($pattern, $message = "%s") {
            $queue = &SimpleErrorQueue::instance();
            if ($queue->isEmpty()) {
                $this->fail(sprintf($message, "Expected error not found"));
                return;
            }
            list($severity, $content, $file, $line, $globals) = $queue->extract();
            $severity = SimpleErrorQueue::getSeverityAsString($severity);
            return $this->assertTrue(
                    (boolean)preg_match($pattern, $content),
                    "Expected pattern match [$pattern] in PHP error [$content] severity [$severity] in [$file] line [$line]");
        }


}
?>