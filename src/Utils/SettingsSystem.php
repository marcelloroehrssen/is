<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 14:08.
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
            'mail_checked' => true,
        ],
        'deleteCharacter' => [
            'value' => 2,
            'label' => 'Personaggio cancellato',
            'role' => 'ROLE_STORY_TELLER',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'publishNewCharacterSheet' => [
            'value' => 4,
            'label' => 'Pubblicata nuova scheda',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'associateCharacter' => [
            'value' => 8,
            'label' => 'Personaggio associato',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'messageSent' => [
            'value' => 16,
            'label' => 'Messaggio Ricevuto',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'roleUpdated' => [
            'value' => 32,
            'label' => 'Ruolo aggiornato',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'connectionDone' => [
            'value' => 64,
            'label' => 'Connessione effettuata',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'connectionRemoved' => [
            'value' => 128,
            'label' => 'Connessione rimossa',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'connectionSend' => [
            'value' => 256,
            'label' => 'Richiesta connessione inviata',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'downtimeResolved' => [
            'value' => 512,
            'label' => 'Downtime risolto',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'newEventCreated' => [
            'value' => 1024,
            'label' => 'Nuovo evento creato',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'newEventProposalCreated' => [
            'value' => 2048,
            'label' => 'Nuova proposta per eliseo inviata',
            'role' => 'ROLE_EDILE',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'eventAssigned' => [
            'value' => 4096,
            'label' => 'Evento assegnato',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'equipmentReceived' => [
            'value' => 8192,
            'label' => 'Oggetto ricevuto',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'equipmentRequestReceived' => [
            'value' => 8192,
            'label' => 'Richiesta di ricezione oggetto',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'equipmentRequestDenied' => [
            'value' => 16384,
            'label' => 'Richiesta di ricezione oggetto rifiutata',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
        'equipmentRequestAccepted' => [
            'value' => 32768,
            'label' => 'Richiesta di ricezione oggetto accettata',
            'role' => 'ROLE_REGISTERED',
            'site_checked' => true,
            'mail_checked' => true,
        ],
    ];

    private $loaded = false;

    public function load(User $user)
    {
        if (true === $this->loaded) {
            return $this;
        }
        $this->loaded = true;
        if (null !== $user->getSettings()) {
            $userSiteSetting = $user->getSettings()->getSiteValue();
            $userMailSetting = $user->getSettings()->getMailValue();

            foreach ($this->settings as &$setting) {
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
        if (null == $userSetting) {
            $userSetting = new Settings();
            $user->setSettings($userSetting);
        }

        if (Settings::TYPE_SITE == $type) {
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
        if (null == $userSetting) {
            $userSetting = new Settings();
        }

        if (Settings::TYPE_SITE === $type) {
            $userSiteSetting = $userSetting->getSiteValue();
        } else {
            $userSiteSetting = $userSetting->getMailValue();
        }

        $value = $this->settings[$function]['value'];

        return ($userSiteSetting & $value) === $value;
    }
}
