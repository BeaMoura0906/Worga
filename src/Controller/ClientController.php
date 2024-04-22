<?php

namespace Worga\src\Controller;

use Worga\src\Model\Entity\Client;
use Worga\src\Model\Entity\User;
use Worga\src\Model\ClientManager;

class ClientController extends Controller
{
    // Property for the ClientManager instance
    private ClientManager $clientManager;

    /**
     * Constructor method to initialize properties and call the parent constructor
     *
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        // Create an instance of ClientManager
        $this->clientManager = new ClientManager();

        // Call the parent constructor with parameters
        parent::__construct($params );
    }

    /**
     * Default action method to render the client view
     */
    public function defaultAction()
    {
        $listClients = $this->clientManager->getAllClients();
        $data = [
            'listClients' => $listClients
        ];
        $this->render('client', $data);
    }

    /**
     * Action method to list all clients with parameters and pagination options for clients bootstrap table.
     */
    public function listClientsAction()
    {
        $nbClients = $this->clientManager->countAllClients() ?? 0;

        $searchParams = [
            'search'		=> $this->vars['search'],
			'sort'			=> $this->vars['sort'],
			'order'			=> $this->vars['order'],
			'offset'		=> $this->vars['offset'],
			'limit'			=> $this->vars['limit'],
			'searchable'	=> $this->vars['searchable']
        ];

        $listClients = $this->clientManager->getAllClientsWithParams($searchParams);

        $dataBs = [];

        foreach( $listClients as $client ) {
            $dataBs[] = [
                'id'        => $client->getId(),
                'lastName'  => $client->getLastName(),
                'firstName' => $client->getFirstName(),
                'address'   => $client->getAddress(),
                'phone'     => $client->getPhoneFormatted(),
                'email'     => $client->getEmail(),
                'other'     => $client->getOther(),
                'insertedAt' => $client->getInsertedAt()->format('d/m/Y'),
                'updatedAt' => $client->getUpdatedAt()->format('d/m/Y')
            ];
        }

        $data = [
            "rows"        => $dataBs,
            "total"       => $nbClients
        ];
        $jsData = json_encode( $data );
        if ($jsData === false) {
            echo "Erreur d'encodage JSON : " . json_last_error_msg();
            error_log("Erreur d'encodage JSON : " . json_last_error_msg());
        } else {
            echo $jsData;
        }
    }

    /**
     * 
     */
    public function addClientAction()
    {
        $this->render('client', []);
    }

    /**
     */
    public function addClientValidAction()
    {
        if( isset( $this->vars['lastName'] ) && isset( $this->vars['firstName'] ) && isset( $this->vars['address'] ) && isset( $this->vars['phone'] ) && isset( $this->vars['email'] )) {
            $lastName = htmlentities( $this->vars['lastName'] );
            $firstName = htmlentities( $this->vars['firstName'] );
            $address = htmlentities( $this->vars['address'] );
            $phone = htmlentities( $this->vars['phone'] );
            $email = htmlentities( $this->vars['email'] );
            $other = isset( $this->vars['other'] ) ?htmlentities( $this->vars['other'] ) : null;

            if( $this->clientManager->checkIfClientExists( $lastName, $firstName ) ) {
                $message = "Un client avec le nom ".$lastName." ".$firstName." existe déjà.";
                $type = "warning";
                $message = [
                    'message' => $message,
                    'type' => $type
                ];
                $data = [
                    'message' => $message
                ];
                $this->render('client', $data);
            } else {
                $client = new Client([]);
                $client->setLastName( $lastName );
                $client->setFirstName( $firstName );
                $client->setAddress( $address );
                $client->setPhone( $phone );
                $client->setEmail( $email );
                $client->setOther( $other );

                $user = new User([]);
                $user->setId( $_SESSION['userId'] );
                $client->setUser( $user );

                if( $client = $this->clientManager->insertClient( $client ) ) {
                    $message = "Le client ".$lastName." ".$firstName." a bien été ajouté.";
                    $type = "success";
                    $message = [
                        'message' => $message,
                        'type' => $type
                    ];
                    $data = [
                        'message' => $message,
                        'selectedClient' => $client
                    ];
                    $this->render('client', $data);
                } else {
                    $message = "Le client ".$lastName." ".$firstName." n'a pas pu être ajouté.";
                    $type = "danger";
                    $message = [
                        'message' => $message,
                        'type' => $type
                    ];
                    $data = [
                        'message' => $message
                    ];
                    $this->render('client', $data);
                }
            }
        } else {
            $message = "Veuillez remplir tous les champs.";
            $type = "warning";
            $message = [
                'message' => $message,
                'type' => $type
            ];
            $data = [
                'message' => $message
            ];
            $this->render('client', $data);
        }
    }
}