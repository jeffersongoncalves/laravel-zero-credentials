<?php

use JeffersonGoncalves\LaravelZero\Credentials\AuthenticationException;

it('uses a generic default message', function () {
    expect((new AuthenticationException)->getMessage())->toBe('Not authenticated.');
});

it('accepts a custom message', function () {
    expect((new AuthenticationException("Run 'bb auth:save' first."))->getMessage())
        ->toBe("Run 'bb auth:save' first.");
});

it('is a runtime exception', function () {
    expect(new AuthenticationException)->toBeInstanceOf(RuntimeException::class);
});
