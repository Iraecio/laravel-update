<?php

declare(strict_types=1);

namespace Iraecio\Updater\SourceRepositoryTypes;

use Exception;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use Iraecio\Updater\Contracts\SourceRepositoryTypeContract;
use Iraecio\Updater\Events\UpdateAvailable;
use Iraecio\Updater\Models\Release;
use Iraecio\Updater\Models\UpdateExecutor;
use Iraecio\Updater\SourceRepositoryTypes\GithubRepositoryTypes\GithubBranchType;
use Iraecio\Updater\SourceRepositoryTypes\GithubRepositoryTypes\GithubTagType;
use Iraecio\Updater\Traits\SupportPrivateAccessToken;
use Iraecio\Updater\Traits\UseVersionFile;

class GithubRepositoryType
{
    use UseVersionFile;
    use SupportPrivateAccessToken;

    const GITHUB_API_URL = 'https://api.github.com';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var UpdateExecutor
     */
    protected $updateExecutor;

    /**
     * Github constructor.
     *
     * @param array          $config
     * @param UpdateExecutor $updateExecutor
     */
    public function __construct(array $config, UpdateExecutor $updateExecutor)
    {
        $this->config = $config;
        $this->updateExecutor = $updateExecutor;

        $this->setAccessToken($this->config['private_access_token']);
    }

    public function create(): SourceRepositoryTypeContract
    {
        if (empty($this->config['repository_vendor']) || empty($this->config['repository_name'])) {
            throw new \Exception('"repository_vendor" or "repository_name" are missing in config file.');
        }

        if ($this->useBranchForVersions()) {
            return resolve(GithubBranchType::class);
        }

        return resolve(GithubTagType::class);
    }

    /**
     * @param Release $release
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function update(Release $release): bool
    {
        return $this->updateExecutor->run($release);
    }

    protected function useBranchForVersions(): bool
    {
        return ! empty($this->config['use_branch']);
    }

    /**
     * @return string
     */
    public function getVersionInstalled(): string
    {
        return (string) config('self-update.version_installed');
    }

    /**
     * Check repository if a newer version than the installed one is available.
     * For updates that are pulled from a commit just checking the SHA won't be enough. So we need to check/compare
     * the commits and dates.
     *
     * @param string $currentVersion
     *
     * @throws InvalidArgumentException
     * @throws Exception
     *
     * @return bool
     */
    public function isNewVersionAvailable($currentVersion = ''): bool
    {
        $version = $currentVersion ?: $this->getVersionInstalled();

        if (! $version) {
            throw new InvalidArgumentException('No currently installed version specified.');
        }

        $versionAvailable = $this->getVersionAvailable();

        if (version_compare($version, $versionAvailable, '<')) {
            if (! $this->versionFileExists()) {
                $this->setVersionFile($versionAvailable);
            }
            event(new UpdateAvailable($versionAvailable));

            return true;
        }

        return false;
    }
}
