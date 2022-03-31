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

namespace ILIAS\Plugin\Proctorio\AccessControl\Acl\Resource;

use ILIAS\Plugin\Proctorio\AccessControl\Acl\Resource;

/**
 * Class GenericResource
 * @package ILIAS\Plugin\Proctorio\AccessControl\Acl\Resource
 * @author  Michael Jansen <mjansen@databay.de>
 */
class GenericResource implements Resource
{
    /**
     * Unique id of Role
     * @var string
     */
    protected $resourceId = '';

    public function __construct(string $roleId)
    {
        $this->resourceId = $roleId;
    }

    public function getResourceId() : string
    {
        return $this->resourceId;
    }

    public function __toString()
    {
        return $this->getResourceId();
    }
}
