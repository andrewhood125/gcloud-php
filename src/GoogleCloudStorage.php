<?php

namespace Andrewhood125;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Google\Auth\ApplicationDefaultCredentials;

class GoogleCloudStorage
{

    public $baseUrl = 'https://www.googleapis.com/storage/v1';

    public function __construct()
    {
        $this->bucket = getenv('GOOGLE_CLOUD_STORAGE_BUCKET');

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
    public function get($object) {
        return json_decode(
            $this->client->get(
                "$this->baseUrl/b/$this->bucket/o/$object"
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
