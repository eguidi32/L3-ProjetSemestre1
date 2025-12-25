<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryService
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        $this->cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? 'dpl0fmyvw';
        $this->apiKey = $_ENV['CLOUDINARY_API_KEY'] ?? '267169677382543';
        $this->apiSecret = $_ENV['CLOUDINARY_API_SECRET'] ?? 'd78wqqjX0B2_mHwHhdOTzoKSrXw';
    }

    public function upload(UploadedFile $file, string $folder = 'brasil_burger'): ?string
    {
        $timestamp = time();
        $publicId = $folder . '/' . uniqid() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Créer la signature
        $params = [
            'folder' => $folder,
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        
        ksort($params);
        $signatureString = http_build_query($params) . $this->apiSecret;
        $signature = sha1($signatureString);

        // Préparer les données pour l'upload
        $postFields = [
            'file' => new \CURLFile($file->getPathname(), $file->getMimeType(), $file->getClientOriginalName()),
            'api_key' => $this->apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => $folder,
            'public_id' => $publicId,
        ];

        // Envoyer à Cloudinary
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return $result['secure_url'] ?? null;
        }

        return null;
    }

    public function delete(string $publicId): bool
    {
        $timestamp = time();
        
        $params = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        
        ksort($params);
        $signatureString = http_build_query($params) . $this->apiSecret;
        $signature = sha1($signatureString);

        $postFields = [
            'public_id' => $publicId,
            'api_key' => $this->apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}