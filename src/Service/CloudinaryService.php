<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CloudinaryService
{
    private $cloudinary;

    public function __construct(ParameterBagInterface $params)
    {
        // Récupérer les variables d'environnement avec getenv() ou via ParameterBagInterface
        $cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: $params->get('cloudinary.cloud_name');
        $apiKey = getenv('CLOUDINARY_API_KEY') ?: $params->get('cloudinary.api_key');
        $apiSecret = getenv('CLOUDINARY_API_SECRET') ?: $params->get('cloudinary.api_secret');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw new \RuntimeException('Les paramètres Cloudinary sont manquants. Vérifiez votre configuration.');
        }

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret
            ]
        ]);
    }

    public function uploadImage($file)
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload($file, [
                'folder' => 'ecard',
                'resource_type' => 'auto'
            ]);
            
            // Log pour debug
            dump('Cloudinary Response:', $result);
            
            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ];
        } catch (\Exception $e) {
            // Log pour debug
            dump('Cloudinary Error:', $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}