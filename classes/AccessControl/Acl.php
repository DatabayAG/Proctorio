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

use ILIAS\Plugin\Proctorio\AccessControl\Acl\Role;

/**
 * Class Acl
 * @package ILIAS\Plugin\Proctorio\AccessControl
 * @author  Michael Jansen <mjansen@databay.de>
 */
interface Acl
{
    public function isAllowed(string $role, string $resource, string $privilege) : bool;

    /**
     * @return Role[]
     */
    public function getRoles() : array;
}
