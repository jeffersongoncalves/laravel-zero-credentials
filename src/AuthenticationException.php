<?php

namespace JeffersonGoncalves\LaravelZero\Credentials;

use RuntimeException;

/**
 * Thrown when an authenticated operation is attempted without valid stored
 * credentials. The message is parametrizable so each CLI can point the user
 * at its own login command (e.g. "Run 'bb auth:save' first.").
 */
class AuthenticationException extends RuntimeException
{
    public function __construct(string $message = 'Not authenticated.')
    {
        parent::__construct($message);
    }
}
