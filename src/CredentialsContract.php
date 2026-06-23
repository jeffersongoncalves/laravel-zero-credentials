<?php

namespace JeffersonGoncalves\LaravelZero\Credentials;

/**
 * Contract for a typed credentials value object.
 *
 * Each CLI implements its own concrete DTO with its own fields
 * (e.g. username/apiToken for Bitbucket; server/username/apiToken/authType
 * for Jira) while sharing the same persistence pipeline provided by
 * {@see AbstractAuthService}.
 */
interface CredentialsContract
{
    /**
     * Rebuild the credentials object from its array representation.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static;

    /**
     * Serialize the credentials to a plain array for JSON storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Whether the credentials hold the minimum data required to authenticate.
     */
    public function isValid(): bool;
}
