<?php

declare(strict_types=1);

namespace Iraecio\Updater\Tests\Commands;

use Iraecio\Updater\Commands\CheckForUpdate;
use Iraecio\Updater\SourceRepositoryTypes\GithubRepositoryType;
use Iraecio\Updater\SourceRepositoryTypes\GithubRepositoryTypes\GithubTagType;
use Iraecio\Updater\Tests\TestCase;
use GuzzleHttp\Client;

final class CheckUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetDownloadDir();
    }

    /** @test */
    public function it_can_run_check_update_command_without_new_version_available(): void
    {
        $client = $this->getMockedClient([
            $this->getResponse200Type('tag'),
            $this->getResponse200ZipFile(),
        ]);
        $this->app->instance(Client::class, $client);

        /** @var GithubTagType $github */
        $github = (resolve(GithubRepositoryType::class))->create();

        $github->deleteVersionFile();

        config(['self-update.version_installed' => '3.5']);

        $this->artisan(CheckForUpdate::class)
             ->expectsOutput('There\'s no new version available.')
             ->assertExitCode(0);
    }

    /** @test */
    public function it_can_run_check_update_command_with_new_version_available(): void
    {
        /** @var GithubTagType $github */
        $github = (resolve(GithubRepositoryType::class))->create();
        $github->setVersionFile('v3.5');

        config(['self-update.version_installed' => 'v1.0']);

        $this->artisan(CheckForUpdate::class)
             ->expectsOutput('A new version [v3.5] is available.')
             ->assertExitCode(0);
    }
}
