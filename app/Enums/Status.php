<?php

namespace App\Enums;

enum Status: string
{
    case Valid   = "active";
    case Invalid = "inactive";
}
