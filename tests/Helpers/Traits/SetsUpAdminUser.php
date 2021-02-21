<?php

namespace Tests\Helpers\Traits;

use App\Enums\Gender;
use App\Models\AuthUser;
use App\Models\User;
use Tests\Helpers\Traits\SetsUpUser;

trait SetsUpAdminUser
{
    use SetsUpUser;

    /**
     * Sets up admin user and acts as user.
     *
     * @return void
     */
    protected function setUpAdminUser()
    {
        $this->setUpUser();
    }
}
