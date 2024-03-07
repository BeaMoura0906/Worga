<?php

namespace Worga\src\Controller;

// use Worga\src\Model\Manager;

class IndexController extends Controller
{
    // Properties for the Manager instance
    private $Manager;

    /**
     * Constructor method to initialize properties and call the parent constructor
     *
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        // Create an instance of Manager
        // $this->cardManager = new Manager();

        // Call the parent constructor with parameters
        parent::__construct($params);
    }

    /**
     * Default action method to render the index view
     */
    public function defaultAction()
    {
        $data = [
            'title' => 'Accueil',
        ];
        $this->render( 'index', $data);
    }
}