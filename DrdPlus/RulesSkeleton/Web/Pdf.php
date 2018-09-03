<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Dirs;
use Granam\Strict\Object\StrictObject;

class Pdf extends StrictObject
{
    /**
     * @var Dirs
     */
    private $dirs;

    public function __construct(Dirs $dirs)
    {
        $this->dirs = $dirs;
    }

    public function sendPdf(): ?int
    {
        if (($_SERVER['QUERY_STRING'] ?? false) !== 'pdf' || !\file_exists($this->dirs->getDocumentRoot() . '/pdf')
        ) {
            return null;
        }
        $pdfFiles = glob($this->dirs->getDocumentRoot() . '/pdf/*.pdf');
        if (!$pdfFiles === 0) {
            return null;
        }
        $pdfFile = $pdfFiles[0];
        $pdfFileBasename = \basename($pdfFile);
        \header('Content-type: application/pdf');
        \header('Content-Length: ' . \filesize($pdfFile));
        \header("Content-Disposition: attachment; filename=\"$pdfFileBasename\"");

        return (int)\readfile($pdfFile);
    }
}