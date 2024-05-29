<?php

namespace App\Controller;

use App\Form\PostCreationFormType;
use App\Service\WordPressService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;

class PostCreationController extends AbstractController
{
    #[Route('/post/creation', name: 'app_post_creation')]
    public function index(Request $request, WordPressService $wordpressService, LoggerInterface $logger): Response
    {
        $form = $this->createForm(PostCreationFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logger->info('Form is submitted and valid.');

            // Handle image upload
            $uploadedMediaId = null;
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('post_images_directory'),
                        $newFilename
                    );
                    $filePath = $this->getParameter('post_images_directory') . '/' . $newFilename;
                    $logger->info('Uploading image to WordPress', ['filePath' => $filePath]);
                    $mediaId = $wordpressService->uploadImage($filePath);
                    $uploadedMediaId = $mediaId;
                    $logger->info('Image uploaded', ['mediaId' => $mediaId]);
                } catch (FileException $e) {
                    $logger->error('Failed to upload image', ['error' => $e->getMessage()]);
                    $this->addFlash('error', 'Failed to upload image.');
                }
            } else {
                $logger->info('No image file found for upload.');
            }

            // Convert form data to array for WordPress API
            $data = [
                'title' => $form->get('title')->getData(),
                'slug' => $form->get('slug')->getData(),
                'content' => $form->get('description')->getData(),
                'featured_media' => $uploadedMediaId,
                'status' => 'publish',
                'visibility' => $form->get('visibility')->getData(),
                'date' => $form->get('publishDate')->getData() ? $form->get('publishDate')->getData()->format('c') : null,
            ];

            $logger->info('Creating post in WordPress', ['data' => $data]);
            $response = $wordpressService->createPost($data);

            if ($response->getStatusCode() === 201) {
                $logger->info('Post created successfully.');
                $this->addFlash('success', 'Post created successfully!');
            } else {
                $logger->error('Failed to create post.', ['status' => $response->getStatusCode(), 'response' => $response->getContent()]);
                $this->addFlash('error', 'Failed to create post.');
            }

            return $this->redirectToRoute('app_post_creation');
        } else {
            $logger->info('Form is invalid.', ['errors' => (string) $form->getErrors(true, false)]);
        }

        return $this->render('post_creation/index.html.twig', [
            'postForm' => $form->createView(),
        ]);
    }
}
