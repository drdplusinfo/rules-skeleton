<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Web\Tools\HtmlDocumentProcessorInterface;
use DrdPlus\RulesSkeleton\Web\Tools\RulesMainBodyPreProcessor;
use DrdPlus\RulesSkeleton\Web\Tools\WebPartsContainer;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\Body;
use Granam\WebContentBuilder\Web\WebFiles;

class RulesMainBody extends Body implements RulesBodyInterface
{
    /**
     * @var WebPartsContainer
     */
    private $webPartsContainer;
    /**
     * @var RulesMainBodyPreProcessor
     */
    private $rulesMainBodyPreProcessor;
    /**
     * @var HtmlDocumentProcessorInterface|null
     */
    private $htmlDocumentPostProcessor;

    public function __construct(
        WebFiles $webFiles,
        WebPartsContainer $webPartsContainer,
        RulesMainBodyPreProcessor $rulesMainBodyPreProcessor,
        ?HtmlDocumentProcessorInterface $htmlDocumentPostProcessor
    )
    {
        parent::__construct($webFiles);
        $this->webPartsContainer = $webPartsContainer;
        $this->rulesMainBodyPreProcessor = $rulesMainBodyPreProcessor;
        $this->htmlDocumentPostProcessor = $htmlDocumentPostProcessor;
    }

    protected function fetchPhpFileContent(string $file): string
    {
        $content = new class($file, $this->webPartsContainer) {
            /** @var string */
            private $file;
            /** @var WebPartsContainer */
            private $webPartsContainer;

            public function __construct(string $file, WebPartsContainer $webPartsContainer)
            {
                $this->file = $file;
                $this->webPartsContainer = $webPartsContainer;
            }

            public function fetchContent(): string
            {
                \extract(['webPartsContainer' => $this->webPartsContainer], \EXTR_SKIP);
                \ob_start();
                /** @noinspection PhpIncludeInspection */
                include $this->file;

                return \ob_get_clean();
            }
        };

        return $content->fetchContent();
    }

    public function preProcessDocument(HtmlDocument $htmlDocument): HtmlDocument
    {
        if (!$this->rulesMainBodyPreProcessor) {
            return $htmlDocument;
        }
        return $this->rulesMainBodyPreProcessor->processDocument($htmlDocument);
    }

    public function postProcessDocument(HtmlDocument $htmlDocument): HtmlDocument
    {
        if (!$this->htmlDocumentPostProcessor) {
            return $htmlDocument;
        }
        return $this->htmlDocumentPostProcessor->processDocument($htmlDocument);
    }
}