<?php

namespace Erichard\DmsBundle\Entity;

use Erichard\DmsBundle\DocumentNodeInterface;
use Erichard\DmsBundle\Entity\Behavior\TranslatableEntity;
use Erichard\DmsBundle\Entity\Metadata;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

class DocumentNodeMetadata implements Translatable
{
    use TranslatableEntity;

    protected $id;
    protected $metadata;
    protected $node;
    protected $value;

    public function __construct(Metadata $m)
    {
        $this->metadata = $m;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setNode(DocumentNodeInterface $node)
    {
        $this->node = $node;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }
}
