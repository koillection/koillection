<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Choice;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AvailableLocale extends Choice
{
    #[\Override]
    public function validatedBy(): string
    {
        return static::class . 'Validator';
    }
}
