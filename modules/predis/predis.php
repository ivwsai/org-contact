<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once MODPATH . '/predis/Autoloader.php';
Predis\Autoloader::register();

function redis_version($info)
{
    if (isset($info['Server']['redis_version'])) {
        return $info['Server']['redis_version'];
    } elseif (isset($info['redis_version'])) {
        return $info['redis_version'];
    } else {
        return 'unknown version';
    }
}

class Module_Predis extends Predis\Client
{
    public function __construct($parameters = null, $options = null)
    {
        parent::__construct($parameters, $options);
    }
}

