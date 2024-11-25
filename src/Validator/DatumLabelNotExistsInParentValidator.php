<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Collection;
use App\Entity\Datum;
use App\Entity\Field;
use App\Entity\Item;
use App\Entity\Path;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DatumLabelNotExistsInParentValidator extends ConstraintValidator
{
    #[\Override]
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DatumLabelNotExistsInParent) {
            throw new UnexpectedTypeException($constraint, DatumLabelNotExistsInParent::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $label = $this->context->getObject()->getLabel();

        foreach ($value->getData() as $existingData) {
            if ($this->context->getObject() !== $existingData && $label === $existingData->getLabel()) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ label }}', $label)
                    ->addViolation();
            }
        }
    }
}
