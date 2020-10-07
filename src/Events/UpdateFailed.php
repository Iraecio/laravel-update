<?php

namespace Iraecio\Updater\Events;

use Iraecio\Updater\Models\Release;

class UpdateFailed
{
    protected $release;

    public function __construct(Release $release)
    {
        $this->release = $release;
    }
}
