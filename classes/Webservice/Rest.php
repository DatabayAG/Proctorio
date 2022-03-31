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

namespace ILIAS\Plugin\Proctorio\Webservice;

use ILIAS\Data\URI;
use ilObjTest;

/**
 * Interface Rest
 * @package ILIAS\Plugin\Proctorio\Webservice
 * @author Michael Jansen <mjansen@databay.de>
 */
interface Rest
{
    public function getLaunchUrl(
        ilObjTest $test,
        URI $testLaunchUrl,
        URI $testUrl
    ) : URI;

    public function getReviewUrl(
        ilObjTest $test,
        URI $testLaunchUrl,
        URI $testUrl
    ) : URI;
}
