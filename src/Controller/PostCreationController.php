<?php 

// src/Controller/PostCreationController.php

namespace App\Controller;

use App\Form\PostCreationFormType;
use App\Service\WordPressService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostCreationController extends AbstractController
{
    #[Route('/post/creation', name: 'app_post_creation')]
    public function index(Request $request, WordPressService $wordpressService): Response
    {
        $form = $this->createForm(PostCreationFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle the image upload
            $imageFiles = $form->get('images')->getData();
            $uploadedMediaIds = [];

            foreach ($imageFiles as $imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );

                    // Upload image to WordPress and get media ID
                    $mediaId = $wordpressService->uploadImage($this->getParameter('images_directory') . '/' . $newFilename);
                    $uploadedMediaIds[] = $mediaId;
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                }
            }

            // Convert form data to array for WordPress API
            $data = [
                'title' => $form->get('title')->getData(),
                'slug' => $form->get('slug')->getData(),
                'content' => $form->get('description')->getData(),
                'featured_media' => count($uploadedMediaIds) > 0 ? $uploadedMediaIds[0] : null, // Assuming first image as featured image
            ];

            $response = $wordpressService->createPost($data);

            if ($response->getStatusCode() === 201) {
                $this->addFlash('success', 'Post created successfully!');
            } else {
                $this->addFlash('error', 'Failed to create post.');
            }

            return $this->redirectToRoute('app_post_creation');
        }

        return $this->render('post_creation/index.html.twig', [
            'postForm' => $form->createView(),
        ]);
    }
}
