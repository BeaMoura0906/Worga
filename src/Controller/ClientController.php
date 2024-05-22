<?php

namespace Worga\src\Controller;

use Worga\src\Model\Entity\Client;
use Worga\src\Model\Entity\User;
use Worga\src\Model\ClientManager;

/**
 * Class ClientController
 * It manages operations related to clients, including database interactions by using the managers and rendering views.
 */
class ClientController extends Controller
{
    // Properties
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
        $data = [
            'listClients' => true
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
     * Action method to render the edit client view with the add client form
     */
    public function addClientAction()
    {
        $this->render('client', []);
    }

    /**
     * Action method to add a new client to the database 
     */
    public function addClientValidAction()
    {
        if( isset( $this->vars['lastName'] ) && isset( $this->vars['firstName'] ) && isset( $this->vars['address'] ) && isset( $this->vars['phone'] ) && isset( $this->vars['email'] )) {
            $lastName = filter_var( $this->vars['lastName'] );
            $firstName = filter_var( $this->vars['firstName'] );
            $address = filter_var( $this->vars['address'] );
            $phone = filter_var( $this->vars['phone'] );
            $email = filter_var( $this->vars['email'] );
            $other = isset( $this->vars['other'] ) ?filter_var( $this->vars['other'] ) : null;

            if( $this->clientManager->checkIfClientExists( $lastName, $firstName ) ) {
                $data = [
                    'message' => [
                        'message' => "Un client avec le nom ".$lastName." ".$firstName." existe déjà.",
                        'type' => "warning"
                    ]
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
                $client->setUser( new User(['id' => $_SESSION['userId']]) );

                if( $client = $this->clientManager->insertClient( $client ) ) {
                    $data = [
                        'message' => [
                            'message' => "Le client ".$lastName." ".$firstName." a bien été ajouté.",
                            'type' => "success"
                        ]
                    ];
                    $this->render('client', $data);
                } else {
                    $data = [
                        'message' => [
                            'message' => "Le client ".$lastName." ".$firstName." n'a pas pu être ajouté.",
                            'type' => "danger"
                        ]
                    ];
                    $this->render('client', $data);
                }
            }
        } else {
            $data = [
                'message' => [
                    'message' => "Veuillez remplir tous les champs.",
                    'type' => 'warning'
                ]
            ];
            $this->render('client', $data);
        }
    }

    /**
     * Action method to render the edit client view with the update client form
     */
    public function updateClientAction()
    {
        $clientId = $this->vars['clientId'] ?? null;
        $client = $this->clientManager->getClientById($clientId);
        if ($client) {
            $data = ['selectedClient' => $client];
            $this->render('client', $data);
        } else {
            $listClients = $this->clientManager->getAllClients();
            $data = [
                'message' => [
                    'message' => "Une erreur est survenue. Veuillez sélectionner un client.",
                    'type' => 'warning'
                ],
                'listClients' => $listClients
            ];
            $this->render('client', $data);
        }
    }

    /**
     * Action method to update a client in the database
     */
    public function updateClientValidAction()
    {
        if ( isset( $this->vars['clientId'] ) && isset( $this->vars['lastName'] ) && isset( $this->vars['firstName'] ) && isset( $this->vars['address'] ) && isset( $this->vars['phone'] ) && isset( $this->vars['email'] ) ) {
            $clientId = filter_var( $this->vars['clientId'] );
            $lastName = filter_var( $this->vars['lastName'] );
            $firstName = filter_var( $this->vars['firstName'] );
            $address = filter_var( $this->vars['address'] );
            $phone = filter_var( $this->vars['phone'] );
            $email = filter_var( $this->vars['email'] );
            $other = isset( $this->vars['other'] ) ? filter_var( $this->vars['other'] ) : null;

            $client = new Client([
                'id' => $clientId,
                'last_name' => $lastName,
                'first_name' => $firstName,
                'address' => $address,
                'phone' => $phone,
                'email' => $email,
                'other' => $other
            ]);
            $client->setUser( new User(['id' => $_SESSION['userId']]) );

            if( $client = $this->clientManager->updateClient( $client ) ) {
                $data = [
                    'message' => [
                        'message' => "Le client ".$lastName." ".$firstName." a bien été mis à jour.",
                        'type' => "success"
                    ]
                ];
                $this->render('client', $data);
            } else {
                $data = [
                    'message' => [
                        'message' => "Le client ".$lastName." ".$firstName." n'a pas pu être mis à jour.",
                        'type' => "danger"
                    ]
                ];
                $this->render('client', $data);
            }
        } else {
            $data = [
                'message' => [
                    'message' => "Veuillez remplir tous les champs.",
                    'type' => 'warning'
                ]
            ];
            $this->render('client', $data);
        }
    }

    /**
     * Action method to delete a client from the database
     */
    public function deleteClientAction()
    {
        $data['listClients'] = true;
        if ( isset( $this->vars['clientId'] ) ) {
            $clientId = filter_var( $this->vars['clientId'] );
            if ($this->clientManager->deleteClientById($clientId)) {
                $data['message'] = [
                    'message' => "Le client a bien été supprimé.",
                    'type' => "success"
                ];
                $this->render('client', $data);
            } else {
                $data['message'] = [
                    'message' => "Le client n'a pas pu être supprimé.",
                    'type' => "danger"
                ];
                $this->render('client', $data);
            }
        } else {
            $data['message'] = [
                'message' => "Une erreur est survenue. Veuillez sélectionner un client.",
                'type' => 'warning'
            ];
            $this->render('client', $data);
        }
    }
}