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

namespace ILIAS\Plugin\Proctorio\UI\Form;

use ilPropertyFormGUI;

/**
 * Interface Bindable
 * @package ILIAS\Plugin\Proctorio\UI\Form
 * @author Michael Jansen <mjansen@databay.de>
 */
interface Bindable
{
    public function bindForm(ilPropertyFormGUI $form) : void;

    public function onFormSaved() : void;

    /**
     * A key value map of form values mapped to the respective element name
     * @return array<string, mixed>
     */
    public function toArray() : array;
}
