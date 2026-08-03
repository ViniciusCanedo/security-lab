<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case PUBLISHER = 'PUBLISHER';
    case COMMON = 'COMMON';
}
