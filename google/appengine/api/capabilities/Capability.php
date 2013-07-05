<?php
/**
 * Allow users to check the status of an API set.
 *
 * Example usage:
 *
 * require_once "google/appengine/api/capabilities/Capability.php";
 *
 * use google\appengine\api\capabilities\Capability;
 *
 * $capability = new Capability('datastore_v3', array('write'));
 * echo $capability->isEnabled();
 *
 * @author slangley@google.com (Stuart Langley)
 */

namespace google\appengine\api\capabilities;

require_once 'google/appengine/api/capabilities/capability_service_pb.php';
require_once 'google/appengine/api/capabilities/UnknownCapabilityError.php';
require_once 'google/appengine/runtime/ApiProxy.php';

use \google\appengine\IsEnabledRequest;
use \google\appengine\IsEnabledResponse;
use \google\appengine\IsEnabledResponse\SummaryStatus;
use \google\appengine\runtime\ApiProxy;

class Capability {
  private $packageName;
  private $capabilities;
  /**
   * If no capabilities are provided, then this will check if the entire
   * package is enabled.
   *
   * @param string $packageName The name of the package to check.
   * @param array $capabilities Array of strings of the capabilities to check.
   */
  public function __construct($packageName, $capabilities = null) {
    $this->packageName = $packageName;
    if (is_null($capabilities)) {
      $capabilities = array();
    } else if (!is_array($capabilities)) {
      throw new \InvalidArgumentException("capabilities is not an array.");
    }
    $this->capabilities = array_merge(array('*'), $capabilities);
  }

  /**
   * Perform the status check for the capability. Each time this method
   * is called the status will be re-checked.
   * @return boolean The enabled status of the package and capability set.
   */
  public function isEnabled() {
    $req = new IsEnabledRequest();
    $resp = new IsEnabledResponse();

    $req->setPackage($this->packageName);

    foreach ($this->capabilities as $capability) {
      $req->addCapability($capability);
    }

    ApiProxy::makeSyncCall('capability_service', 'IsEnabled', $req, $resp);

    $status = $resp->getSummaryStatus();

    if ($status === SummaryStatus::UNKNOWN) {
      throw new UnknownCapabilityError();
    }

    return ($status === SummaryStatus::ENABLED ||
            $status === SummaryStatus::SCHEDULED_FUTURE ||
            $status === SummaryStatus::SCHEDULED_NOW);
  }
}

