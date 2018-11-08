<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 14:08
 */

namespace App\Utils;


use App\Entity\Settings;
use App\Entity\User;

class SettingsSystem
{
    private $settings = [
        'publishNewCharacter' => [
            'value' => 1,
            'label' => 'Pubblicato nuovo personaggio',
            'role' => 'ROLE_STORY_TELLER',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'deleteCharacter' => [
            'value' => 2,
            'label' => 'Personaggio cancellato',
            'role' => 'ROLE_STORY_TELLER',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'publishNewCharacterSheet' => [
            'value' => 4,
            'label' => 'Pubblicata nuova scheda',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'associateCharacter' => [
            'value' => 8,
            'label' => 'Personaggio associato',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'messageSent' => [
            'value' => 16,
            'label' => 'Messaggio Ricevuto',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'roleUpdated' => [
            'value' => 32,
            'label' => 'Ruolo aggiornato',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'connectionDone' => [
            'value' => 64,
            'label' => 'Connessione effettuata',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'connectionRemoved' => [
            'value' => 128,
            'label' => 'Connessione rimossa',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'connectionSend' => [
            'value' => 256,
            'label' => 'Richiesta connessione inviata',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'downtimeResolved' => [
            'value' => 512,
            'label' => 'Downtime risolto',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'newEventCreated' => [
            'value' => 1024,
            'label' => 'Nuovo evento creato',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'newEventProposalCreated' => [
            'value' => 2048,
            'label' => 'Nuova proposta per eliseo inviata',
            'role' => 'ROLE_EDILE',
            'site_checked' => true,
            'mail_checked' => false,
        ],
        'eventAssigned' => [
            'value' => 4096,
            'label' => 'Eevnto assegnato',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => false,
        ],
    ];

    private $loaded = false;

    public function load(User $user)
    {
        if ($this->loaded === true) {
            return $this;
        }
        $this->loaded = true;
        if ($user->getSettings() !== null) {

            $userSiteSetting = $user->getSettings()->getSiteValue();
            $userMailSetting = $user->getSettings()->getMailValue();

            foreach ($this->settings as &$setting)  {
                $setting['site_checked'] = ($setting['value'] & $userSiteSetting) == $setting['value'];
                $setting['mail_checked'] = ($setting['value'] & $userMailSetting) == $setting['value'];
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function setSetting(User $user, string $type, int $value, bool $isChecked)
    {
        $userSetting = $user->getSettings();
        if ($userSetting === null ) {
            $userSetting = new Settings();
            $user->setSettings($userSetting);
        }

        if ($type == Settings::TYPE_SITE) {
            $userSiteSetting = $userSetting->getSiteValue();

            if ($isChecked) {
                $userSiteSetting += $value;
            } else {
                $userSiteSetting -= $value;
            }

            $userSetting->setSiteValue($userSiteSetting);
        } else {
            $userMailSetting = $userSetting->getMailValue();

            if ($isChecked) {
                $userMailSetting += $value;
            } else {
                $userMailSetting -= $value;
            }

            $userSetting->setMailValue($userMailSetting);
        }
    }

    public function checkSiteSetting(User $user, string $function)
    {
        return $this->checkSetting($user, $function, Settings::TYPE_SITE);
    }

    public function checkMailSetting(User $user, string $function)
    {
        return $this->checkSetting($user, $function, Settings::TYPE_MAIL);
    }

    private function checkSetting(User $user, string $function, string $type)
    {
        $this->load($user);

        $userSetting = $user->getSettings();
        if ($userSetting === null ) {
            $userSetting = new Settings();
        }

        if ($type === Settings::TYPE_SITE) {
            $userSiteSetting = $userSetting->getSiteValue();
        } else {
            $userSiteSetting = $userSetting->getMailValue();
        }

        $value = $this->settings[$function]['value'];

        return ($userSiteSetting & $value) === $value;
    }
}