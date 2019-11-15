<?php
/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian.feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CaptainHook\App\Runner\Config\Change;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Runner\Config\Change;
use CaptainHook\App\Runner\Config\Setup\Advanced;

/**
 * Class AddAction
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhookphp/captainhook
 * @since   Class available since Release 4.2.0
 */
class DisableHook extends Hook
{
    /**
     * Apply changes to the given config
     *
     * @param  \CaptainHook\App\Config $config
     * @throws \Exception
     */
    public function applyTo(Config $config)
    {
        $config->getHookConfig($this->hookToChange)->setEnabled(false);
    }
}
