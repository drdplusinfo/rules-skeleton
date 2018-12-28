<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Cache;
use DrdPlus\RulesSkeleton\HtmlDocument;
use DrdPlus\RulesSkeleton\WebVersions;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Redirect;
use Granam\Strict\Object\StrictObject;

class Content extends StrictObject
{
    public const TABLES = 'tables';
    public const FULL = ' full';
    public const PDF = 'pdf';
    public const PASS = 'pass';

    /** @var HtmlHelper */
    private $htmlHelper;
    /** @var WebVersions */
    private $webVersions;
    /** @var Head */
    private $head;
    /** @var Menu */
    private $menu;
    /** @var Body */
    private $body;
    /** @var Cache */
    private $cache;
    /** @var string */
    private $contentType;
    /** @var Redirect|null */
    private $redirect;

    public function __construct(
        HtmlHelper $htmlHelper,
        WebVersions $webVersions,
        Head $head,
        Menu $menu,
        Body $body,
        Cache $cache,
        string $contentType,
        ?Redirect $redirect
    )
    {
        $this->htmlHelper = $htmlHelper;
        $this->webVersions = $webVersions;
        $this->head = $head;
        $this->menu = $menu;
        $this->body = $body;
        $this->cache = $cache;
        $this->contentType = $contentType;
        $this->redirect = $redirect;
    }

    public function __toString()
    {
        return $this->getStringContent();
    }

    public function containsTables(): bool
    {
        return $this->contentType === self::TABLES;
    }

    public function containsFull(): bool
    {
        return $this->contentType === self::FULL;
    }

    public function getStringContent(): string
    {
        if ($this->containsPdf()) {
            return $this->body->getBodyString();
        }
        $cachedContent = $this->getCachedContent();
        if ($cachedContent !== null) {
            return $this->injectRedirectIfAny($cachedContent);
        }
        $previousMemoryLimit = \ini_set('memory_limit', '1G');
        $content = $this->composeContent();
        try {
            $this->cache->saveContentForDebug($content);
        } catch (\RuntimeException $runtimeException) {
            \trigger_error($runtimeException->getMessage() . "\n" . $runtimeException->getTraceAsString(), \E_USER_WARNING);
        }
        $htmlDocument = $this->buildHtmlDocument($content);
        $updatedContent = $htmlDocument->saveHTML();
        $this->cache->cacheContent($updatedContent);
        if ($previousMemoryLimit !== false) {
            \ini_set('memory_limit', $previousMemoryLimit);
        }

        // has to be AFTER cache as we do not want to cache it
        return $this->injectRedirectIfAny($updatedContent);
    }

    private function buildHtmlDocument(string $content): HtmlDocument
    {
        $htmlDocument = new HtmlDocument($content);
        $this->htmlHelper->prepareSourceCodeLinks($htmlDocument);
        $this->htmlHelper->addIdsToTablesAndHeadings($htmlDocument);
        $this->htmlHelper->replaceDiacriticsFromIds($htmlDocument);
        $this->htmlHelper->replaceDiacriticsFromAnchorHashes($htmlDocument);
        $this->htmlHelper->addAnchorsToIds($htmlDocument);
        $this->htmlHelper->resolveDisplayMode($htmlDocument);
        $this->htmlHelper->markExternalLinksByClass($htmlDocument);
        $this->htmlHelper->externalLinksTargetToBlank($htmlDocument);
        $this->htmlHelper->injectIframesWithRemoteTables($htmlDocument);
        $this->htmlHelper->addVersionHashToAssets($htmlDocument);
        if (!$this->htmlHelper->isInProduction()) {
            $this->htmlHelper->makeExternalDrdPlusLinksLocal($htmlDocument);
        }
        $this->injectCacheId($htmlDocument);

        return $htmlDocument;
    }

    private function injectCacheId(HtmlDocument $htmlDocument): void
    {
        $htmlDocument->documentElement->setAttribute('data-cache-stamp', $this->cache->getCacheId());
    }

    private function composeContent(): string
    {
        $patchVersion = $this->webVersions->getCurrentPatchVersion();
        $now = \date(\DATE_ATOM);
        $head = $this->head->getHeadString();
        $menu = $this->menu->getMenuString();
        $body = $this->body->getBodyString();

        return <<<HTML
<!DOCTYPE html>
<html lang="cs" data-content-version="{$patchVersion}" data-cached-at="{$now}">
<head>
    {$head}
</head>
<body class="container">
    {$menu}
    {$body}
</body>
</html>
HTML;
    }

    private function getCachedContent(): ?string
    {
        if ($this->cache->isCacheValid()) {
            return $this->cache->getCachedContent();
        }

        return null;
    }

    private function injectRedirectIfAny(string $content): string
    {
        if (!$this->getRedirect()) {
            return $content;
        }
        $cachedDocument = new HtmlDocument($content);
        $meta = $cachedDocument->createElement('meta');
        $meta->setAttribute('http-equiv', 'Refresh');
        $meta->setAttribute('content', $this->getRedirect()->getAfterSeconds() . '; url=' . $this->getRedirect()->getTarget());
        $meta->setAttribute('id', 'meta_redirect');
        $cachedDocument->head->appendChild($meta);

        return $cachedDocument->saveHTML();
    }

    private function getRedirect(): ?Redirect
    {
        return $this->redirect;
    }

    public function containsPdf(): bool
    {
        return $this->contentType === self::PDF;
    }

    public function containsPass(): bool
    {
        return $this->contentType === self::PASS;
    }

}