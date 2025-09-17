<?php

namespace Sendy\Api\Exceptions;

/**
 * Represents an HTTP 4xx error that occurred during a request to the Sendy API.
 */
class ClientException extends HttpException implements SendyException {}
