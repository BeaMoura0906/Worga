<?php

namespace Worga\src\Classes;

class FinTransCategories
{
    public const CATEGORY_TO_BE_DEBITED = 'to_be_debited';
    public const CATEGORY_DEBIT = 'debit';
    public const CATEGORY_CREDIT = 'credit';

    /**
     * Retrieves the list of financial transaction categories.
     *
     * @return array The list of financial transaction categories.
     */
    public static function getFinTransCategories(): array
    {
        return [
            self::CATEGORY_TO_BE_DEBITED,
            self::CATEGORY_DEBIT,
            self::CATEGORY_CREDIT
        ];
    }

    /**
     * Retrieves the list of financial transaction categories in French.
     * 
     * @return array The list of financial transaction categories in French.
     */
    public static function getFinTransCategoriesFr(): array
    {
        return [
            self::CATEGORY_TO_BE_DEBITED => 'A débiter',
            self::CATEGORY_DEBIT => 'Débit',
            self::CATEGORY_CREDIT => 'Crédit'
        ];
    }
    
    /**
     * Get the English financial transaction category from its French category.
     *
     * @param string 
     * @return string|null 
     */
    public static function getRoleInEnglish(string $finTransCategoryFr): ?string
    {
        $finTransCategoryFrProcessed = strtolower($finTransCategoryFr);
        $finTransCategoryFrProcessed = str_replace(' ', '', $finTransCategoryFrProcessed);

        switch ($finTransCategoryFrProcessed) {
            case 'adébiter':
                return self::CATEGORY_TO_BE_DEBITED;
            case 'débit':
                return self::CATEGORY_DEBIT;
            case 'crédit':
                return self::CATEGORY_CREDIT;
            default:
                return null;
        }
    }
}