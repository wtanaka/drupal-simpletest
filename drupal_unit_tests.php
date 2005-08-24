<?php
/**
 * Implementes getTestInstances to allow access to the test objects from outside
 */
class DrupalGroupTest extends GroupTest {
  function DrupalGroupTest($label) {
    $this->GroupTest($label);
  }
  
  /**
   * @return array of instanciated tests that this GroupTests holds
   */
  function getTestInstances() {
    for ($i = 0, $count = count($this->_test_cases); $i < $count; $i++) {
      if (is_string($this->_test_cases[$i])) {
        $class = $this->_test_cases[$i];
        $this->_test_cases[$i] = &new $class();
      }
    }
    return $this->_test_cases; 
  } 
}

class DrupalUnitTests extends DrupalGroupTest {
  /**
   * Constructor
   * @param array   $class_list  list containing the classes of tests to be processed
   *                             default: NULL - run all tests
   */
  function DrupalUnitTests($class_list = NULL) {
    static $classes;
    $this->GroupTest('Drupal Unit Tests');
    
    /* Tricky part to avoid double inclusion */
    if (!$classes) {
      $files = module_invoke_all('simpletest');
    
      $existing_classes = get_declared_classes();
      foreach ($files as $file) {
          if ($error = $this->_requireWithError($file)) {
          $this->addTestCase(new BadGroupTest($file, $error));
          return;
        }
      }
      
      if (is_null($class_list)) {
        $classes = $this->_selectRunnableTests($existing_classes, get_declared_classes());
      }
      else {
        $classes = $class_list;
      }
    }
        
    if (count($classes) == 0) {
      $this->addTestCase(new BadGroupTest($test_file, 'No new test cases'));
      return;
    }
    if (!is_null($class_list)) {
      $classes = $class_list; 
    }
    $groups = array();
    foreach ($classes as $class) {
      $this->_addClassToGroups($groups, $class);
    }
    foreach ($groups as $group_name => $group) {
      $group_test = &new DrupalGroupTest($group_name);
      foreach ($group as $key => $v) {
        $group_test->addTestCase($group[$key]); 
      }
      $this->addTestCase($group_test);
    }
  }

  /**
   * Adds a class to a groups array specified by the get_info of the group
   * @param array  $groups Group of categorizesd tests
   * @param string $class  Name of a class
   */
  function _addClassToGroups(&$groups, $class) {
    $test = &new $class();
    if (method_exists($test, 'get_info')) {
      $info = $test->get_info();
      $groups[$info['group']][] = $test;
    }
    else {
      $groups[$class][] = $test; 
    }
  }
  
  /**
   * Invokes run() on all of the held test cases, instantiating
   * them if necessary.
   * The Druapl version uses paintHeader instead of paintGroupStart
   * to avoid collapsing of the very top level.
   *
   * @param SimpleReporter $reporter    Current test reporter.
   * @access public
   */
  function run(&$reporter) {
    cache_clear_all();
    parent::run($reporter);
  }
}
?>