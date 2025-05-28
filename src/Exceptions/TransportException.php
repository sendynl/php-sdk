<?php

namespace Sendy\Api\Exceptions;

/**
 * Indicates that an HTTP request did not result in a proper response, e.g. a network error, timeout, or driver error.
 */
class TransportException extends SendyException
{
}
