<?php

namespace Erichard\DmsBundle\Entity;

use Erichard\DmsBundle\DocumentInterface;
use Erichard\DmsBundle\Entity\Behavior\TranslatableEntity;
use Erichard\DmsBundle\Entity\Metadata;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

class DocumentMetadata implements Translatable
{
    use TranslatableEntity;

    protected $id;
    protected $metadata;
    protected $document;
    protected $value;

    public function __construct(Metadata $m)
    {
        $this->metadata = $m;

    }

    public function getId()
    {
        return $this->id;
    }

    public function setDocument(DocumentInterface $document)
    {
        $this->document = $document;

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

    public function __toString()
    {
        return $this->document->getName().' - '.$this->metadata->getName();
    }
}
