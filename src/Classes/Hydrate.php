<?php

namespace Worga\src\Classes;

use Worga\src\Model\Entity\User;
use Worga\src\Model\Entity\Client;
use Worga\src\Model\Entity\Account;
use Worga\src\Model\UserManager;
use Worga\src\Model\ClientManager;
use Worga\src\Model\AccountManager;

class Hydrate 
{
    // Properties for the Hydrate class
    private UserManager $userManager;
    private ClientManager $clientManager;
    private AccountManager $accountManager;

    public function __construct()
    {
        $this->userManager = new UserManager();
        $this->clientManager = new ClientManager();
        $this->accountManager = new AccountManager();
    }

    /**
     * Hydrate method to set object properties based on provided data.
     *
     * @param array $data Data to hydrate the object.
     * @param object $object The object to hydrate.
     */
    public function hydrate(array $data, $object)
    {
        foreach ($data as $key => $value) {
            if ($key === 'user' || $key === 'user_id') {
                // Set user property to new User object
                $user = $this->userManager->getUserById($value) ?? new User(['id' => $value]);
                $object->setUser($user);
            } else if ($key === 'client' || $key === 'client_id') {
                // Set client property to new Client object
                $client = $this->clientManager->getClientById($value) ?? new Client(['id' => $value]);
                $object->setClient($client);
            } else if ($key === 'account' || $key === 'account_id') {
                // Set account property to new Account object
                $account = $this->accountManager->getAccountById($value) ?? new Account(['id' => $value]);
                $object->setAccount($account);
            } else {         
                // Convert snake_case key to camelCase and set property using setter method
                $method = 'set' . $this->convertSnakeCaseToCamelCase($key);

                if (method_exists($object, $method)) {
                    $object->$method($value);
                }
            }                
        }
    }

    /**
     * Convert snake_case to CamelCase.
     *
     * @param string $snakeCase Snake case string.
     * @return string Camel case string.
     */
    private function convertSnakeCaseToCamelCase($snakeCase)
    {
        return str_replace('_', '', ucwords($snakeCase, '_'));
    }

}
