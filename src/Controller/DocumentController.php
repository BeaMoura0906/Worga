<?php

namespace Worga\src\Controller;

use Error;
use Worga\src\Model\DocumentManager;
use Worga\src\Model\FinTransManager;
use Worga\src\Model\Entity\Document;
use Worga\src\Model\Entity\FinancialTransaction;
use Worga\src\Model\Entity\User;

use finfo;

/**
 * Class DocumentController
 */
class DocumentController extends Controller
{
    /** Properties */
    private $docManager;
    private $finTransManager;

    /**
     * Constructor method to initialize properties and call the parent constructor
     * 
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        $this->docManager = new DocumentManager();
        $this->finTransManager = new FinTransManager();

        parent::__construct($params);
    }

    /**
     * Default action
     */
    public function defaultAction() 
    {

    }

    /**
     * Upload a document for a financial transaction in the database and on the server. It's render the financial-transactions view with the uploaded document with a success or an error message in JSON response. 
     */
    public function uploadDocumentAction() 
    {
        if (isset($this->vars['finTransId']) && isset($this->vars['accountId']) && isset($this->vars['docName']) && isset($_FILES['document']) && $_FILES['document']['error'] == UPLOAD_ERR_OK) { 
            $file = $_FILES['document'];
            $accountId = htmlentities($this->vars['accountId']);
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . $this->pathRoot . 'financial-documents/act' . $accountId . '/'; 

            if (!is_dir($uploadDir)) { 
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("Impossible de créer le répertoire: $uploadDir");
                    echo json_encode(['success' => false, 'message' => 'Erreur lors du téchargement. Veuillez réessayer.']);
                    exit;
                } else {
                    error_log("Répertoire créé: $uploadDir");
                }
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            if ($mimeType !== 'application/pdf') {
                echo json_encode(['success' => false, 'message' => 'Seuls les fichiers PDF sont autorisés.']);
                exit;
            }

            $newFileName = bin2hex(random_bytes(10)) . ".pdf";
            $uploadFile = $uploadDir . basename($newFileName);
            $docPath = 'financial-documents/act' . $accountId . '/' . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                $docName = htmlentities($this->vars['docName']);
                $doc = new Document([
                    'name' => $docName,
                    'path' => $docPath
                ]);
                $finTransId = htmlentities($this->vars['finTransId']);   
                $finTrans = $this->finTransManager->getFinTransById($finTransId) ?? new FinancialTransaction(['id' => $finTransId]);
                $doc->setFinTrans($finTrans);
                $doc->setUser(new User(['id' => $_SESSION['userId']]));

                if ($this->docManager->insertDocument($doc)) {
                    $data = ['success' => true, 'message' => 'Téléchargement du document réussi !', 'docPath' => $this->pathRoot . $docPath];
                } else {
                    $data = ['success' => false, 'message' => 'Échec du téléchargement du document. Veuillez réessayer.'];
                }
                error_log("Fichier téléchargé avec succès à $uploadFile");
            } else {
                $data = ['success' => false, 'message' => 'Échec du téléchargement du document. Veuillez réessayer.'];
            }

            echo json_encode($data);
        } else {
            error_log("Échec du déplacement du fichier vers le dossier de destination.");
            echo json_encode(['success' => false, 'message' => 'Échec du téléchargement du document. Veuillez réessayer.']);
            exit;
        }
    }

}