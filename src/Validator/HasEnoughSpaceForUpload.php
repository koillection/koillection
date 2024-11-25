<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class HasEnoughSpaceForUpload extends Constraint
{
    public string $message = 'error.upload.not_enough_space';

    public string $mode = 'strict';

    #[\Override]
    public function validatedBy(): string
    {
        return static::class . 'Validator';
    }
}
