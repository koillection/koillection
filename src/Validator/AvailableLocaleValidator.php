<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AvailableLocaleValidator extends ChoiceValidator
{
    public function __construct(
        #[Autowire(param: 'kernel.enabled_locales')] private readonly array $enabledLocales
    ) {
    }

    #[\Override]
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AvailableLocale) {
            throw new UnexpectedTypeException($constraint, AvailableLocale::class);
        }

        $constraint->choices = $this->enabledLocales;

        parent::validate($value, $constraint);
    }
}
