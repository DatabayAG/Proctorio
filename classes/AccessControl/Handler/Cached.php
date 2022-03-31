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

namespace ILIAS\Plugin\Proctorio\AccessControl\Handler;

use ILIAS\Plugin\Proctorio\AccessControl;
use ilObjTest;
use ilObjUser;

/**
 * Class Cached
 * @package ILIAS\Plugin\Proctorio\AccessControl\Handler
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Cached implements AccessControl\AccessHandler
{
    /** @var AccessControl\AccessHandler */
    private $origin;
    /** @var array<string, bool> */
    private $cache = [];

    public function __construct(AccessControl\AccessHandler $origin)
    {
        $this->origin = $origin;
    }

    public function withActor(ilObjUser $actor) : AccessControl\AccessHandler
    {
        $clone = clone $this;
        $clone->origin = $clone->origin->withActor($actor);
        $clone->cache = [];

        return $clone;
    }

    public function mayTakeTests(ilObjTest $test) : bool
    {
        return $this->cache[__METHOD__] ?? ($this->cache[__METHOD__] = $this->origin->mayTakeTests($test));
    }

    public function mayReadTestReviews(ilObjTest $test) : bool
    {
        return $this->cache[__METHOD__] ?? ($this->cache[__METHOD__] = $this->origin->mayReadTestReviews($test));
    }

    public function mayReadTestSettings(ilObjTest $test) : bool
    {
        return $this->cache[__METHOD__] ?? ($this->cache[__METHOD__] = $this->origin->mayReadTestSettings($test));
    }

    public function mayWriteTestSettings(ilObjTest $test) : bool
    {
        return $this->cache[__METHOD__] ?? ($this->cache[__METHOD__] = $this->origin->mayWriteTestSettings($test));
    }
}
