<?php

namespace Climb\Orm\Annotation;

use Climb\Annotation\AnnotationInterface;

class Table implements AnnotationInterface
{
    public const TAG = 'Table';

    /**
     * @var string
     */
    private string $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
