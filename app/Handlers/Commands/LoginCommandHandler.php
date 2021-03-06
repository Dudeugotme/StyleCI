<?php

/*
 * This file is part of StyleCI.
 *
 * (c) Graham Campbell <graham@mineuk.com>
 * (c) Joseph Cohen <joseph.cohen@dinkbit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StyleCI\StyleCI\Handlers\Commands;

use StyleCI\StyleCI\Commands\LoginCommand;
use StyleCI\StyleCI\Events\UserHasSignedUpEvent;
use StyleCI\StyleCI\Models\User;

/**
 * This is the login command handler class.
 *
 * @author Graham Campbell <graham@mineuk.com>
 * @author Joseph Cohen <joseph.cohen@dinkbit.com>
 */
class LoginCommandHandler
{
    /**
     * Handle the login command.
     *
     * @param \StyleCI\StyleCI\Commands\LoginCommand $command
     *
     * @return \StyleCI\StyleCI\Models\User
     */
    public function handle(LoginCommand $command)
    {
        $user = User::find($command->getId());

        if (!$user) {
            $new = true;
            $user = new User();
        }

        $user->id = $command->getId();
        $user->name = $command->getName();
        $user->email = $command->getEmail();
        $user->username = $command->getUsername();
        $user->access_token = $command->getToken();

        $user->save();

        if (isset($new)) {
            event(new UserHasSignedUpEvent($user));
        }

        return $user;
    }
}
