<?php
/**
 * PHP Unit tests for the BlobstoreService.
 *
 * @author slangley@google.com (Stuart Langley)
 */

require_once 'google/appengine/api/blobstore/BlobstoreService.php';
require_once 'google/appengine/testing/ApiProxyTestBase.php';

use google\appengine\api\blobstore\BlobstoreService;
use google\appengine\testing\ApiProxyTestBase;
use google\appengine\BlobstoreServiceError;

/**
 * Unittest for BlobstoreService class.
 */
class BlobstoreServiceTest extends ApiProxyTestBase {
  public function setUp() {
    parent::setUp();
    $this->_SERVER = $_SERVER;
  }

  public function tearDown() {
    $_SERVER = $this->_SERVER;
    parent::tearDown();
  }

  public function testCreateUploadUrl() {
    $req = new \google\appengine\CreateUploadURLRequest();
    $req->setSuccessPath('http://foo/bar');

    $resp = new \google\appengine\CreateUploadURLResponse();
    $resp->setUrl('http://upload/to/here');

    $this->apiProxyMock->expectCall('blobstore', 'CreateUploadURL', $req,
        $resp);

    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar');
    $this->assertEquals($upload_url, 'http://upload/to/here');
    $this->apiProxyMock->verify();
  }

  public function testInvalidSuccessPath() {
    $this->setExpectedException('\InvalidArgumentException');
    $upload_url = BlobstoreService::CreateUploadUrl(10);
  }

  public function testSetMaxBytesPerBlob() {
    $req = new \google\appengine\CreateUploadURLRequest();
    $req->setSuccessPath('http://foo/bar');
    $req->setMaxUploadSizePerBlobBytes(37337);

    $resp = new \google\appengine\CreateUploadURLResponse();
    $resp->setUrl('http://upload/to/here');

    $this->apiProxyMock->expectCall('blobstore', 'CreateUploadURL', $req,
        $resp);

    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar',
        ['max_bytes_per_blob' => 37337,]);
    $this->assertEquals($upload_url, 'http://upload/to/here');
    $this->apiProxyMock->verify();
  }

  public function testInvalidMaxBytesPerBlob() {
    $this->setExpectedException('\InvalidArgumentException');
    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar',
        ['max_bytes_per_blob' => 'not an int',]);
  }

  public function testNegativeMaxBytesPerBlob() {
    $this->setExpectedException('\InvalidArgumentException');
    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar',
        ['max_bytes_per_blob' => -1,]);
  }

  public function testSetMaxBytesTotal() {
    $req = new \google\appengine\CreateUploadURLRequest();
    $req->setSuccessPath('http://foo/bar');
    $req->setMaxUploadSizeBytes(137337);

    $resp = new \google\appengine\CreateUploadURLResponse();
    $resp->setUrl('http://upload/to/here');

    $this->apiProxyMock->expectCall('blobstore', 'CreateUploadURL', $req,
        $resp);

    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar',
        ['max_bytes_total' => 137337,]);
    $this->assertEquals($upload_url, 'http://upload/to/here');
    $this->apiProxyMock->verify();
  }

  public function testInvalidMaxBytes() {
    $this->setExpectedException('\InvalidArgumentException');
    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar',
        ['max_bytes_total' => 'not an int',]);
  }

  public function testNegativeMaxBytes() {
    $this->setExpectedException('\InvalidArgumentException');
    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar',
        ['max_bytes_total' => -1,]);
  }

  public function testGsBucketName() {
    $req = new \google\appengine\CreateUploadURLRequest();
    $req->setSuccessPath('http://foo/bar');
    $req->setGsBucketName('my_cool_bucket');

    $resp = new \google\appengine\CreateUploadURLResponse();
    $resp->setUrl('http://upload/to/here');

    $this->apiProxyMock->expectCall('blobstore', 'CreateUploadURL', $req,
        $resp);

    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar',
        ['gs_bucket_name' => 'my_cool_bucket',]);
    $this->assertEquals($upload_url, 'http://upload/to/here');
    $this->apiProxyMock->verify();
  }

  public function testInvalidGsBucketName() {
    $this->setExpectedException('\InvalidArgumentException');
    $upload_url = BlobstoreService::createUploadUrl('http://foo/bar',
        ['gs_bucket_name' => null,]);
  }

  public function testMultipleOptions() {
    $req = new \google\appengine\CreateUploadURLRequest();
    $req->setSuccessPath('http://foo/bar');
    $req->setMaxUploadSizePerBlobBytes(37337);
    $req->setMaxUploadSizeBytes(137337);
    $req->setGsBucketName('my_cool_bucket');

    $resp = new \google\appengine\CreateUploadURLResponse();
    $resp->setUrl('http://upload/to/here');

    $this->apiProxyMock->expectCall('blobstore', 'CreateUploadURL', $req,
        $resp);

    $upload_url = BlobstoreService::createUploadUrl('http://foo/bar',
        ['gs_bucket_name' => 'my_cool_bucket',
         'max_bytes_total' => 137337,
         'max_bytes_per_blob' => 37337]);
    $this->assertEquals($upload_url, 'http://upload/to/here');
    $this->apiProxyMock->verify();
  }

  public function testUrlTooLongException() {
    $req = new \google\appengine\CreateUploadURLRequest();
    $req->setSuccessPath('http://foo/bar');

    $exception = new \google\appengine\runtime\ApplicationError(
        BlobstoreServiceError\ErrorCode::URL_TOO_LONG, 'message');

    $this->setExpectedException('\InvalidArgumentException', '');

    $this->apiProxyMock->expectCall('blobstore', 'CreateUploadURL', $req,
        $exception);

    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar');
  }

  public function testPermissionDeniedException() {
    $req = new \google\appengine\CreateUploadURLRequest();
    $req->setSuccessPath('http://foo/bar');

    $exception = new \google\appengine\runtime\ApplicationError(
        BlobstoreServiceError\ErrorCode::PERMISSION_DENIED, 'message');

    $this->setExpectedException(
        '\google\appengine\api\blobstore\BlobstoreException',
        'Permission Denied');

    $this->apiProxyMock->expectCall('blobstore', 'CreateUploadURL', $req,
        $exception);

    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar');
  }

  public function testInternalErrorException() {
    $req = new \google\appengine\CreateUploadURLRequest();
    $req->setSuccessPath('http://foo/bar');

    $exception = new \google\appengine\runtime\ApplicationError(
        BlobstoreServiceError\ErrorCode::INTERNAL_ERROR, 'message');

    $this->setExpectedException(
        '\google\appengine\api\blobstore\BlobstoreException', '');

    $this->apiProxyMock->expectCall('blobstore', 'CreateUploadURL', $req,
        $exception);

    $upload_url = BlobstoreService::CreateUploadUrl('http://foo/bar');
  }

  public function testInvalidOptions() {
    $this->setExpectedException('\InvalidArgumentException');
    $upload_url = BlobstoreService::createUploadUrl('http://foo/bar',
        ['gs_bucket_name' => 'bucket',
         'foo' => 'bar']);
  }

}

