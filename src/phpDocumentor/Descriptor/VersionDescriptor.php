<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Descriptor;

use phpDocumentor\Configuration\VersionSpecification;

final class VersionDescriptor
{
    /** @var string */
    private $number;

    /** @var Collection<DocumentationSetDescriptor> */
    private $documentationSets;

    /**
     * @param Collection<DocumentationSetDescriptor> $documentationSets
     */
    public function __construct(string $number, Collection $documentationSets)
    {
        $this->documentationSets = $documentationSets;
        $this->number = $number;
    }

    public function getNumber() : string
    {
        return $this->number;
    }

    /**
     * @return Collection<DocumentationSetDescriptor>
     */
    public function getDocumentationSets() : Collection
    {
        return $this->documentationSets;
    }

    public static function fromConfiguration(VersionSpecification $config) : self
    {
        $documentationSets = Collection::fromClassString(DocumentationSetDescriptor::class);

        foreach ($config->getGuides() ?? [] as $guide) {
            $documentationSets->add(new GuideSetDescriptor('', $guide['source'], $guide['output']));
        }

        foreach ($config->getApi() ?? [] as $api) {
            $documentationSets->add(new ApiSetDescriptor('', $api['source'], $api['output']));
        }

        return new self($config->getNumber(), $documentationSets);
    }
}
