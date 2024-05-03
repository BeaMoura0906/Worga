<?php

namespace Worga\src\Classes;

/**
 * Class Role
 * Util class for user roles.
 */
class Role
{
    /** Properties ROLE_* */
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

    /**
     * Retrieves the list of roles in French.
     * 
     * @return array The list of roles in French.
     */
    public static function getRolesInFrench(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_EDITOR => 'Editeur',
            self::ROLE_VISITOR => 'Visiteur'
        ];
    }

    /**
     * Retrieves the list of roles in French without the admin role.
     * 
     * @return array The list of roles in French without the admin role.
     */
    public static function getRolesInFrenchWithoutAdmin(): array
    {
        return [
            self::ROLE_EDITOR => 'Editeur',
            self::ROLE_VISITOR => 'Visiteur'
        ];
    }
    
    /**
     * Get the English role from the French role.
     *
     * @param string $roleInFrench The role in French.
     * @return string|null The role in English, or null if not found.
     */
    public static function getRoleInEnglish(string $roleInFrench): ?string
    {
        switch (strtolower($roleInFrench)) {
            case 'administrateur':
                return self::ROLE_ADMIN;
            case 'editeur':
                return self::ROLE_EDITOR;
            case 'visiteur':
                return self::ROLE_VISITOR;
            default:
                return null;
        }
    }
}