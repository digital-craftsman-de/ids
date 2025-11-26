<?php

declare(strict_types=1);

namespace DigitalCraftsman\Ids\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @codeCoverageIgnore
 */
final class IdsExtension extends Extension
{
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
    }
}
