<?php

namespace App\Exceptions;

use Exception;

/**
 * A cart operation the customer should be told about in plain language -
 * insufficient stock, a sold-out variant, a disallowed mix. The message is
 * shown directly to the customer, so write it for them.
 */
class CartException extends Exception
{
}
