<?php 

namespace App\Service;

use App\Repository\PostRepository;

class PostService
{
	private $postRepository;

	public function __construct(PostRepository $postRepository)
	{
		$this->postRepository = $postRepository;
	}

	public function savePosts(array $posts): array
	{
		if (empty($posts)) {
			return ['status' => 422, 'message' => 'Error: No post to save in the database'];
		}

		try {
			foreach ($posts as $post) {
				$this->postRepository->save($post);
			}
			$this->postRepository->flush();
		} catch (Exception $e) {
			return ['status' => 400, 'message' => 'Error: ' . $e->getMessage()]; 
		}

		return ['status' => 200, 'message' => 'Posts are successfully saved in the database'];
	}
		
}