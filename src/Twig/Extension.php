<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 21/05/2018
 * Time: 03:41.
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Utils\CacophonySavyUtil;

class Extension extends AbstractExtension
{
    protected $characterSheetPath;

    protected $kernelDir;

    public function __construct($characterSheetPath, string $kernelDir)
    {
        $this->characterSheetPath = $characterSheetPath;
        $this->kernelDir = $kernelDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('ue', [$this, 'html_decode']),
            new TwigFilter('date_diff', [$this, 'date_diff']),
            new TwigFilter('cacophony_savy', [$this, 'cacophonySavy']),
            new TwigFilter('strftime', [$this, 'strftime']),
            new TwigFilter('file_exists', [$this, 'fileExists']),
            new TwigFilter('clean', [$this, 'clean']),
            new TwigFilter('datediffFromatted', [$this, 'datediffFromatted']),
            new TwigFilter('code', [$this, 'generateCode']),
            new TwigFilter('tags', [$this, 'printTags'], ['is_safe' => ['html']]),
            new TwigFilter('get_qr_code_path', [$this, 'getQrCodePath']),
        ];
    }

    public function clean($value)
    {
        return html_entity_decode(strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_COMPAT, 'UTF-8')), ENT_QUOTES | ENT_COMPAT, 'UTF-8');
    }

    public function html_decode($value)
    {
        return html_entity_decode($value);
    }

    public function date_diff($value, $date2 = null)
    {
        if (null === $date2) {
            $date2 = new \DateTime();
        }
        $interval = date_diff($value, $date2);

        return $interval->format('%a');
    }

    public function cacophonySavy($value)
    {
        return CacophonySavyUtil::encode($value);
    }

    public function strftime($date, $modifiers = '%A %d %B %H:%M')
    {
        setlocale(LC_ALL, 'it_IT');
        $date = strftime($modifiers, $date->getTimestamp());

        return utf8_encode($date);
    }

    public function fileExists($fileName)
    {
        return file_exists(sprintf('%s/%s', $this->characterSheetPath, $fileName));
    }

    public function datediffFromatted(\DateTime $date)
    {
        $date = $this->date_diff($date);

        return $date > 1 ? "mancano $date giorni" : "manca $date giorno";
    }

    public function generateCode($seed)
    {
        return dechex(rand(1, 255));
    }

    public function printTags($character)
    {
        $figs = $character->getExtra()->getTitle() ?? $character->getFigs()->getName();
        $rankName = $character->getRank()->getName();
        $city = $character->getExtra()->getCity();
        $covenantName = $character->getCovenant()->getName();
        $clanName = $character->getClan()->getName();
        $labels = <<<EOF
        <span class="label label-primary">$rankName</span>&nbsp;<span class="label label-warning">$figs</span>&nbsp;<span class="label label-danger">$city</span>&nbsp;<span class="label label-default">$covenantName</span>&nbsp;<span class="label label-info">$clanName</span>&nbsp;
EOF;

        return trim($labels);
    }

    public function getQrCodePath($code, $size = 150)
    {
        $path = [
            $this->kernelDir,
            'public',
            'images',
            $size . '-' . $code . '.png',
        ];
        return implode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app';
    }
}
