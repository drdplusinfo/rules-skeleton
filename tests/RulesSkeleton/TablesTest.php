<?php declare(strict_types=1);

namespace Tests\DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Request;
use Tests\DrdPlus\RulesSkeleton\Partials\AbstractContentTest;
use Granam\WebContentBuilder\HtmlDocument;
use Gt\Dom\Element;
use Gt\Dom\NodeList;

class TablesTest extends AbstractContentTest
{
    /**
     * @test
     * @dataProvider provideParametersToGetTablesOnly
     * @param array $get
     * @param string $url
     */
    public function I_can_get_tables_only(array $get, string $url): void
    {
        $this->goOut(); // tables should be accessible for free
        $htmlDocumentWithTablesOnly = $this->getHtmlDocument($get, [], [], $url);
        /** @var NodeList|Element[] $tables */
        $tables = $htmlDocumentWithTablesOnly->getElementsByTagName('table');
        $expectedTableIds = $this->getTableIds();
        if (!$this->getTestsConfiguration()->hasTables()) {
            self::assertCount(0, $tables, 'No tables expected due to tests configuration');
            self::assertCount(0, $expectedTableIds, 'No tables expected due to tests configuration');
        } else {
            self::assertGreaterThan(0, is_countable($tables) ? count($tables) : 0, 'Some tables expected due to tests configuration');
            self::assertGreaterThan(0, count($expectedTableIds), 'Some tables expected due to tests configuration');
        }
        $fetchedTableIds = $this->getElementsIds($tables);
        $missingIds = \array_diff($expectedTableIds, $fetchedTableIds);
        self::assertEmpty($missingIds, 'Some tables with IDs are missing: ' . \implode(',', $missingIds));
        $this->There_is_no_other_content_than_tables($htmlDocumentWithTablesOnly);
        $this->Expected_table_ids_are_present($fetchedTableIds);
    }

    public function provideParametersToGetTablesOnly(): array
    {
        return [
            'via query parameter' => [[Request::TABLES => '' /* all of them */], '/'],
            'via english path' => [[], '/' . Request::TABLES],
            'via czech path' => [[], '/' . Request::TABULKY],
        ];
    }

    protected function getTableIds(): array
    {
        static $tableIds;
        if ($tableIds === null) {
            $this->goIn(); // parse table IDs from passed content
            $tableIds = $this->parseTableIds($this->getHtmlDocument());
            \sort($tableIds);
            $this->Expected_table_ids_are_present($tableIds);
        }

        return $tableIds;
    }

    private function getElementsIds(\Traversable $elements): array
    {
        return \array_map(
            fn(Element $element): string => $this->getHtmlHelper()->getFirstIdFrom($element),
            $this->traversableToArray($elements)
        );
    }

    private function traversableToArray(\Traversable $iterable): array
    {
        $array = [];
        foreach ($iterable as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }

    protected function Expected_table_ids_are_present(array $tableIds): void
    {
        $someExpectedTableIds = $this->getTestsConfiguration()->getSomeExpectedTableIds();
        $missingIds = \array_diff($someExpectedTableIds, $tableIds);
        self::assertEmpty(
            $missingIds,
            sprintf(
                'Some expected table IDs are missing: %s, got only %s',
                implode(',', $missingIds),
                implode(',', $tableIds)
            )
        );
    }

    protected function There_is_no_other_content_than_tables(HtmlDocument $htmlDocument): void
    {
        $menuWrapper = $htmlDocument->getElementById(HtmlHelper::ID_MENU_WRAPPER);
        $menuWrapper->remove();

        $classesToRemove = [
            HtmlHelper::CLASS_INVISIBLE_ID,
            HtmlHelper::CLASS_INVISIBLE,
            HtmlHelper::CLASS_TABLES_RELATED,
            HtmlHelper::CLASS_BACKGROUND_RELATED,
        ];
        foreach ($classesToRemove as $classToRemove) {
            foreach ($htmlDocument->getElementsByClassName($classToRemove) as $elementToRemove) {
                $elementToRemove->remove();
            }
        }
        foreach ($htmlDocument->body->children as $child) {
            self::assertSame(
                'table',
                $child->tagName,
                'Expected only tables, got ' . $child->outerHTML
            );
        }
    }

    /**
     * @test
     */
    public function I_can_get_wanted_tables_from_content(): void
    {
        if (!$this->getTestsConfiguration()->hasTables()) {
            self::assertFalse(false, 'Disabled by tests configuration');

            return;
        }
        $tableIds = $this->getTableIds();
        $implodedTableIds = \implode(',', $tableIds);
        $htmlDocument = $this->getHtmlDocument([Request::TABLES => $implodedTableIds]);
        $tables = $htmlDocument->body->getElementsByTagName('table');
        self::assertGreaterThan(
            0,
            $tables->count(),
            \sprintf(
                'No tables have been fetched from %s, when required IDs %s',
                $this->getTestsConfiguration()->getLocalUrl() . '?' . Request::TABLES . '=' . \urlencode($implodedTableIds),
                $implodedTableIds
            )
        );
        self::assertCount(count($tableIds), $tables, 'Expected same amount of tables as requested');
        self::assertSame(
            [],
            \array_diff($this->getTestsConfiguration()->getSomeExpectedTableIds(), $tableIds),
            'Some expected table IDs are missing'
        );
        $this->There_is_no_other_content_than_tables($htmlDocument);
    }

    /**
     * @test
     */
    public function I_can_get_tables_related_content(): void
    {
        if (!$this->getTestsConfiguration()->hasTables()) {
            self::assertFalse(false, 'Disabled by tests configuration');

            return;
        }
        $htmlDocument = $this->getHtmlDocument([], [], [], '/tables');
        $tablesRelatedElements = $htmlDocument->body->getElementsByClassName(HtmlHelper::CLASS_TABLES_RELATED);
        if (!$this->getTestsConfiguration()->hasTablesRelatedContent()) {
            self::assertCount(
                0,
                $tablesRelatedElements,
                \sprintf(
                    "No tables-related content expected when fetching %s as tests configuration '%s' says",
                    $this->getTestsConfiguration()->getLocalUrl() . '/tables',
                    TestsConfiguration::HAS_TABLES_RELATED_CONTENT
                )
            );
            return;
        }
        self::assertGreaterThan(
            0,
            count($tablesRelatedElements),
            sprintf(
                "Expected some tables-related content when fetching %s as tests configuration '%s' says",
                $this->getTestsConfiguration()->getLocalUrl() . '/tables',
                TestsConfiguration::HAS_TABLES_RELATED_CONTENT
            )
        );
    }

    /**
     * @test
     */
    public function I_can_get_tables_only_even_with_query_in_url(): void
    {
        if ($this->getTestsConfiguration()->hasTables()) {
            $tablesWithQuery = $this->getHtmlDocument(['foo' => 'bar'], [], [], '/tables');
            self::assertGreaterThan(
                0,
                count($tablesWithQuery->getElementsByTagName('table')),
                'Seems tables with query has broken routing, try URL /tables?foo=bar'
            );
        } else {
            $tablesRoute = $this->getTestsConfiguration()->getLocalUrl() . '/tables?foo=bar&' . Request::TRIAL . '=1';

            $this->goIn();
            $response = $this->fetchContentFromUrl($tablesRoute, false);
            $this->goOut();

            self::assertContains(
                $response['responseHttpCode'],
                [200, 201, 202, 203],
                sprintf(
                    'Seems tables with query has broken routing, try URL %s (%s)',
                    $tablesRoute,
                    json_encode($this->shortenContentInResponse($response), JSON_PRETTY_PRINT)
                )
            );
        }
    }

    private function shortenContentInResponse(array $response): array
    {
        if (!$response['content']) {
            return $response;
        }
        $response['content'] = mb_strlen($response['content']) > 123
            ? (mb_substr($response['content'], 0, 120) . '...')
            : $response['content'];
        return $response;
    }
}
