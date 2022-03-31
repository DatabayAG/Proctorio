<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\Plugin\Proctorio\Frontend\Controller;

/**
 * Class Error
 * @package ILIAS\Plugin\Proctorio\Frontend\Controller
 * @author Michael Jansen <mjansen@databay.de>
 */
class Error extends Base
{
    public function getDefaultCommand() : string
    {
        return 'showCmd';
    }

    protected function init() : void
    {
        $this->pageTemplate->loadStandardTemplate();
        parent::init();
    }

    public function showCmd() : string
    {
        return $this->uiRenderer->render([
            $this->uiFactory->messageBox()->failure(
                $this->getCoreController()->getPluginObject()->txt('controller_not_found')
            )
        ]);
    }
}
