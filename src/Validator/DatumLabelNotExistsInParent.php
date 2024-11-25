<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class DatumLabelNotExistsInParent extends Constraint
{
    public string $message = 'error.label.must_be_unique';

    public string $mode = 'strict';

    #[\Override]
    public function validatedBy(): string
    {
        return static::class . 'Validator';
    }
}
