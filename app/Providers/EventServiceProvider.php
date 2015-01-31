<?php

/*
 * This file is part of StyleCI.
 *
 * (c) Graham Campbell <graham@mineuk.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StyleCI\StyleCI\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

/**
 * This is the event service provider class.
 *
 * @author Graham Campbell <graham@mineuk.com>
 */
class EventServiceProvider extends EventServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [];
}
