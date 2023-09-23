<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PriorityLevel extends Enum
{
    #[Description('Urgent')]
    const URGENT = 0;

    #[Description('High')]
    const HIGH = 1;

    #[Description('Normal')]
    const NORMAL = 2;

    #[Description('Low')]
    const LOW = 3;
}
