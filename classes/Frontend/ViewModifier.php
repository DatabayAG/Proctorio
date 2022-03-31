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

namespace ILIAS\Plugin\Proctorio\Frontend;

/**
 * Interface ViewModifier
 * @package ILIAS\Plugin\Proctorio\Frontend
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ViewModifier
{
    /**
     * @param string $component
     * @param string $part
     * @param array $parameters
     * @return bool
     */
    public function shouldModifyHtml(string $component, string $part, array $parameters) : bool;

    /**
     * @param string $component
     * @param string $part
     * @param array $parameters
     * @return array A `\ilUIHookPluginGUI::getHtml()` compatible array
     */
    public function modifyHtml(string $component, string $part, array $parameters) : array;

    /**
     * @param string $component
     * @param string $part
     * @param array $parameters
     * @return bool
     */
    public function shouldModifyGUI(string $component, string $part, array $parameters) : bool;

    /**
     * @param string $component
     * @param string $part
     * @param array $parameters
     * @return void
     */
    public function modifyGUI(string $component, string $part, array $parameters) : void;
}
