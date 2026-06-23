<div class="filament-hidden">

![laravel-zero-credentials](https://raw.githubusercontent.com/jeffersongoncalves/laravel-zero-credentials/main/art/jeffersongoncalves-laravel-zero-credentials.png)

</div>

# laravel-zero-credentials

Reusable base for storing CLI authentication credentials locally in JSON, with
secure file permissions and a typed credentials contract.

Extracted from the `bb-cli` and `jira-cli` Laravel Zero tools, whose
`AuthService` classes were ~90% identical. This package provides the shared
persistence pipeline so each CLI only declares its own credential fields.

## Why

Every CLI that authenticates against an API needs to:

- resolve the user's home directory across platforms,
- write credentials to `~/.<app>/config.json` (or a custom/XDG path),
- create the directory `0700` and the file `0600`,
- cache the loaded credentials in memory,
- validate stored data before trusting it.

This package does all of that. The CLI supplies only a typed DTO and two small
methods.

## Installation

```bash
composer require jeffersongoncalves/laravel-zero-credentials
```

Requires PHP `^8.2`. No other dependencies.

## Usage

### 1. Implement the credentials contract

```php
use JeffersonGoncalves\LaravelZero\Credentials\CredentialsContract;

final class Credentials implements CredentialsContract
{
    public function __construct(
        public readonly string $username,
        public readonly string $apiToken,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            username: $data['username'] ?? '',
            apiToken: $data['api_token'] ?? '',
        );
    }

    public function toArray(): array
    {
        return ['username' => $this->username, 'api_token' => $this->apiToken];
    }

    public function isValid(): bool
    {
        return $this->username !== '' && $this->apiToken !== '';
    }
}
```

### 2. Extend the abstract auth service

```php
use JeffersonGoncalves\LaravelZero\Credentials\AbstractAuthService;
use JeffersonGoncalves\LaravelZero\Credentials\CredentialsContract;

final class AuthService extends AbstractAuthService
{
    protected function appName(): string
    {
        return 'bb-cli'; // => ~/.bb-cli/config.json
    }

    protected function fromArray(array $data): CredentialsContract
    {
        return Credentials::fromArray($data);
    }
}
```

### 3. Use it

```php
$auth = new AuthService;

$auth->save(new Credentials('alice', 'secret-token'));

$auth->isAuthenticated();          // true
$auth->load()->username;           // 'alice'
$auth->getConfigPath();            // /home/alice/.bb-cli/config.json

$auth->forget();                   // deletes the file
```

### Guarding commands

```php
use JeffersonGoncalves\LaravelZero\Credentials\AuthenticationException;

if (! $auth->isAuthenticated()) {
    throw new AuthenticationException("Run 'bb auth:save' first.");
}
```

### Custom / XDG config directory

Override `configDir()` to change where credentials live (the default is
`~/.<appName>`):

```php
protected function configDir(): string
{
    $base = getenv('XDG_CONFIG_HOME') ?: $this->getHomeDir().'/.config';

    return $base.'/bb-cli';
}
```

## Public API

| Class | Description |
|-------|-------------|
| `CredentialsContract` | Interface for a typed credentials DTO: `fromArray()`, `toArray()`, `isValid()`. |
| `AbstractAuthService` | Template method base: `save()`, `load()`, `isAuthenticated()`, `forget()`, `getConfigPath()`, `getConfigDir()`, `getHomeDir()`. Subclass defines `appName()` and `fromArray()`; override `configDir()` to customize the path. |
| `AuthenticationException` | `RuntimeException` with a parametrizable message (default `"Not authenticated."`). |

## License

MIT
