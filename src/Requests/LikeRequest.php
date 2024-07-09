<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class LikeRequest extends BaseRequest
{
    #[Type('integer')]
    #[NotBlank()]
    public $entityId;

    #[Type('string')]
    #[NotBlank([])]
    public $entityType;
}
