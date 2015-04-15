<?php

namespace Masterflow\Controller\SimpleUser;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use SimpleUser\User;


/**
 * A simple User model.
 *
 * @package SimpleUser
 */
class UserNexecom extends User
{

    /**
     * Returns the roles granted to the user. Note that all users have the ROLE_USER role.
     *
     * @return array A list of the user's roles.
     */
    public function getRoles()
    {
        $roles = $this->roles;

        // Every user must have at least one role, per Silex security docs.
        if(empty($roles)){
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }
}