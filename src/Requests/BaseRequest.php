<?php

namespace App\Requests;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseRequest
{
    public function __construct(protected ValidatorInterface $validator, protected RequestStack $requestStack)
    {
        $this->populate();
        $this->validate();
    }

    public function getRequest(): Request
    {
         return $this->requestStack->getCurrentRequest();
    }

    public function validate(): void
    {
        $errors = $this->validator->validate($this);

        $messages = ['message' => 'validation_failed', 'errors' => []];
        foreach ($errors as $message) {
            $messages['errors'][$message->getPropertyPath()] = $message->getMessage();
        }

        if (count($messages['errors']) > 0) {
            $response = new JsonResponse($messages, Response::HTTP_BAD_REQUEST);
            $response->send();
        }
    }

    protected function populate(): void
    {
        $request = $this->getRequest();
        $reflection = new \ReflectionClass($this);

        foreach ($request->toArray() as $property => $value) {
            if (property_exists($this, $property)) {
                $reflectionProperty = $reflection->getProperty($property);
                $reflectionProperty->setValue($this, $value);
            }
        }
    }

}
