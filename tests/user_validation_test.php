<?php

class UserValidationCase extends UnitTestCase {
  function UserValidationCase() {
    $this->UnitTestCase('User validation test');
  }
  
  // username validation
  
  function testMinLengthName() {
    $name = '';
    $result = user_validate_name($name);
    $this->assertNotNull($result, 'Excessively short username');
  }
  function testValidCharsName() {
    $name = 'ab/';
    $result = user_validate_name($name);
    $this->assertNotNull($result, 'Invalid chars in username');
  }
  function testMaxLengthName() {
    $name = str_repeat('a', 57);
    $result = user_validate_name($name);
    $this->assertNotNull($result, 'Excessively long username');
  }
  function testValidName() {
    $name = 'abc';
    $result = user_validate_name($name);
    $this->assertNull($result, 'Valid username');
  }
  
  // mail validation
  
  function testMinLengthMail() {
    $name = '';
    $result = user_validate_mail($name);
    $this->assertNotNull($result, 'Empty mail');
  }
  function testInValidMail() {
    $name = 'abc';
    $result = user_validate_mail($name);
    $this->assertNotNull($result, 'Invalid mail');
  }
  function testValidMail() {
    $name = 'absdsdsdc@dsdsde.com';
    $result = user_validate_mail($name);
    $this->assertNull($result, 'Valid mail');
  }
  
  // authmap validation
  
  function testValidAuthmap() {
    $name = 'absdsdsdc@dsdsde.com';
    $result = user_validate_authmap('Drupal', $name, 'drupal');
    $this->assertNull($result, 'Valid authmap');
  }
}

?>