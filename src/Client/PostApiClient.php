<?php

namespace App\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostApiClient
{
    public function __construct( private HttpClientInterface $client) {
    }
    
    public function findByAuthorIds(array $authorIds)
    {
        $response = $this->client->request(
            'GET',
            'https://witty-tick-fredericlesueurs-aa3deee5.koyeb.app/api/posts'
        );
        
    }

    public function findByAuthorId(int $id): array
    {
        $response = $this->client->request(
            'GET',
            'https://witty-tick-fredericlesueurs-aa3deee5.koyeb.app/api/posts?authorId='. $id
        );

        $content = $response->getContent();
        
        return $content ? json_decode($content, true) : [];
    }
}