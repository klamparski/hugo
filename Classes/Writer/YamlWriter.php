<?php

namespace SourceBroker\Hugo\Writer;

use SourceBroker\Hugo\Domain\Model\Document;
use SourceBroker\Hugo\Domain\Model\DocumentCollection;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class YamlWriter
 * @package SourceBroker\Hugo\Writer
 */
class YamlWriter implements WriterInterface
{
    /**
     * @var string
     */
    protected $ext = 'md';

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @param string $path
     */
    public function setRootPath(string $path): void
    {
        if (strlen($path) === 0) {
            new \Exception('$path is empty when setting setRootPath in YamlWriter');
        } else {
            $this->rootPath = rtrim($path, DIRECTORY_SEPARATOR) . '/';
        }
    }

    /**
     * @param Document $document
     * @param array $path
     */
    public function save(Document $document, array $path): void
    {
        switch ($document->getType()) {
            case Document::TYPE_PAGE:
                $documentName = '_index';
                break;
            default:
                if (empty($document->getId())) {
                    throw new \RuntimeException('Id of document is missing', 1693179681746);
                }

                $documentName = $document->getId() . '_' . ucfirst($document->getSlug());
        }

        $filename = $documentName . '.' . $this->ext;

        $fullPath = GeneralUtility::getFileAbsFileName($this->rootPath . implode('/', $path)) . '/' . $filename;

        $content = "---\n" . Yaml::dump($document->getFrontMatter()) . "---\n";

        GeneralUtility::mkdir_deep(dirname($fullPath));

        file_put_contents($fullPath, $content);
    }

    /**
     * @param DocumentCollection $collection
     * @param array $path
     */
    public function saveDocuments(DocumentCollection $collection, array $path): void
    {
        foreach ($collection as $document) {
            $this->save($document, $path);
        }
    }

    /**
     * clean root path folder
     */
    public function clean(): void
    {
        // Kind of protection to not remove too much if $this->rootPath is empty or set for wrong folder.
        if (strlen($this->rootPath) !== 0 && (
                file_exists(PATH_site . $this->rootPath . '../config.yml')
                || file_exists(PATH_site . $this->rootPath . '../config.yaml')
                || file_exists(PATH_site . $this->rootPath . '../config.toml')
                || file_exists(PATH_site . $this->rootPath . '../config.json')
            )) {
            GeneralUtility::rmdir(PATH_site . $this->rootPath, true);
        }
    }
}