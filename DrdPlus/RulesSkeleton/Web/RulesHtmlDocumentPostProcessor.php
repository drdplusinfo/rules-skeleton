<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\CacheIdProvider;
use DrdPlus\RulesSkeleton\Configurations\PrefetchConfiguration;
use DrdPlus\RulesSkeleton\CurrentWebVersion;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Web\Menu\MenuBodyInterface;
use DrdPlus\RulesSkeleton\Web\Tools\HtmlDocumentProcessorInterface;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\Body;

class RulesHtmlDocumentPostProcessor implements HtmlDocumentProcessorInterface
{
    /** @var CurrentWebVersion */
    private $currentWebVersion;
    /** @var MenuBodyInterface */
    private $menuBody;
    /** @var Body */
    private $body;
    /** @var CacheIdProvider */
    private $cacheIdProvider;
    /** @var PrefetchConfiguration */
    private $prefetchConfiguration;

    public function __construct(
        MenuBodyInterface $menuBody,
        CurrentWebVersion $currentWebVersion,
        CacheIdProvider $cacheIdProvider,
        PrefetchConfiguration $prefetchConfiguration
    )
    {
        $this->currentWebVersion = $currentWebVersion;
        $this->menuBody = $menuBody;
        $this->cacheIdProvider = $cacheIdProvider;
        $this->prefetchConfiguration = $prefetchConfiguration;
    }

    public function processDocument(HtmlDocument $htmlDocument): HtmlDocument
    {
        $this->injectCacheStamp($htmlDocument);
        $this->injectMenuWrapper($htmlDocument);
        $this->injectCacheId($htmlDocument);
        $this->injectBackgroundWallpaper($htmlDocument);
        $this->injectPrefetch($htmlDocument);
        return $htmlDocument;
    }

    private function injectCacheStamp(HtmlDocument $htmlDocument): void
    {
        $patchVersion = $this->currentWebVersion->getCurrentPatchVersion();
        $htmlDocument->documentElement->setAttribute('data-content-version', $patchVersion);
        $htmlDocument->documentElement->setAttribute('data-cached-at', \date(\DATE_ATOM));
    }

    private function injectMenuWrapper(HtmlDocument $htmlDocument): void
    {
        $menuWrapper = $htmlDocument->createElement('div');
        $menuWrapper->setAttribute('id', HtmlHelper::ID_MENU_WRAPPER);
        $menuWrapper->prop_set_innerHTML($this->menuBody->getValue());
        $htmlDocument->body->insertBefore($menuWrapper, $htmlDocument->body->firstElementChild);
    }

    private function injectCacheId(HtmlDocument $htmlDocument): void
    {
        $htmlDocument->documentElement->setAttribute(HtmlHelper::DATA_CACHE_STAMP, $this->cacheIdProvider->getCacheId());
    }

    private function injectBackgroundWallpaper(HtmlDocument $htmlDocument): void
    {
        $this->injectBackgroundWallpaperPart($htmlDocument, HtmlHelper::CLASS_BACKGROUND_WALLPAPER_RIGHT_PART);
        $this->injectBackgroundWallpaperPart($htmlDocument, HtmlHelper::CLASS_BACKGROUND_WALLPAPER_LEFT_PART);
    }

    private function injectBackgroundWallpaperPart(HtmlDocument $htmlDocument, string $htmlClass): void
    {
        $backgroundWallpaper = $htmlDocument->createElement('div');
        $backgroundWallpaper->classList->add($htmlClass);
        $backgroundWallpaper->classList->add(HtmlHelper::CLASS_BACKGROUND_WALLPAPER);
        $backgroundWallpaper->classList->add(HtmlHelper::CLASS_BACKGROUND_RELATED);
        $htmlDocument->body->insertBefore($backgroundWallpaper, $htmlDocument->body->firstElementChild);
    }

    private function injectPrefetch(HtmlDocument $htmlDocument): void
    {
        $anchorsRegexp = $this->prefetchConfiguration->getAnchorsRegexp();
        if ($anchorsRegexp === '') {
            return;
        }
        $matchingHrefs = $this->getMatchingHrefs($anchorsRegexp, $htmlDocument);
        if (!$matchingHrefs) {
            return;
        }
        foreach (array_unique($matchingHrefs) as $matchingHref) {
            $this->injectPrefetchToHref($matchingHref, $htmlDocument);
        }
    }

    private function getMatchingHrefs(string $anchorsRegexp, HtmlDocument $htmlDocument): array
    {
        $anchors = $htmlDocument->body->getElementsByTagName('a');
        $matchingHrefs = [];
        foreach ($anchors as $anchor) {
            $href = (string)$anchor->getAttribute('href');
            if ($href === '') {
                continue;
            }
            if (preg_match($anchorsRegexp, $href)) {
                $matchingHrefs[] = $href;
            }
        }
        return $matchingHrefs;
    }

    private function injectPrefetchToHref(string $href, HtmlDocument $htmlDocument): void
    {
        $link = $htmlDocument->createElement('link');
        $link->setAttribute('rel', 'prefetch');
        $link->setAttribute('href', $href);
        $htmlDocument->head->appendChild($link);
    }

}
