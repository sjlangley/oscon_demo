<?php
/**
 * Blobstore Service allows the user to create and serve blobs.
 *
 * @author slangley@google.com (Stuart Langley)
 */

namespace google\appengine\api\blobstore;

use \google\appengine\BlobstoreServiceError\ErrorCode;
use \google\appengine\CreateUploadURLRequest;
use \google\appengine\CreateUploadURLResponse;
use \google\appengine\runtime\ApiProxy;
use \google\appengine\runtime\ApplicationError;

require_once 'google/appengine/api/blobstore/blobstore_service_pb.php';
require_once 'google/appengine/api/blobstore/BlobstoreException.php';
require_once 'google/appengine/runtime/ApiProxy.php';
require_once 'google/appengine/runtime/ApplicationError.php';



class BlobstoreService {
  static $default_options = ['gs_bucket_name', 'max_bytes_per_blob',
      'max_bytes_total'];
  /**
   * Create an absolute URL that can be used by a user to asynchronously upload
   * a large blob. Upon completion of the upload, a callback is made to the
   * specified URL.
   *
   * @param string $success_path A relative URL which will be invoked after the
   * user successfully uploads a blob.
   * @param mixed[] $options A key value pair array of upload options. Valid
   * options are:
   * - max_bytes_per_blob: an integer value of the largest size that any one
   *   uploaded blob may be. Default value: unlimited.
   * - max_bytes_total: an integer value that is the total size that sum of all
   *   uploaded blobs may be. Default value: unlimited.
   * - gs_bucket_name: a string that is the name of a Google Cloud Storage
   *   bucket that the blobs should be uploaded to. Not specifying a value
   *   will result in the blob being uploaded to the application's default
   *   bucket, if set, or to the blobstore.
   *
   * @return string The upload URL.
   *
   * @throws InvalidArgumentException If $success_path is not valid, or one of
   * the options is not valid.
   * @throws BlobstoreException Thrown when there is a failure using the
   * blobstore service.
   */
  public static function CreateUploadUrl($success_path, $options=array()) {
    $req = new CreateUploadURLRequest();
    $resp = new CreateUploadURLResponse();

    if (!is_string($success_path)) {
      throw new \InvalidArgumentException('$success_path must be a string');
    }

    $req->setSuccessPath($success_path);

    if (array_key_exists('max_bytes_per_blob', $options)) {
      $val = $options['max_bytes_per_blob'];
      if (!is_int($val)) {
        throw new \InvalidArgumentException(
            'max_bytes_per_blob must be an integer');
      }
      if ($val < 1) {
        throw new \InvalidArgumentException(
            'max_bytes_per_blob must be positive.');
      }
      $req->setMaxUploadSizePerBlobBytes($val);
    }

    if (array_key_exists('max_bytes_total', $options)) {
      $val = $options['max_bytes_total'];
      if (!is_int($val)) {
        throw new \InvalidArgumentException(
            'max_bytes_total must be an integer');
      }
      if ($val < 1) {
        throw new \InvalidArgumentException(
            'max_bytes_total must be positive.');
      }
      $req->setMaxUploadSizeBytes($val);
    }

    if (array_key_exists('gs_bucket_name', $options)) {
      $val = $options['gs_bucket_name'];
      if (!is_string($val)) {
        throw new \InvalidArgumentException('gs_bucket_name must be a string');
      }
      $req->setGsBucketName($val);
    }

    $extra_options = array_diff(array_keys($options), $default_options);

    if (!empty($extra_options)) {
      throw new \InvalidArgumentException('Invalid options supplied: ' .
          implode(',', $extra_options));
    }

    try {
      ApiProxy::makeSyncCall('blobstore', 'CreateUploadURL', $req, $resp);
    } catch (ApplicationError $e) {
      throw ApplicationErrorToException($e);
    }
    return $resp->getUrl();
  }


  private static function ApplicationErrorToException($error) {
    switch($error->getApplicationError()) {
      case ErrorCode::URL_TOO_LONG:
        return new \InvalidArgumentException(
            'The upload URL supplied was too long.');
      case ErrorCode::PERMISSION_DENIED:
        return new BlobstoreException('Permission Denied');
      case ErrorCode::ARGUMENT_OUT_OF_RANGE:
        return new \InvalidArgumentException($error->getMessage());
      default:
        return new BlobstoreException(
            'Error Code: ' . $error->getApplicationError());
    }
  }
}
