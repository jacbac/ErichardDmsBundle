<?php

namespace Erichard\DmsBundle;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Erichard\DmsBundle\Entity\DocumentMetadata;
use Erichard\DmsBundle\Entity\DocumentNodeMetadata;
use GetId3\GetId3Core as GetId3;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class DmsManager
{
    protected $registry;
    protected $securityContext;
    protected $options;

    public function __construct(Registry $registry, SecurityContextInterface $securityContext, array $options = array())
    {
        $this->registry = $registry;
        $this->securityContext = $securityContext;
        $this->options = $options;
    }

    public function getRoots()
    {
        $nodes = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->getRoots()
        ;

        return array_filter($nodes, function($node) {
            return $this->securityContext->isGranted('VIEW', $node);
        });
    }

    public function getNodeById($nodeId)
    {
        $documentNode = $nodes = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->findOneByIdWithChildren($nodeId)
        ;

        if (null !== $documentNode) {
            $this->prepareNode($documentNode);
        }

        return $documentNode;
    }

    public function getNode($nodeSlug)
    {
        $documentNode = $nodes = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->findOneBySlugWithChildren($nodeSlug)
        ;

        if (null !== $documentNode) {
            $this->prepareNode($documentNode);
        }

        return $documentNode;
    }

    public function getDocument($documentSlug, $nodeSlug)
    {
        $document = $nodes = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\Document')
            ->findOneBySlugAndNode($documentSlug, $nodeSlug)
        ;

        if (null !== $document) {
            $this->prepareDocument($document);
        }

        return $document;
    }

    public function findNodesByMetadatas(array $metatadas = array(), array $sortBy = array())
    {
        $documentNodes = $nodes = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->findByMetadatas($metatadas, $sortBy)
        ;

        return array_filter($documentNodes, function(DocumentNodeInterface $documentNode) {
            return $this->securityContext->isGranted('VIEW', $documentNode);
        });
    }

    public function findDocumentsByMetadatas(array $metatadas = array(), array $sortBy = array())
    {
        $documents = $nodes = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\Document')
            ->findByMetadatas($metatadas, $sortBy)
        ;

        return array_filter($documents, function(DocumentInterface $document) {
            return $this->securityContext->isGranted('VIEW', $document);
        });
    }

    protected function prepareNode(DocumentNodeInterface $documentNode)
    {
        if (!$this->securityContext->isGranted('VIEW', $documentNode) ||
            (!$this->securityContext->isGranted('NODE_EDIT', $documentNode) && !$documentNode->isEnabled())
        ) {
            throw new AccessDeniedException('You are not allowed to view this node : '. $documentNode->getName());
        }

        foreach ($documentNode->getNodes() as $node) {
            if (!$this->securityContext->isGranted('VIEW', $node)) {
                $documentNode->removeNode($node);
            }
        }

        foreach ($documentNode->getDocuments() as $document) {
            if (!$this->securityContext->isGranted('VIEW', $document)) {
                $documentNode->removeDocument($document);
            }

            $this->prepareDocument($document);
        }

        return $documentNode;
    }

    protected function prepareDocument(DocumentInterface $document)
    {
        if (!$this->securityContext->isGranted('VIEW', $document) ||
            (!$this->securityContext->isGranted('DOCUMENT_EDIT', $document) && !$document->isEnabled())
        ) {
            throw new AccessDeniedException('You are not allowed to view this document: '. $document->getName());
        }

        // Set the mimetype
        $absPath  = $this->options['storage_path'] . DIRECTORY_SEPARATOR . $document->getFilename();
        $getID3 = new GetId3;
        $info = $getID3->analyze($absPath);
        if (isset($info['mime_type'])) {
            $document->setMimeType(isset($info['mime_type'])? $info['mime_type'] : 'unknown');
        }

        return $document;
    }

    public function getNodeMetadatas(DocumentNodeInterface $node)
    {
        $metadatas = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\Metadata')
            ->findByScope(array('node', 'both'))
        ;

        foreach ($metadatas as $m) {
            if (!$node->hasMetadata($m->getName())) {
                $metadata = new DocumentNodeMetadata($m);
                $node->addMetadata($metadata);
            }
        }
    }

    public function getDocumentMetadatas(DocumentInterface $document)
    {
        // Set all metadata on the document
        $metadatas = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\Metadata')
            ->findByScope(array('document', 'both'))
        ;

        foreach ($metadatas as $m) {
            if (!$document->hasMetadata($m->getName())) {
                $metadata = new DocumentMetadata($m);
                $document->addMetadata($metadata);
            }
        }
    }
}
