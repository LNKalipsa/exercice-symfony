<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Client\PostApiClient;

#[Route('/api', name: 'api_')]
final class AuthorsController extends AbstractController
{
    #[Route('/authors', name: 'get_authors', methods:['GET'])]
    public function getAuthors(AuthorRepository $authorRepository, PostApiClient $postApiClient): JsonResponse
    {
        $authors = $authorRepository->findAll();
        $authorIds = [];

        foreach($authors as $author){
            $authorIds[] = $author->getId();
        }

        $allPosts = $postApiClient->findByAuthorIds($authorIds);
        
        $authors = array_map(function (Author $author) use ($allPosts) {
            $postsByAuthor = [];
            foreach($allPosts as $post){
                if($post['authorId'] === $author->getId()){
                    $postsByAuthor[] = $post;
                }
            }
            
            $author = [
                'id' => $author->getId(),
                'firstName' => $author->getFirstName(),
                'lastName' => $author->getLastName(),
                'avatarUrl' => $author->getAvatarUrl(),
                'bio' => $author->getBio(),
                'posts'=> $postsByAuthor
            ];
            return $author;
        }, $authors);
        return new JsonResponse($authors);
    }

    #[Route('/authors/{id}', name: 'get_author', methods: ['GET'])]
    public function getAuthorsById(?Author $author, PostApiClient $postApiClient): JsonResponse
    {
        if (!$author) {
            return $this->json(['error' => 'Author not found'], 404);
        }
        
        $postsByAuthor = $postApiClient->findByAuthorId($author->getId())['member'] ?? null;

        if ($postsByAuthor) {
            $postsByAuthor = array_map(function ($post) {
                return [
                    'id' => $post['id'],
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'authorId' => $post['authorId'],
                ];
            }, $postsByAuthor);
        }

        $author = [
            'id' => $author->getId(),
            'firstName' => $author->getFirstName(),
            'lastName' => $author->getLastName(),
            'avatarUrl' => $author->getAvatarUrl(),
            'bio' => $author->getBio(),
            'posts' => $postsByAuthor ?: null,
        ];
        return new JsonResponse($author);
    }

    #[Route('/authors', name: 'new_author', methods: ['POST'])]
    public function newAuthor(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
        if (!isset($data['firstName']) || !isset($data['lastName'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $author = new Author();
        $author->setFirstName($data['firstName']);
        $author->setLastName($data['lastName']);
        $author->setAvatarUrl($data['avatarUrl']?? Null);
        $author->setBio($data['bio']?? Null);

        $entityManager->persist($author);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $author->getId(),
            'firstName' => $author->getFirstName(),
            'lastName' => $author->getLastName(),
            'avatarUrl' => $author->getAvatarUrl(),
            'bio' => $author->getBio(),
        ], Response::HTTP_CREATED);
    }
}
