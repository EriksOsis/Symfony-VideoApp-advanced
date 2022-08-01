<?php

namespace App\Utils;

use App\Utils\Interfaces\UploaderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class VimeoUploader implements UploaderInterface
{

    public function __construct(Security $security)
    {
        $this->vimeoToken = $security->getUser()->getVimeoApiKey();
    }

    public function upload($file)
    {
        // TODO: Implement upload() method.
    }

    public function delete($path): bool
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.vimeo.com/video/$path",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => [
                "Accept: application/vnd.vimeo.*+json;version=3.4",
                "Authorization: Bearer {$this->vimeoToken}",
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoder"
            ]
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new ServiceUnavailableHttpException('Error. Try again later. Message: ' . $error);
        } else {
            return true;
        }
    }
}