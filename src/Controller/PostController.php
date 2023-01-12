<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\PostRepository;

class PostController extends AbstractController
{
    private $postRepository;

    public function __construct(PostRepository $postRepository) 
    {
        $this->postRepository = $postRepository;
    }

    #[Route('/lista', name: 'home')]
    public function index()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $posts = $this->postRepository->findAll();
        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }

    #[Route('/usun-post/{id}', name: 'delete_post', requirements: ['id' => '\d+'])]
    public function delete(int $id)
    {
        $post = $this->postRepository->find($id);
        if ($post) {
            $this->postRepository->remove($post, true);
        }
        $this->addFlash('notice', 'Post został usunięty');
        return $this->redirectToRoute('home');
    }
}