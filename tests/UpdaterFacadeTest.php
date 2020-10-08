<?php

declare(strict_types=1);

namespace Iraecio\Updater\Tests;

use Iraecio\Updater\UpdaterFacade;
use Iraecio\Updater\UpdaterManager;

final class UpdaterFacadeTest extends TestCase
{
    /** @test */
    public function it_can_use_the_facade(): void
    {
        $this->assertInstanceOf(
            UpdaterManager::class,
            UpdaterFacade::getFacadeRoot()
        );
    }
}
