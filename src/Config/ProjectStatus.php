<?php

namespace App\Config;

enum ProjectStatus: int
{
    case inactive = 1;
    case active = 2;
    case review = 3;
}
