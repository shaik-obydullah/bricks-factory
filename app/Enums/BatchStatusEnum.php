<?php

namespace App\Enums;

enum BatchStatusEnum: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
}
