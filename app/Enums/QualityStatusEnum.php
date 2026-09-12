<?php

namespace App\Enums;

enum QualityStatusEnum: string
{
    case PENDING = 'pending';
    case PASSED = 'passed';
    case FAILED = 'failed';
}
