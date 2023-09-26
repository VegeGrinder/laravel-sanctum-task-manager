<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PriorityLevel extends Enum
{
    #[Description('Low')]
    const LOW = 0;

    #[Description('Normal')]
    const NORMAL = 1;

    #[Description('High')]
    const HIGH = 2;

    #[Description('Urgent')]
    const URGENT = 3;
}
