<?php

namespace JeffersonGoncalves\LaravelZero\Credentials\Tests\Fixtures;

use JeffersonGoncalves\LaravelZero\Credentials\AbstractAuthService;
use JeffersonGoncalves\LaravelZero\Credentials\CredentialsContract;

class FakeAuthService extends AbstractAuthService
{
    protected function appName(): string
    {
        return 'fake-cli';
    }

    protected function fromArray(array $data): CredentialsContract
    {
        return FakeCredentials::fromArray($data);
    }
}
