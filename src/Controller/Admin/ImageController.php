<?php

namespace App\Controller\Admin;

use App\Service\CloudinaryService;
use App\Document\CardImage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin')]
class ImageController extends AbstractController
{
    public function __construct(
        private CloudinaryService $cloudinaryService,
        private DocumentManager $documentManager
    ) {}

    #[Route('/upload-image', name: 'admin_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $file = $request->files->get('image');
        $cardName = $request->request->get('cardName');
        $type = $request->request->get('type');
    
        // Debug logs
        dump('Request Files:', $request->files->all());
        dump('Request Data:', $request->request->all());
    
        if (!$file) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }
    
        $debug = [
            'file_info' => [
                'name' => $file->getClientOriginalName(),
                'type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $file->getPathname()
            ],
            'cardName' => $cardName,
            'type' => $type
        ];
    
        try {
            $result = $this->cloudinaryService->uploadImage($file->getPathname());
            if (!$result['success']) {
                return $this->json([
                    'error' => 'Upload to Cloudinary failed',
                    'details' => $result['error'],
                    'debug' => $debug
                ], 500);
            }
    
            $cardImage = new CardImage();
            $cardImage->setPublicId($result['public_id']);
            $cardImage->setUrl($result['url']);
            $cardImage->setCardName($cardName ?? 'default');
            $cardImage->setType($type ?? 'default');
            
            $this->documentManager->persist($cardImage);
            $this->documentManager->flush();
    
            return $this->json([
                'success' => true,
                'image' => [
                    'id' => $cardImage->getId(),
                    'url' => $result['url'],
                    'public_id' => $result['public_id'],
                    'cardName' => $cardImage->getCardName(),
                    'type' => $cardImage->getType()
                ],
                'debug' => $debug
            ]);
        } catch (\Exception $e) {
            dump('Error:', $e->getMessage());
            return $this->json([
                'error' => 'Upload failed',
                'message' => $e->getMessage(),
                'debug' => $debug
            ], 500);
        }
    }#[Route('/images', name: 'admin_list_images', methods: ['GET'])]
public function listImages(): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
    try {
        $images = $this->documentManager
            ->getRepository(CardImage::class)
            ->findAll();

        return $this->json($images);
    } catch (\Exception $e) {
        return $this->json([
            'error' => true,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
}
    #[Route('/images/{id}', name: 'admin_edit_image', methods: ['PATCH'])]
    public function editImage(string $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Récupérer l'image depuis MongoDB
        $cardImage = $this->documentManager
            ->getRepository(CardImage::class)
            ->find($id);
    
        if (!$cardImage) {
            return $this->json(['error' => 'Image not found'], 404);
        }
    
        // Décoder le JSON du body
        $data = json_decode($request->getContent(), true);
    
        // Mise à jour conditionnelle des champs
        if (isset($data['cardName'])) {
            $cardImage->setCardName($data['cardName']);
        }
        
        if (isset($data['type'])) {
            $cardImage->setType($data['type']);
        }
    
        // Sauvegarder les modifications
        $this->documentManager->flush();
    
        return $this->json([
            'success' => true,
            'image' => [
                'id' => $cardImage->getId(),
                'publicId' => $cardImage->getPublicId(),
                'url' => $cardImage->getUrl(),
                'cardName' => $cardImage->getCardName(),
                'type' => $cardImage->getType()
            ]
        ]);
    }
}