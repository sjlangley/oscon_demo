<?php
/**
 * Tests for the Cloud SQL session handler.
 *
 * Author: slangley@google.com (Stuart Langley)
 */

namespace google\appengine\ext\session;

require_once 'google/appengine/ext/session/CloudSqlSessionHandler.php';

class CloudSqlSessionHandlerTest extends \PHPUnit_Framework_TestCase {

  public function testInvalidInstanceName() {
    $this->setExpectedException('\InvalidArgumentException');
    $handler = new CloudSqlSessionHandler('', 'user', 'password', 'database');
  }

  // TODO(slangley): Work out how to test session gc.
  public function testSession() {
    $stub = $this->getMock('Mysql', array('connect', 'select_db', 'close',
        'escape_string', 'query'));
    $host = 'my_instance';
    $user = 'user';
    $passwd = 'password';
    $db = 'database';

    $linkId = "my_link_id";
    $mySessionId = "my_session_id";
    $escapedSessionId = "escaped_session_id";

    configureCloudSqlSessionHandler($host, $user, $passwd, $db, $stub);

    // Expectations for starting the session
    $stub->expects($this->at(0))
        ->method('connect')
        ->with($this->equalTo($host),
               $this->equalTo($user),
               $this->equalTo($passwd))
        ->will($this->returnValue($linkId));

    $stub->expects($this->at(1))
        ->method('select_db')
        ->with($this->equalTo($db),
               $this->equalTo($linkId))
        ->will($this->returnValue(true));

    $stub->expects($this->at(2))
        ->method('escape_string')
        ->with($this->equalTo($mySessionId), $this->equalTo($linkId))
        ->will($this->returnValue($escapedSessionId));

    $stub->expects($this->at(3))
        ->method('query')
        ->with($this->equalTo(
            "select data from sessions where id = '$escapedSessionId'"))
        ->will($this->returnValue(false));

    // Expectations for writing & closing the session
    $escapedAccess = 'escaped_access';
    $escapedData = 'escaped_data';

    $stub->expects($this->at(4))
        ->method('escape_string')
        ->with($this->equalTo($mySessionId), $this->equalTo($linkId))
        ->will($this->returnValue($escapedSessionId));

    $stub->expects($this->at(5))
        ->method('escape_string')
        ->with($this->anything(), $this->equalTo($linkId))
        ->will($this->returnValue($escapedAccess));

    $stub->expects($this->at(6))
        ->method('escape_string')
        ->with($this->anything(), $this->equalTo($linkId))
        ->will($this->returnValue($escapedData));

    $query = "replace into sessions values ('$escapedSessionId', " .
        "'$escapedAccess', '$escapedData')";
    $stub->expects($this->at(7))
        ->method('query')
        ->with($this->equalTo($query), $this->equalTo($linkId))
        ->will($this->returnValue(true));

    $stub->expects($this->at(8))
      ->method('close')
      ->with($this->equalTo($linkId))
      ->will($this->returnValue(true));

    session_id($mySessionId);
    // Supress errors to overcome 'cannot write header' error.
    @session_start();
    $_SESSION['Foo'] = 'Bar';
    session_write_close();
  }
}


