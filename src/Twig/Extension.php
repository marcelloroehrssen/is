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
        );
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app';
    }
}