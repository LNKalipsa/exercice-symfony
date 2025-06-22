<?php

namespace App\Client;

class PostApiClient
{
    public function findByAuthorIds(array $authorIds): array
    {
        // Simulate an API call to fetch posts by author IDs
        // In a real application, this would make an HTTP request to an external API

        $posts = [];
        foreach ($authorIds as $authorId) {
            $posts[] = [
                'id' => rand(1, 1000),
                'title' => 'Titre : ' . $authorId,
                'content' => 'Contenu : ' . $authorId,
                'authorId' => $authorId,
            ];
        }

        return $posts;
    }

    public function findAuthorById(int $id): array
    {
        // Simulate an API call to fetch a post by ID
        // In a real application, this would make an HTTP request to an external API

        return [
            'id' => $id,
            'title' => 'Titre : ' . $id,
            'content' => 'Contenu : ' . $id,
            'authorId' => $id,
        ];
    }
}