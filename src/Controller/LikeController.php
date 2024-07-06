<?php

namespace App\Controller;

use App\Service\LikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LikeController extends AbstractController
{

    private $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    #[Route('/like', name: 'app_like', methods: ['POST'])]
    public function index(Request $request, ValidatorInterface $validator): Response
    {
        $requestData = $request->toArray();
        $userId = $this->getUser()->getId();

        // Define validation constraints
        $constraints = new Assert\Collection([
            'entityType' => [new Assert\NotBlank(), new Assert\Type('string')],
            'entityId' => [new Assert\NotBlank(), new Assert\Type('integer')],
        ]);

        // Validate the request data
        $violations = $validator->validate($requestData, $constraints);

        // Handle validation errors
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }

            return $this->json(['status' => 'error', 'errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // If data is valid, proceed with liking the entity
        $entityType = $requestData['entityType'];
        $entityId = $requestData['entityId'];
        $this->likeService->like($entityType, $entityId, $userId);

        return $this->json(['status' => 'liked']);
    }
}
