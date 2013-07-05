<?php
/**
 * Unit tests for the capabilities API.
 *
 * @author slangley@google.com (Stuart Langley)
 */

require_once 'google/appengine/api/capabilities/Capability.php';
require_once 'google/appengine/api/capabilities/UnknownCapabilityError.php';
require_once 'google/appengine/testing/ApiProxyTestBase.php';

use google\appengine\api\capabilities\Capability;
use google\appengine\CapabilityConfig\Status;
use google\appengine\IsEnabledRequest;
use google\appengine\IsEnabledResponse;
use google\appengine\IsEnabledResponse\SummaryStatus;
use google\appengine\testing\ApiProxyTestBase;

class CapabilityTest extends ApiProxyTestBase {

  public function setUp() {
    parent::setUp();
    $this->_SERVER = $_SERVER;
  }

  public function tearDown() {
    $_SERVER = $this->_SERVER;
    parent::tearDown();
  }

  public function testCapabilityEnabled() {
    $req = new IsEnabledRequest();
    $req->setPackage('datastore');
    $req->addCapability('*');
    $req->addCapability('write');

    $resp = new IsEnabledResponse();
    $resp->setSummaryStatus(SummaryStatus::ENABLED);
    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('*');
    $config->setStatus(Status::ENABLED);

    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('write');
    $config->setStatus(Status::ENABLED);
    $config->setAdminMessage(
        "Write performance is degraded for the next 15-20 minutes.");

    $this->apiProxyMock->expectCall('capability_service',
                                    'IsEnabled',
                                    $req,
                                    $resp);

    $capability = new Capability('datastore', array('write'));
    $this->assertTrue($capability->isEnabled());
    $this->apiProxyMock->verify();
  }

  public function testCapabilityDisabled() {
    $req = new IsEnabledRequest();
    $req->setPackage('datastore');
    $req->addCapability('*');
    $req->addCapability('write');

    $resp = new IsEnabledResponse();
    $resp->setSummaryStatus(SummaryStatus::DISABLED);
    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('*');
    $config->setStatus(Status::ENABLED);

    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('write');
    $config->setStatus(Status::DISABLED);
    $config->setAdminMessage(
        "The datastore is in read-only mode for scheduled maintenance.");

    $this->apiProxyMock->expectCall('capability_service',
                                    'IsEnabled',
                                    $req,
                                    $resp);

    $capability = new Capability('datastore', array('write'));
    $this->assertFalse($capability->isEnabled());
    $this->apiProxyMock->verify();
  }

  public function testCapabilityScheduledPast() {
    $req = new IsEnabledRequest();
    $req->setPackage('datastore');
    $req->addCapability('*');
    $req->addCapability('write');

    $resp = new IsEnabledResponse();
    $resp->setSummaryStatus(SummaryStatus::SCHEDULED_NOW);
    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('*');
    $config->setStatus(Status::ENABLED);

    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('write');
    $config->setStatus(Status::SCHEDULED);
    $config->setAdminMessage(
        "The datastore will be in read-only mode starting at 4pm PDT.");


    $this->apiProxyMock->expectCall('capability_service',
                                    'IsEnabled',
                                    $req,
                                    $resp);

    $capability = new Capability('datastore', array('write'));
    $this->assertTrue($capability->isEnabled());
    $this->apiProxyMock->verify();
  }

  public function testCapabilityScheduledFuture() {
    $req = new IsEnabledRequest();
    $req->setPackage('datastore');
    $req->addCapability('*');
    $req->addCapability('write');

    $resp = new IsEnabledResponse();
    $resp->setSummaryStatus(SummaryStatus::SCHEDULED_FUTURE);
    $resp->setTimeUntilScheduled(15);
    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('*');
    $config->setStatus(Status::ENABLED);

    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('write');
    $config->setStatus(Status::SCHEDULED);
    $config->setAdminMessage(
        "The datastore will be in read-only mode starting at 4pm PDT.");


    $this->apiProxyMock->expectCall('capability_service',
                                    'IsEnabled',
                                    $req,
                                    $resp);

    $capability = new Capability('datastore', array('write'));
    $this->assertTrue($capability->isEnabled());
    $this->apiProxyMock->verify();
  }

  public function testMultipleCapabilities() {
    $req = new IsEnabledRequest();
    $req->setPackage('datastore');
    $req->addCapability('*');
    $req->addCapability('write');
    $req->addCapability('erase');

    $resp = new IsEnabledResponse();
    $resp->setSummaryStatus(SummaryStatus::SCHEDULED_FUTURE);
    $resp->setTimeUntilScheduled(15);
    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('*');
    $config->setStatus(Status::ENABLED);

    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('write');
    $config->setStatus(Status::ENABLED);

    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('erase');
    $config->setStatus(Status::ENABLED);

    $this->apiProxyMock->expectCall('capability_service',
                                    'IsEnabled',
                                    $req,
                                    $resp);

    $capability = new Capability('datastore', array('write', 'erase'));
    $this->assertTrue($capability->isEnabled());
    $this->apiProxyMock->verify();
  }

  public function testUnknownCapability() {
    $req = new IsEnabledRequest();
    $req->setPackage('datastore');
    $req->addCapability('*');
    $req->addCapability('write');

    $resp = new IsEnabledResponse();
    $resp->setSummaryStatus(SummaryStatus::UNKNOWN);
    $config = $resp->addConfig();
    $config->setPackage('datastore');
    $config->setCapability('*');
    $config->setStatus(Status::ENABLED);

    $this->apiProxyMock->expectCall('capability_service',
                                    'IsEnabled',
                                    $req,
                                    $resp);
    $this->setExpectedException(
      'google\appengine\api\capabilities\UnknownCapabilityError');

    $capability = new Capability('datastore', array('write'));
    $capability->isEnabled();
    $this->apiProxyMock->verify();
  }

  public function testInvalidCapability() {
    $this->setExpectedException('\InvalidArgumentException');
    $capability = new Capability('datastore', 'write');
  }
}

