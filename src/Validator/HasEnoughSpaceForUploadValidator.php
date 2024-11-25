<?php

declare(strict_types=1);

namespace App\Validator;

use App\Service\DiskUsageCalculator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasEnoughSpaceForUploadValidator extends ChoiceValidator
{
    public function __construct(
        private readonly DiskUsageCalculator $diskUsageCalculator,
        private readonly Security $security
    ) {
    }

    #[\Override]
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof HasEnoughSpaceForUpload) {
            throw new UnexpectedTypeException($constraint, HasEnoughSpaceForUpload::class);
        }

        if ($value === null) {
            return;
        }

        if ($this->diskUsageCalculator->hasEnoughSpaceForUpload($this->security->getUser(), $value) === false) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
