<?php

namespace JeffersonGoncalves\LaravelZero\Credentials;

/**
 * Template method base for storing CLI credentials locally as JSON.
 *
 * Persists to {@see getConfigPath()} (default ~/.<appName>/config.json),
 * creating the directory with 0700 and the file with 0600 permissions, and
 * caches the loaded credentials in memory.
 *
 * Subclasses must define {@see appName()} and {@see fromArray()}. The config
 * directory can be customized by overriding {@see configDir()}.
 */
abstract class AbstractAuthService
{
    private ?CredentialsContract $credentials = null;

    /**
     * Application name, used as the default config directory segment
     * (e.g. 'bb-cli' => ~/.bb-cli).
     */
    abstract protected function appName(): string;

    /**
     * Rebuild the concrete credentials DTO from its stored array form.
     *
     * @param  array<string, mixed>  $data
     */
    abstract protected function fromArray(array $data): CredentialsContract;

    /**
     * Persist credentials to disk and prime the in-memory cache.
     */
    public function save(CredentialsContract $credentials): void
    {
        $configDir = $this->getConfigDir();

        if (! is_dir($configDir)) {
            mkdir($configDir, 0700, true);
        }

        $configPath = $this->getConfigPath();
        file_put_contents($configPath, json_encode($credentials->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        chmod($configPath, 0600);

        $this->credentials = $credentials;
    }

    /**
     * Load credentials from cache or disk. Returns null when the file is
     * missing, unreadable, malformed, or holds invalid credentials.
     */
    public function load(): ?CredentialsContract
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $configPath = $this->getConfigPath();

        if (! file_exists($configPath)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($configPath), true);

        if (! is_array($data)) {
            return null;
        }

        $credentials = $this->fromArray($data);

        if (! $credentials->isValid()) {
            return null;
        }

        $this->credentials = $credentials;

        return $this->credentials;
    }

    /**
     * Whether valid credentials are currently stored.
     */
    public function isAuthenticated(): bool
    {
        return $this->load() !== null;
    }

    /**
     * Remove stored credentials from disk and clear the in-memory cache.
     */
    public function forget(): void
    {
        $configPath = $this->getConfigPath();

        if (file_exists($configPath)) {
            unlink($configPath);
        }

        $this->credentials = null;
    }

    /**
     * Absolute path to the credentials JSON file.
     */
    public function getConfigPath(): string
    {
        return $this->getConfigDir().DIRECTORY_SEPARATOR.'config.json';
    }

    /**
     * Absolute path to the directory holding the credentials file.
     */
    public function getConfigDir(): string
    {
        return $this->configDir();
    }

    /**
     * Resolve the config directory. Override to support XDG or custom paths.
     * Defaults to ~/.<appName> for compatibility with existing CLIs.
     */
    protected function configDir(): string
    {
        return $this->getHomeDir().DIRECTORY_SEPARATOR.'.'.$this->appName();
    }

    /**
     * Resolve the current user's home directory across platforms.
     */
    public function getHomeDir(): string
    {
        return match (true) {
            isset($_SERVER['HOME']) => $_SERVER['HOME'],
            isset($_SERVER['USERPROFILE']) => $_SERVER['USERPROFILE'],
            isset($_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH']) => $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'],
            default => '~',
        };
    }
}
