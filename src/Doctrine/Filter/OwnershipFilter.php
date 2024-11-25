<?php

declare(strict_types=1);

namespace App\Doctrine\Filter;

use App\Entity\User;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class OwnershipFilter extends SQLFilter
{
    #[\Override]
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->getReflectionClass()->hasProperty('owner')) {
            return sprintf('%s.owner_id = %s', $targetTableAlias, $this->getParameter('id'));
        }

        if (User::class === $targetEntity->getReflectionClass()->getName()) {
            return sprintf('%s.id = %s', $targetTableAlias, $this->getParameter('id'));
        }

        return '';
    }
}
