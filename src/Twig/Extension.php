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

    public function __construct($characterSheetPath)
    {
        $this->characterSheetPath = $characterSheetPath;
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
            new TwigFilter('unread', [$this, 'unreadNotification']),
            new TwigFilter('strftime', [$this, 'strftime']),
            new TwigFilter('file_exists', [$this, 'fileExists']),
            new TwigFilter('clean', [$this, 'clean']),
            new TwigFilter('datediffFromatted', [$this, 'datediffFromatted']),
            new TwigFilter('code', [$this, 'generateCode']),
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app';
    }
}
