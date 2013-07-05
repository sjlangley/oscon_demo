<?php

namespace google\appengine\api\users;

require_once "google/appengine/api/users/Error.php";

/**
 * Thrown by APIProxy when API calls are temporarily disabled.
 */
class NotAllowedError extends Error {
}
