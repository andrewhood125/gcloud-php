<?php

namespace Andrewhood125;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Google\Auth\ApplicationDefaultCredentials;
use Andrewhood125\Exceptions\ConfigurationException;
use Andrewhood125\Exceptions\MissingParameterException;

class GoogleCloudStorage
{

    public $baseUrl = 'https://www.googleapis.com/storage/v1';
    public $uploadUrl = 'https://www.googleapis.com/upload/storage/v1';

    public function __construct($bucket = null)
    {
        $this->bucket = $bucket ?: getenv('GOOGLE_CLOUD_STORAGE_BUCKET');

        if(!$this->bucket)
            throw new ConfigurationException("GOOGLE_CLOUD_STORGE_BUCKET is not set");

        // create middleware
        $middleware = ApplicationDefaultCredentials::getMiddleware(config('storage.scopes'));
        $stack = HandlerStack::create();
        $stack->push($middleware);

        // create the HTTP client
        $this->client = new Client([
          'handler' => $stack,
          'auth' => 'google_auth'  // authorize all requests
        ]);
    }


    /**
     * https://cloud.google.com/storage/docs/json_api/v1/objects/get
     */
    public function get($name) {

        if(empty($name))
            throw new MissingParameterException("Parameter \$name is empty: $name");

        return json_decode(
            $this->client->get(
                "$this->baseUrl/b/$this->bucket/o/$name"
            )->getBody()
        );
    }

    /**
     * Like `get` but downloads the object.
     */
    public function download($object, $folder) {
        $fullPath = $folder.$object;
        $object = $this->get($object);
        $this->client->get($object->mediaLink, [
            'sink' => $fullPath
        ]);
        return $fullPath;
    }

    /**
     * https://cloud.google.com/storage/docs/json_api/v1/objects/insert
     */
    public function insert($file) {
        $body = fopen($file, 'r');
        $params = [
            'uploadType' => 'media',
            'name' => basename($file)
        ];
        $paramString = http_build_query($params);
        $this->client->request('POST', "{$this->uploadUrl}/b/{$this->bucket}/o?{$paramString}", [
            'body' => $body
        ]);
    }

    /**
     * https://cloud.google.com/storage/docs/json_api/v1/objects/list
     */
    public function ls($nextPageToken = null) {
        return json_decode(
            $this->client->get(
                "$this->baseUrl/b/$this->bucket/o?pageToken=$nextPageToken"
            )->getBody()
        );
    }

}
