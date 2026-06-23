<?php

namespace JeffersonGoncalves\LaravelZero\Credentials\Tests\Fixtures;

use JeffersonGoncalves\LaravelZero\Credentials\CredentialsContract;

class FakeCredentials implements CredentialsContract
{
    public function __construct(
        public readonly string $username,
        public readonly string $apiToken,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            username: (string) ($data['username'] ?? ''),
            apiToken: (string) ($data['api_token'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'api_token' => $this->apiToken,
        ];
    }

    public function isValid(): bool
    {
        return $this->username !== '' && $this->apiToken !== '';
    }
}
