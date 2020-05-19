<?php

namespace MichaelJennings\RouteGuards\Tests\Unit;

use MichaelJennings\RouteGuards\RouteGuardServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (method_exists($this, 'loadFixtures')) {
            $this->loadFixtures();
        }
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app)
    {
        return [
            RouteGuardServiceProvider::class,
        ];
    }
}
