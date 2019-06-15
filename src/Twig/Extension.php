<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 21/05/2018
 * Time: 03:41
 */

namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Utils\CacophonySavyUtil;
use App\Entity\Notifications;

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
        return array(
            new TwigFilter('ue', array($this, 'html_decode')),
            new TwigFilter('date_diff', array($this, 'date_diff')),
            new TwigFilter('cacophony_savy', array($this, 'cacophonySavy')),
            new TwigFilter('unread', array($this, 'unreadNotification')),
            new TwigFilter('strftime', array($this, 'strftime')),
            new TwigFilter('file_exists', array($this, 'fileExists')),
            new TwigFilter('clean', array($this, 'clean')),
            new TwigFilter('datediffFromatted', array($this, 'datediffFromatted')),
            new TwigFilter('code', array($this, 'generateCode')),
            new TwigFilter('tags', array($this, 'printTags'), array('is_safe' => array('html'))),
        );
    }
    
    public function clean($value)
    {
        return html_entity_decode(strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_COMPAT, 'UTF-8')),  ENT_QUOTES | ENT_COMPAT, 'UTF-8');
    }
    
    public function html_decode($value)
    {
        return html_entity_decode($value);
    }

    public function date_diff($value, $date2 = null)
    {
        if ($date2 === null) {
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
	    return dechex(rand(1,255)); 	    
	}

	public function printTags($character)
    {
        $figs = $character->getExtra()->getTitle() ?? $character->getFigs()->getName();
        $rankName = $character->getRank()->getName();
        $city = $character->getExtra()->getCity();
        $covenantName = $character->getCovenant()->getName();
        $clanName = $character->getClan()->getName();
        $labels =<<<EOF
        <span class="label label-primary">$rankName</span>&nbsp;<span class="label label-warning">$figs</span>&nbsp;<span class="label label-danger">$city</span>&nbsp;<span class="label label-default">$covenantName</span>&nbsp;<span class="label label-info">$clanName</span>&nbsp;
EOF;
        return trim($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app';
    }
}
