<?php

namespace App\Enum;

enum AllowedMimeType: string
{
    case PNG = 'image/png';
    case JPG = 'image/jpeg';
    case MP4 = 'video/mp4';
}
