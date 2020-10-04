<?php

namespace Framework3\Templating;

use Framework3\ClassFinder\AutoloadTools;
use Framework3\Config\ConfigBag;
use Framework3\Exception\AppException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Class Twig
 *
 * @package Climb\Templating
 */
class Twig
{
    /**
     * @var AutoloadTools
     */
    private AutoloadTools $autoloadTools;

    /**
     * @var ConfigBag
     */
    private ConfigBag $config;

    /**
     * @var string
     */
    private string $baseDir;

    /**
     * @param AutoloadTools $autoloadTools
     * @param ConfigBag     $config
     * @param string        $baseDir
     */
    public function __construct(AutoloadTools $autoloadTools, ConfigBag $config, string $baseDir)
    {
        $this->autoloadTools = $autoloadTools;
        $this->config        = $config;
        $this->baseDir       = $baseDir;
    }

    /**
     * Returns framework template dir relative to BASE_DIR .env var.
     *
     * @return string
     */
    private function getFrameworkTemplateDir(): string
    {
        return str_replace($this->baseDir, '', __DIR__ . '/Template');
    }

    /**
     * Returns an array of template dirs relatives to BASE_DIR .env var.
     *
     * @return string[]
     *
     * @throws AppException
     */
    private function getTemplatingDirs(): array
    {
        $dirs = [
            $this->getFrameworkTemplateDir()
        ];

        foreach ($this->config->get('TEMPLATE_DIR', true) as $namespace) {
            $dir    = $this->autoloadTools->getDirPathFromNamespace($namespace);
            $dirs[] = $this->baseDir . DIRECTORY_SEPARATOR . $dir;
        }

        return $dirs;
    }

    /**
     * @return Environment
     *
     * @throws AppException
     */
    public function getEnvironment(): Environment
    {
        // fileSystem
        $loader = new FilesystemLoader($this->getTemplatingDirs(), $this->baseDir);

        // Twig Environment
        return new Environment($loader, $this->config->get('OPTIONS', true));
    }
}
