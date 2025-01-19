<?php

declare(strict_types=1);

namespace App\Model\AdvancedItemSearch;

class Filter
{
    private ?string $condition = null;

    private ?string $type = null;

    private ?string $datumLabel = null;

    private ?string $datumType = null;

    private ?string $operator = null;

    private ?string $value = null;

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): Filter
    {
        $this->condition = $condition;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): Filter
    {
        $this->type = $type;

        return $this;
    }

    public function getDatumLabel(): ?string
    {
        return $this->datumLabel;
    }

    public function setDatumLabel(?string $datumLabel): Filter
    {
        $this->datumLabel = $datumLabel;

        return $this;
    }

    public function getDatumType(): ?string
    {
        return $this->datumType;
    }

    public function setDatumType(?string $datumType): Filter
    {
        $this->datumType = $datumType;

        return $this;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): Filter
    {
        $this->operator = $operator;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): Filter
    {
        $this->value = $value;

        return $this;
    }
}
