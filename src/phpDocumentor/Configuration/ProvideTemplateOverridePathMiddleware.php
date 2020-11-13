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

namespace phpDocumentor\Configuration;

use League\Uri\Contracts\UriInterface;
use phpDocumentor\Dsn;
use phpDocumentor\Path;
use phpDocumentor\Transformer\Writer\Twig\EnvironmentFactory;
use function file_exists;
use function getcwd;

/**
 * Determines the path where the general template overrides are.
 *
 * Since phpDocumentor 3, we now have the feature that in the subfolder '.phpdoc/template' relative to where your
 * configuration file resides you can add twig templates with the same name as those used in a template. When you
 * do this, that file will be used instead of the one in the template, and you can customize rendering this way.
 *
 * This configuration middleware is responsible for finding the path where these overrides are, and adding this to the
 * Twig Environment Factory so that it can be added as a template source once the Twig Environment is created.
 */
final class ProvideTemplateOverridePathMiddleware implements MiddlewareInterface
{
    public const PATH_TO_TEMPLATE_OVERRIDES = '.phpdoc/template';

    /** @var EnvironmentFactory */
    private $environmentFactory;

    public function __construct(EnvironmentFactory $environmentFactory)
    {
        $this->environmentFactory = $environmentFactory;
    }

    //phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * @param array{phpdocumentor: array{configVersion: string, title?: string, use-cache?: bool, paths?: array{output: string, cache: string}, versions?: array<string, array{api: array<int, array{ignore-tags: array, extensions: non-empty-array<string>, markers: non-empty-array<string>, visibillity: string, source: array{dsn: Dsn, paths: array}, ignore: array{paths: array}}>, apis: array, guides: array}>, settings?: array<mixed>, templates?: non-empty-list<string>}} $configuration
     *
     * @return array{phpdocumentor: array{configVersion: string, title?: string, use-cache?: bool, paths?: array{output: string, cache: string}, versions?: array<string, array{api: array<int, array{ignore-tags: array, extensions: non-empty-array<string>, markers: non-empty-array<string>, visibillity: string, source: array{dsn: Dsn, paths: array}, ignore: array{paths: array}}>, apis: array, guides: array}>, settings?: array<mixed>, templates?: non-empty-list<string>}}
     */
    //phpcs:enable Generic.Files.LineLength.TooLong
    public function __invoke(array $configuration, ?UriInterface $pathOfConfigFile = null) : array
    {
        $path = $this->normalizePath($pathOfConfigFile, new Path(self::PATH_TO_TEMPLATE_OVERRIDES));
        if (file_exists((string) $path)) {
            $this->environmentFactory->withTemplateOverridesAt($path);
        }

        return $configuration;
    }

    private function normalizePath(?UriInterface $uri, Path $path) : Path
    {
        if ($uri === null) {
            return new Path(getcwd() . '/' . $path);
        }

        $configFile = Dsn::createFromUri($uri);
        $configPath = $configFile->withPath(Path::dirname($configFile->getPath()));

        return Dsn::createFromString((string) $path)->resolve($configPath)->getPath();
    }
}
