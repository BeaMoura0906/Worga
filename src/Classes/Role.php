<?php

namespace Worga\src\Classes;

class Role
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_VISITOR = 'visitor';

    /**
     * Retrieves the list of roles.
     *
     * @return array The list of roles.
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
            self::ROLE_VISITOR
        ];
    }
}