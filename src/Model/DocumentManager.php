<?php

namespace Worga\src\Model;

use Worga\src\Model\Entity\Document;

/**
 * Class DocumentManager 
 * Manages operations related to documents, including database interactions.
 */
class DocumentManager extends Manager
{
    /**
     * Get a document by its financial transaction ID.
     * 
     * @param $finTransId The ID of the financial transaction.
     * @return Document|null The retrieved document object, or null if the document does not exist.
     */
    public function getDocumentByFinTransId($finTransId): ?Document
    {
        $sql = "SELECT * FROM documents WHERE fin_trans_id = :fin_trans_id";
        $req = $this->dbManager->db->prepare($sql);
        $req->execute(['fin_trans_id' => $finTransId]);
        if ($documentData = $req->fetch()) {
            return new Document($documentData);
        } else {
            return null;
        }
    }

    /**
     * Get a document by its ID.
     * 
     * @param $id The ID of the document.
     * @return Document|null The retrieved document object, or null if the document does not exist.
     */
    public function getDocumentById($id): ?Document
    {
        $sql = "SELECT * FROM documents WHERE id = :id";
        $req = $this->dbManager->db->prepare($sql);
        $req->execute(['id' => $id]);
        if ($documentData = $req->fetch()) {
            return new Document($documentData);
        } else {
            return null;
        }
    }

    /**
     * Insert a new document into the database.
     * 
     * @param Document $document The document to insert.
     * @return Document|null The inserted document object, or null if the insertion failed.
     */
    public function insertDocument(Document $document): ?Document
    {
        $sql = "INSERT INTO documents (
                    name,
                    path,
                    inserted_at, 
                    updated_at, 
                    fin_trans_id, 
                    user_id
                ) VALUES (
                    :name, 
                    :path, 
                    NOW(), 
                    NOW(), 
                    :fin_trans_id, 
                    :user_id
                )";
        $req = $this->dbManager->db->prepare($sql);
        $state = $req->execute([
            'name' => $document->getName(),
            'path' => $document->getPath(),
            'fin_trans_id' => $document->getFinTrans()->getId(),
            'user_id' => $document->getUser()->getId()
        ]);
        if( $state ) {
            $document = $this->getDocumentById($this->dbManager->db->lastInsertId());
            return $document;
        } else {
            return null;
        }
    }

    /**
     * Delete a document by its financial transaction ID from the database.
     * 
     * @param $finTransId The ID of the financial transaction.
     * @return bool True if the document was deleted successfully, false otherwise.
     */
    public function deleteDocumentByFinTransId($finTransId): bool
    {
        $sql = "DELETE FROM documents WHERE fin_trans_id = :fin_trans_id";
        $req = $this->dbManager->db->prepare($sql);
        return $req->execute(['fin_trans_id' => $finTransId]);
    }
}