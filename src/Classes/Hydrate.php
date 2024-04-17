<?php

namespace Worga\src\Classes;

use Worga\src\Model\Entity\User;
use Worga\src\Model\UserManager;

class Hydrate 
{
    // Properties for the Hydrate class
    private UserManager $userManager;

    public function __construct()
    {
        $this->userManager = new UserManager();
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
            if ($key === 'user') {
                // Set user property to new User object
                $user = $this->userManager->getUserById($value) ?? new User($value);
                $object->setUser($user);
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
