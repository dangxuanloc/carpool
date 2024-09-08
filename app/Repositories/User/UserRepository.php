<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Retrieves the model associated with this function.
     *
     * @return string The fully qualified class name of the model.
     */
    public function getModel()
    {
        return User::class;
    }
}
