<?php

use JeffersonGoncalves\LaravelZero\Credentials\Tests\Fixtures\FakeAuthService;
use JeffersonGoncalves\LaravelZero\Credentials\Tests\Fixtures\FakeCredentials;

beforeEach(function () {
    $this->home = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lz-credentials-'.uniqid();
    mkdir($this->home, 0700, true);

    putenv('HOME='.$this->home);
    $_SERVER['HOME'] = $this->home;
    unset($_SERVER['USERPROFILE'], $_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH']);

    $this->service = new FakeAuthService;
});

afterEach(function () {
    $configPath = $this->service->getConfigPath();
    if (file_exists($configPath)) {
        unlink($configPath);
    }
    if (is_dir($this->service->getConfigDir())) {
        rmdir($this->service->getConfigDir());
    }
    if (is_dir($this->home)) {
        rmdir($this->home);
    }
});

it('points the config dir at the home directory by app name', function () {
    expect($this->service->getConfigDir())
        ->toBe($this->home.DIRECTORY_SEPARATOR.'.fake-cli')
        ->and($this->service->getConfigPath())
        ->toBe($this->home.DIRECTORY_SEPARATOR.'.fake-cli'.DIRECTORY_SEPARATOR.'config.json');
});

it('saves and loads credentials in a roundtrip', function () {
    $this->service->save(new FakeCredentials('alice', 'secret-token'));

    $loaded = (new FakeAuthService)->load();

    expect($loaded)->toBeInstanceOf(FakeCredentials::class)
        ->and($loaded->username)->toBe('alice')
        ->and($loaded->apiToken)->toBe('secret-token');
});

it('writes the config file with 0600 permissions', function () {
    $this->service->save(new FakeCredentials('alice', 'secret-token'));

    $perms = substr(sprintf('%o', fileperms($this->service->getConfigPath())), -4);

    expect(file_exists($this->service->getConfigPath()))->toBeTrue();

    if (PHP_OS_FAMILY !== 'Windows') {
        expect($perms)->toBe('0600');
    }
});

it('reports not authenticated before save and authenticated after', function () {
    expect($this->service->isAuthenticated())->toBeFalse();

    $this->service->save(new FakeCredentials('alice', 'secret-token'));

    expect($this->service->isAuthenticated())->toBeTrue();
});

it('forgets credentials by removing the file', function () {
    $this->service->save(new FakeCredentials('alice', 'secret-token'));
    expect(file_exists($this->service->getConfigPath()))->toBeTrue();

    $this->service->forget();

    expect(file_exists($this->service->getConfigPath()))->toBeFalse()
        ->and((new FakeAuthService)->isAuthenticated())->toBeFalse();
});

it('returns null when stored data is invalid', function () {
    mkdir($this->service->getConfigDir(), 0700, true);
    file_put_contents($this->service->getConfigPath(), json_encode(['username' => 'alice', 'api_token' => '']));

    expect((new FakeAuthService)->load())->toBeNull()
        ->and((new FakeAuthService)->isAuthenticated())->toBeFalse();
});

it('returns null when the file holds malformed json', function () {
    mkdir($this->service->getConfigDir(), 0700, true);
    file_put_contents($this->service->getConfigPath(), 'not-json');

    expect((new FakeAuthService)->load())->toBeNull();
});

it('returns null when no credentials file exists', function () {
    expect($this->service->load())->toBeNull();
});
