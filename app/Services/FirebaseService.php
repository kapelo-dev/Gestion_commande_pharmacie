<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $firestore;

    public function __construct()
    {
        $credentials = config('firebase.credentials');

        $this->firestore = new FirestoreClient([
            'keyFilePath' => $credentials,
        ]);
    }

    public function getFirestore()
    {
        return $this->firestore;
    }

    public function getDocuments($collection)
    {
        try {
            return $this->firestore->collection($collection)->documents();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des documents : ' . $e->getMessage());
            throw $e;
        }
    }

    public function getSubCollection($collection, $document, $subCollection)
    {
        try {
            return $this->firestore
                ->collection($collection)
                ->document($document)
                ->collection($subCollection)
                ->documents();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la sous-collection : ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDocument($collection, $document)
    {
        try {
            return $this->firestore
                ->collection($collection)
                ->document($document)
                ->snapshot();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du document : ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDocumentByField($collection, $documentId, $subCollection, $field, $value)
    {
        $query = $this->firestore->collection($collection)
            ->document($documentId)
            ->collection($subCollection)
            ->where($field, '=', $value)
            ->limit(1);

        $documents = $query->documents();

        foreach ($documents as $document) {
            return $document;
        }

        return null;
    }

    public function getPharmacyName(string $pharmacyId)
    {
        $document = $this->firestore->collection('pharmacies')->document($pharmacyId)->snapshot();

        if ($document->exists()) {
            return $document->get('nom');
        }

        return null;
    }

    public function addDocument($collection, $documentId, $subCollection, $data)
    {
        return $this->firestore->collection($collection)->document($documentId)->collection($subCollection)->add($data);
    }

    public function updateDocument($collection, $documentId, $subCollection, $subDocumentId, $data)
    {
        return $this->firestore->collection($collection)->document($documentId)->collection($subCollection)->document($subDocumentId)->update($data);
    }

    public function deleteDocument($collection, $documentId, $subCollection, $subDocumentId)
    {
        return $this->firestore->collection($collection)->document($documentId)->collection($subCollection)->document($subDocumentId)->delete();
    }
}
