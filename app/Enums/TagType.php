<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TagType extends Enum
{
    #[Description('In-progress')]
    const IN_PROGRESS = 'In-progress';

    #[Description('Blocked')]
    const BLOCKED = 'Blocked';

    #[Description('On-hold')]
    const ON_HOLD = 'On-hold';
}
