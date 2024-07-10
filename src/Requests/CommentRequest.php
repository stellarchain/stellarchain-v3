<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class CommentRequest extends BaseRequest
{
    #[Type('string')]
    #[NotBlank([])]
    public $content;
}
