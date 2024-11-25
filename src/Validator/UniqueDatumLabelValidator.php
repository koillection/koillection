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

class UniqueDatumLabelValidator extends ConstraintValidator
{
    #[\Override]
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueDatumLabel) {
            throw new UnexpectedTypeException($constraint, UniqueDatumLabel::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $labels = [];
        if ($value instanceof Item || $value instanceof Collection) {
            $value = $value->getData();
            $labels[] = $this->context->getObject()->getLabel();
        }

        foreach ($value as $element) {
            $label = match (true) {
                $element instanceof Field => $element->getName(),
                $element instanceof Datum => $element->getLabel(),
                $element instanceof Path => $element->getName(),
                default => throw new UnexpectedValueException($element, 'Field|Datum|Path'),
            };

            if (\in_array($label, $labels)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ label }}', $label)
                    ->addViolation();
            } else {
                $labels[] = $label;
            }
        }
    }
}
