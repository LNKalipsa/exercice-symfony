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

#[Route('/api', name: 'api_')]
final class AuthorsController extends AbstractController
{
    #[Route('/authors', name: 'get_authors')]
    public function getAuthors(AuthorRepository $authorRepository): JsonResponse
    {
        $authors = $authorRepository->findAll();
        
        $authors = array_map(function (Author $author) {
            $author = [
                'id' => $author->getId(),
                'firstName' => $author->getFirstName(),
                'lastName' => $author->getLastName(),
                'avatarUrl' => $author->getAvatarUrl(),
                'bio' => $author->getBio(),
            ];
            return $author;
        }, $authors);

        return new JsonResponse($authors);
    }

    #[Route('/authors/{id}', name: 'get_authors_id')]
    public function getAuthorsById(int $id, AuthorRepository $authorRepository): JsonResponse
    {
        $author = $authorRepository->find($id);

        if (!$author) {
            return $this->json(['error' => 'Author not found'], 404);
        }
        
        $author = [
            'id' => $author->getId(),
            'firstName' => $author->getFirstName(),
            'lastName' => $author->getLastName(),
            'avatarUrl' => $author->getAvatarUrl(),
            'bio' => $author->getBio(),
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

        $author = new Author($data['firstName'], $data['lastName'], $data['avatarUrl'] ?? null, $data['bio'] ?? null);

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
