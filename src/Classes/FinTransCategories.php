<?php

namespace Worga\src\Classes;

/**
 * Class FinTransCategories
 * Util class for financial transaction categories.
 */
class FinTransCategories
{
    /** Properties CATEGORY_* */
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
            self::CATEGORY_TO_BE_DEBITED => 'À débiter | Devis',
            self::CATEGORY_DEBIT => 'Débit | Facture',
            self::CATEGORY_CREDIT => 'Crédit | Paiement'
        ];
    }

    /**
     * Get the French financial transaction category from its English category.
     * 
     * @param string $finTransCategory The financial transaction category in English.
     * @return string|null The financial transaction category in French, or null if not found.
     */
    public static function getFinTransCategoryFr(string $finTransCategory): string
    {
        switch ($finTransCategory) {
            case self::CATEGORY_TO_BE_DEBITED:
                return 'À débiter | Devis';
            case self::CATEGORY_DEBIT:
                return 'Débit | Facture';
            case self::CATEGORY_CREDIT:
                return 'Crédit | Paiement';
            default:
                return '';
        }
    }
    
    /**
     * Get the English financial transaction category from its French category.
     *
     * @param string $finTransCategoryFr The financial transaction category in French.
     * @return string|null The financial transaction category in English, or null if not found.
     */
    public static function getRoleInEnglish(string $finTransCategoryFr): ?string
    {
        switch ($finTransCategoryFr) {
            case 'À débiter | Devis':
                return self::CATEGORY_TO_BE_DEBITED;
            case 'Débit | Facture':
                return self::CATEGORY_DEBIT;
            case 'Crédit | Paiement':
                return self::CATEGORY_CREDIT;
            default:
                return null;
        }
    }
}