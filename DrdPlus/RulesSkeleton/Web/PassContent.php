<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Environment;
use DrdPlus\RulesSkeleton\HtmlHelper;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\Content;
use Granam\WebContentBuilder\Web\HeadInterface;

class PassContent extends Content
{
    /** @var HtmlHelper */
    protected $htmlHelper;
    /** @var Environment */
    protected $environment;

    public function __construct(HtmlHelper $htmlHelper, Environment $environment, HeadInterface $head, PassBody $passBody)
    {
        parent::__construct($htmlHelper, $head, $passBody);
        $this->htmlHelper = $htmlHelper;
        $this->environment = $environment;
    }

    protected function buildHtmlDocument(string $content): HtmlDocument
    {
        $htmlDocument = new HtmlDocument($content);
        $htmlDocument->body->classList->add('container');
        $this->solveIds($htmlDocument);
        $this->solveLinks($htmlDocument);

        return $htmlDocument;
    }

    private function solveIds(HtmlDocument $htmlDocument): void
    {
        $this->htmlHelper->unifyIds($htmlDocument);
        $this->htmlHelper->replaceDiacriticsFromDrdPlusAnchorHashes($htmlDocument);
    }

    private function solveLinks(HtmlDocument $htmlDocument): void
    {
        $this->htmlHelper->externalLinksTargetToBlank($htmlDocument);
        $this->htmlHelper->markExternalLinksByClass($htmlDocument);
        $this->htmlHelper->addVersionHashToAssets($htmlDocument);
        if (!$this->environment->isInProduction()) {
            $this->htmlHelper->makeExternalDrdPlusLinksLocal($htmlDocument);
        }
    }
}