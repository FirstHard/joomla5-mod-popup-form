<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_popup_form
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use Joomla\Database\ParameterType;

class ModPopupFormHelper
{
    /**
     * Ajax form handler.
     *
     * URL: index.php?option=com_ajax&module=popup_form&method=submit&format=json
     *
     * @return array
     */
    public static function submitAjax(): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $moduleId = (int) $input->getInt('module_id', 0);

        $module = null;
        if ($moduleId > 0) {
            try {
                $db = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from($db->quoteName('#__modules'))
                    ->where($db->quoteName('id') . ' = ' . (int) $moduleId);

                $db->setQuery($query);
                $module = $db->loadObject();
            } catch (\Throwable $e) {
                if (\defined('JDEBUG') && JDEBUG) {
                    Log::add(
                        'mod_popup_form: Error loading module by id ' . $moduleId . ' - ' . $e->getMessage(),
                        Log::WARNING,
                        'mod_popup_form'
                    );
                }
            }
        }

        if (!$module) {
            $module           = new \stdClass();
            $module->id       = $moduleId;
            $module->params   = '{}';
        }

        $paramsRaw = $module->params ?? '{}';
        $params    = new Registry($paramsRaw);

        $recipientMode = (string) $params->get('recipient_mode', 'email');
        $emailTo       = null;

        if ($recipientMode === 'contact') {
            $requestContactId = $input->getInt('contact_id', 0);
            $contactId = $requestContactId ?: (int) $params->get('contact_id', 0);

            if ($contactId > 0) {
                try {
                    $db = Factory::getContainer()->get('DatabaseDriver');
                    $query = $db->getQuery(true)
                        ->select($db->quoteName('email_to'))
                        ->from($db->quoteName('#__contact_details'))
                        ->where($db->quoteName('id') . ' = ' . (int) $contactId)
                        ->where($db->quoteName('published') . ' = 1');

                    $db->setQuery($query);
                    $emailFromContact = (string) $db->loadResult();

                    if ($emailFromContact && filter_var($emailFromContact, FILTER_VALIDATE_EMAIL)) {
                        $emailTo = $emailFromContact;
                    }
                } catch (\Throwable $e) {
                    if (\defined('JDEBUG') && JDEBUG) {
                        Log::add(
                            'mod_popup_form: Error fetching contact email - ' . $e->getMessage(),
                            Log::WARNING,
                            'mod_popup_form'
                        );
                    }
                }
            }

            if (!$emailTo) {
                $emailTo = trim((string) $params->get('email_to', ''));
            }
        } else {
            $emailTo = trim((string) $params->get('email_to', ''));
        }

        if (!filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException(Text::_('MOD_POPUP_FORM_ERROR_INVALID_EMAIL_TO'), 500);
        }

        $formFields = $params->get('form_fields', []);

        if (is_string($formFields)) {
            $decoded = json_decode($formFields, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $formFields = $decoded;
            }
        }

        if ($formFields instanceof Registry) {
            $formFields = $formFields->toArray();
        }

        if (is_object($formFields)) {
            $formFields = (array) $formFields;
        }

        if (empty($formFields) || !is_array($formFields)) {
            $formFields = [
                [
                    'name'           => 'name',
                    'label'          => Text::_('MOD_POPUP_FORM_FIELD_NAME_LABEL'),
                    'type'           => 'text',
                    'required'       => 1,
                    'show_label'     => 1,
                    'label_position' => 'top',
                    'email_validate' => 0,
                ],
                [
                    'name'           => 'phone',
                    'label'          => Text::_('MOD_POPUP_FORM_FIELD_PHONE_LABEL'),
                    'type'           => 'tel',
                    'required'       => 1,
                    'show_label'     => 1,
                    'label_position' => 'top',
                    'email_validate' => 0,
                ],
            ];
        }

        $errors = [];
        $values = [];

        foreach ($formFields as $idx => $fieldCfg) {
            if ($fieldCfg instanceof Registry) {
                $fieldCfg = $fieldCfg->toArray();
            } elseif (is_object($fieldCfg)) {
                $fieldCfg = (array) $fieldCfg;
            }

            if (isset($fieldCfg['field'])) {
                $inner    = $fieldCfg['field'];
                $fieldCfg = $inner instanceof Registry ? $inner->toArray() : (array) $inner;
            }

            $rawName   = $fieldCfg['name'] ?? '';
            $fieldName = preg_replace('#[^a-zA-Z0-9_]#', '_', $rawName) ?: ('field_' . ($idx + 1));

            $label         = $fieldCfg['label'] ?? $fieldName;
            $type          = $fieldCfg['type'] ?? 'text';
            $required      = !empty($fieldCfg['required']);
            $emailValidate = !empty($fieldCfg['email_validate']);

            $value = trim((string) $input->get($fieldName, '', 'string'));

            $values[$fieldName] = [
                'label' => $label,
                'value' => $value,
            ];

            if ($required && $value === '') {
                $errors[$fieldName] = Text::sprintf('MOD_POPUP_FORM_ERROR_FIELD_REQUIRED_GENERIC', $label);
                continue;
            }

            if ($type === 'email' && $emailValidate && $value !== '') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$fieldName] = Text::sprintf('MOD_POPUP_FORM_ERROR_FIELD_EMAIL_INVALID', $label);
                }
            }
        }

        /**
         * CAPTCHA verification
         */
        $captchaPlugin = trim((string) $params->get('captcha_plugin', ''));

        if ($captchaPlugin !== '') {
            try {
                $captcha = Captcha::getInstance($captchaPlugin);

                if (!$captcha) {
                    $errors['captcha'] = Text::_('MOD_POPUP_FORM_ERROR_CAPTCHA_FAILED');
                } else {
                    $code = $input->get('mod_popup_form_captcha_' . $moduleId, '', 'string');

                    if (!$captcha->checkAnswer($code)) {
                        $errors['captcha'] = Text::_('MOD_POPUP_FORM_ERROR_CAPTCHA_INVALID');
                    }
                }
            } catch (\Throwable $e) {
                $errors['captcha'] = Text::_('MOD_POPUP_FORM_ERROR_CAPTCHA_FAILED');
            }
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors'  => $errors,
            ];
        }

        /** @var Mail $mailer */
        $mailer = Factory::getMailer();

        $subject = Text::_('MOD_POPUP_FORM_EMAIL_SUBJECT_DEFAULT');
        $body    = Text::_('MOD_POPUP_FORM_EMAIL_BODY_INTRO') . "\n\n";

        foreach ($values as $field) {
            $body .= ($field['label'] ?? '') . ': ' . ($field['value'] ?? '') . "\n";
        }

        $mailer->addRecipient($emailTo);
        $mailer->setSubject($subject);
        $mailer->setBody($body);

        try {
            $send = $mailer->send();
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                Text::sprintf('MOD_POPUP_FORM_ERROR_SENDING_FAILED_WITH_REASON', $e->getMessage()),
                500
            );
        }

        if ($send !== true) {
            throw new \RuntimeException(Text::_('MOD_POPUP_FORM_ERROR_SENDING_FAILED'), 500);
        }

        return [
            'success' => true,
        ];
    }
}
