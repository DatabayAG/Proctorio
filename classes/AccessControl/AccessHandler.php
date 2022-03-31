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

namespace ILIAS\Plugin\Proctorio\AccessControl;

use ilObjTest;
use ilObjUser;

/**
 * Interface AccessHandler
 * @package ILIAS\Plugin\Proctorio\AccessControl
 * @author  Michael Jansen <mjansen@databay.de>
 */
interface AccessHandler
{
    public function withActor(ilObjUser $actor) : self;

    public function mayTakeTests(ilObjTest $test) : bool;

    public function mayReadTestReviews(ilObjTest $test) : bool;

    public function mayReadTestSettings(ilObjTest $test) : bool;

    public function mayWriteTestSettings(ilObjTest $test) : bool;
}
