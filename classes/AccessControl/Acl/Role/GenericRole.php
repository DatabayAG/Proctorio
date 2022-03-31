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

namespace ILIAS\Plugin\Proctorio\AccessControl\Acl\Role;

use ILIAS\Plugin\Proctorio\AccessControl\Acl\Role;

/**
 * Class GenericRole
 * @package ILIAS\Plugin\Proctorio\AccessControl\Acl\Role
 * @author Michael Jansen <mjansen@databay.de>
 */
class GenericRole implements Role
{
    /**
     * Unique id of Role
     * @var string
     */
    protected $roleId = '';

    public function __construct(string $roleId)
    {
        $this->roleId = $roleId;
    }

    public function getRoleId() : string
    {
        return $this->roleId;
    }

    public function __toString()
    {
        return $this->getRoleId();
    }
}
