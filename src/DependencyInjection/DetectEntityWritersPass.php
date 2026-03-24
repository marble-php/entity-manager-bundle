<?php

declare(strict_types=1);

namespace Marble\EntityManager\Bundle\DependencyInjection;

use Marble\EntityManager\Contract\EntityWriter;

/**
 * @api
 */
class DetectEntityWritersPass extends AbstractDetectMarbleImplementationPass
{
    #[\Override]
    protected function getBaseClass(): string
    {
        return EntityWriter::class;
    }

    #[\Override]
    protected function getInterface(): ?string
    {
        return EntityWriter::class;
    }

    #[\Override]
    protected function getServiceTagName(): string
    {
        return 'marble.entity_manager.entity_writer';
    }

    #[\Override]
    protected function getLocatorServiceId(): string
    {
        return 'marble.entity_manager.entity_writer_locator';
    }
}
