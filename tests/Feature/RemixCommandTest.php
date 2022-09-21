<?php

it('fails on non Laravel application', function () {
    chdir(__DIR__.'/../../tests-output/non');
    $this->artisan('run')->assertExitCode(1);
});

it('fails on existing app with vite build', function () {
    chdir(__DIR__.'/../../tests-output/existing');
    $this->artisan('run')->assertExitCode(1);
});

it('successfully remixs a fresh app', function () {
    chdir(__DIR__.'/../../tests-output/fresh');
    $this->artisan('run')->assertExitCode(0);
});
