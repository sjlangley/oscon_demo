<?php
/**
 * @author vol@google.com (Ivan Volosyuk)
 */

namespace google\appengine\api\users;

require_once "google/appengine/api/users/Error.php";

/**
 * Thrown by User constructor when there's no email argument and no user is
 * logged in.
 */
class UserNotFoundError extends Error {
}
