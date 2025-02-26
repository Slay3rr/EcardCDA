<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CloudinaryService
{
    private $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
                'api_key' => $_ENV['CLOUDINARY_API_KEY'],
                'api_secret' => $_ENV['CLOUDINARY_API_SECRET']
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