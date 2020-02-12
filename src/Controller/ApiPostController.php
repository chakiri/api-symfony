<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiPostController extends AbstractController
{
    /**
     * @Route("/api/post", name="api_post", methods={"GET"})
     */
    public function index(PostRepository $repository)
    {
        return $this->json($repository->findAll(), 200, [], ['groups' => 'posts:read']);
    }

    /**
     * @Route("/api/post", name="api_post_create", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        $jsonSent = $request->getContent();

        try{
            $post = $serializer->deserialize($jsonSent, Post::class, "json");

            $post->setCreatedAt(new \DateTime());

            $errors = $validator->validate($post);

            if (count($errors) > 0){
                return $this->json($errors, 400);
            }

            $manager->persist($post);

            $manager->flush();

            return $this->json($post, 201, [], ['groups' => 'posts:read']);

        }catch (NotEncodableValueException $e){

            return $this->json([
                "statut" => 400,
                "message" => $e->getMessage()
            ],400);
        }
    }
}
