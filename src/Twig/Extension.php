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
		setlocale(LC_TIME, 'it_IT');
		$date = strftime($modifiers, $date->getTimestamp());
		return utf8_encode($date);
	}
	
	public function fileExists($fileName)
	{
	    return file_exists($fileName);
	}
	
	public function datediffFromatted(\DateTime $date)
	{
	    $date = $this->date_diff($date);
	    return $date > 1 ? "mancano $date giorni" : "manca $date giorno";
	}

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app';
    }
}
