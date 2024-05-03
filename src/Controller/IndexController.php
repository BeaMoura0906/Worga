<?php

namespace Worga\src\Controller;

/**
 * Class IndexController
 * It manages operations related to the index view, including rendering it. 
 */
class IndexController extends Controller
{
    /**
     * Constructor method to initialize properties and call the parent constructor
     *
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        // Call the parent constructor with parameters
        parent::__construct($params);
    }

    /**
     * Default action method to render the index view
     */
    public function defaultAction()
    {
        $this->render( 'index', []);
    }
}