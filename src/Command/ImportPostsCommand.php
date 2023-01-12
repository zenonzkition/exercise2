<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Post;
use App\Service\PostService;

#[AsCommand(
    name: 'app:import-posts',
    description: 'Imports posts into database',
    hidden: false,
    aliases: ['app:retrieve-posts']
)]
class ImportPostsCommand extends Command
{
    private $client;
    private $serializer;
    private $postService;

    public function __construct(HttpClientInterface $client, PostService $postService)
    {
        parent::__construct();
        $this->client = $client;
        $this->postService = $postService;
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Importing posts',
            '...............',
            '',
        ]);

        $response = $this->client->request(
            'GET',
            'https://jsonplaceholder.typicode.com/posts'
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode != 200) {
            $output->writeln('Error during import, please try again.');
            return Command::FAILURE;
        }
        $posts = $response->toArray();
        $postObjects = [];
        foreach ($posts as $post) {
            $postObject = $this->serializer->denormalize($post, Post::class, null);
            $response = $this->client->request(
                'GET',
                'https://jsonplaceholder.typicode.com/users/' . $postObject->getUserId()
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                $output->writeln('Error during import, please try again.');
                return Command::FAILURE;
            }
            $user = $response->toArray();
            $postObject->setAuthor($user['name']);
            $postObjects[] = $postObject;
        }

        $result = $this->postService->savePosts($postObjects);
        $output->writeln($result['message']);
        if ($result['status'] != 200) {
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}