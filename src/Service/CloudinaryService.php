<?php

namespace App\Service;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    private $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => 'dienxmy6h',
                'api_key' => '344731258574875',
                'api_secret' => 'gRJckNUdrguzXpj_KZpH59J_U2w'
            ]
        ]);
    }

    public function uploadImage($file)
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload($file);
            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}