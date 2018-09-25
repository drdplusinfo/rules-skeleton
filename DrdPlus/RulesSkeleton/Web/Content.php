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

    protected function buildHtmlDocument(string $content): HtmlDocument
    {
        $htmlDocument = new HtmlDocument($content);
        $this->getHtmlHelper()->prepareSourceCodeLinks($htmlDocument);
        $this->getHtmlHelper()->addIdsToTablesAndHeadings($htmlDocument);
        $this->getHtmlHelper()->replaceDiacriticsFromIds($htmlDocument);
        $this->getHtmlHelper()->replaceDiacriticsFromAnchorHashes($htmlDocument);
        $this->getHtmlHelper()->addAnchorsToIds($htmlDocument);
        $this->getHtmlHelper()->resolveDisplayMode($htmlDocument);
        $this->getHtmlHelper()->markExternalLinksByClass($htmlDocument);
        $this->getHtmlHelper()->externalLinksTargetToBlank($htmlDocument);
        $this->getHtmlHelper()->injectIframesWithRemoteTables($htmlDocument);
        $this->getHtmlHelper()->addVersionHashToAssets($htmlDocument);
        if (!$this->getHtmlHelper()->isInProduction()) {
            $this->getHtmlHelper()->makeExternalDrdPlusLinksLocal($htmlDocument);
        }
        $this->injectCacheId($htmlDocument);

        return $htmlDocument;
    }

    protected function getHtmlHelper(): HtmlHelper
    {
        return $this->htmlHelper;
    }

    protected function injectCacheId(HtmlDocument $htmlDocument): void
    {
        $htmlDocument->documentElement->setAttribute('data-cache-stamp', $this->getCache()->getCacheId());
    }

    protected function composeContent(): string
    {
        $patchVersion = $this->getWebVersions()->getCurrentPatchVersion();
        $now = \date(\DATE_ATOM);
        $head = $this->getHead()->getHeadString();
        $menu = $this->getMenu()->getMenuString();
        $body = $this->getBody()->getBodyString();

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

    protected function getHead(): Head
    {
        return $this->head;
    }

    protected function getMenu(): Menu
    {
        return $this->menu;
    }

    protected function getBody(): Body
    {
        return $this->body;
    }

    protected function getWebVersions(): WebVersions
    {
        return $this->webVersions;
    }

    protected function getCachedContent(): ?string
    {
        if ($this->getCache()->isCacheValid()) {
            return $this->getCache()->getCachedContent();
        }

        return null;
    }

    protected function getCache(): Cache
    {
        return $this->cache;
    }

    public function containsTables(): bool
    {
        return $this->contentType === self::TABLES;
    }

    public function containsFull(): bool
    {
        return $this->contentType === self::FULL;
    }

    protected function getContentType(): string
    {
        return $this->contentType;
    }

    public function getStringContent(): string
    {
        if ($this->containsPdf()) {
            return $this->getBody()->getBodyString();
        }
        $cachedContent = $this->getCachedContent();
        if ($cachedContent !== null) {
            return $cachedContent;
        }
        $previousMemoryLimit = \ini_set('memory_limit', '1G');
        $content = $this->composeContent();
        try {
            $this->getCache()->saveContentForDebug($content);
        } catch (\RuntimeException $runtimeException) {
            \trigger_error($runtimeException->getMessage() . "\n" . $runtimeException->getTraceAsString(), \E_USER_WARNING);
        }
        $htmlDocument = $this->buildHtmlDocument($content);
        $updatedContent = $htmlDocument->saveHTML();
        $this->getCache()->cacheContent($updatedContent);
        if ($previousMemoryLimit !== false) {
            \ini_set('memory_limit', $previousMemoryLimit);
        }

        // has to be AFTER cache as we do not want to cache it
        return $this->injectRedirectIfAny($updatedContent);
    }

    protected function injectRedirectIfAny(string $content): string
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

    protected function getRedirect(): ?Redirect
    {
        return $this->redirect;
    }

    public function containsPdf(): bool
    {
        return $this->getContentType() === self::PDF;
    }

    public function containsPass(): bool
    {
        return $this->getContentType() === self::PASS;
    }

}