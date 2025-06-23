<?php

namespace App\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostApiClient
{
    public function __construct( private HttpClientInterface $client) {
    }

    public function findByAuthorIds(array $authorIds)
    {
        
        $apiUrl = 'https://witty-tick-fredericlesueurs-aa3deee5.koyeb.app/api/posts?';

        $param = null;
        foreach($authorIds as $authorId){
            $param .= 'authorId[]='.$authorId."&";
        }
        
        $response = $this->client->request(
            'GET',
            $apiUrl.$param
        );
        $posts = $response->getContent() ? json_decode($response->getContent(), true) : [];
        
        $result = [];
        foreach($posts['member'] as $post) {
            $result[] = [
                'id'=>$post['id'],
                'title'=> $post['title'],
                'content'=> $post['content'],
                'authorId'=> $post['authorId'],
            ];
        }
        return $result;
    }

    public function findByAuthorId(int $id): array
    {
        $response = $this->client->request(
            'GET',
            'https://witty-tick-fredericlesueurs-aa3deee5.koyeb.app/api/posts?authorId='. $id
        );
        
        return $response->getContent() ? json_decode($response->getContent(), true) : [];
    }
}