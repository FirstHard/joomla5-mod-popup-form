<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Mail;
use Joomla\Registry\Registry;

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
        
        $moduleId = (int) $input->get('module_id', 0);

        if (!$moduleId) {
            throw new \RuntimeException(Text::_('MOD_POPUP_FORM_ERROR_NO_MODULE_ID'), 400);
        }
        
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'params']))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('id') . ' = ' . (int) $moduleId)
            ->where($db->quoteName('module') . ' = ' . $db->quote('mod_popup_form'));

        $db->setQuery($query);
        $module = $db->loadObject();

        if (!$module) {
            throw new \RuntimeException(Text::_('MOD_POPUP_FORM_ERROR_MODULE_NOT_FOUND'), 404);
        }
        
        $params = new Registry($module->params);

        $emailTo = trim((string) $params->get('email_to', ''));

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
        
        if ($formFields instanceof \Joomla\Registry\Registry) {
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
                    'placeholder'    => '',
                    'required'       => 1,
                    'show_label'     => 1,
                    'label_position' => 'top',
                    'email_validate' => 0,
                ],
                [
                    'name'           => 'phone',
                    'label'          => Text::_('MOD_POPUP_FORM_FIELD_PHONE_LABEL'),
                    'type'           => 'tel',
                    'placeholder'    => '',
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
            
            if ($fieldCfg instanceof \Joomla\Registry\Registry) {
                $fieldCfg = $fieldCfg->toArray();
            } elseif (is_object($fieldCfg)) {
                $fieldCfg = (array) $fieldCfg;
            } else {
                $fieldCfg = (array) $fieldCfg;
            }
            
            if (isset($fieldCfg['field'])) {
                $inner = $fieldCfg['field'];

                if ($inner instanceof \Joomla\Registry\Registry) {
                    $fieldCfg = $inner->toArray();
                } elseif (is_object($inner)) {
                    $fieldCfg = (array) $inner;
                } elseif (is_array($inner)) {
                    $fieldCfg = $inner;
                }
            }

            $rawName = $fieldCfg['name'] ?? '';

            $fieldName = preg_replace('#[^a-zA-Z0-9_]#', '_', $rawName);
            if ($fieldName === '') {
                $fieldName = 'field_' . ($idx + 1);
            }

            $label         = $fieldCfg['label'] ?? $fieldName;
            $type          = $fieldCfg['type'] ?? 'text';
            $required      = (int)($fieldCfg['required'] ?? 0) === 1;
            $emailValidate = (int)($fieldCfg['email_validate'] ?? 0) === 1;

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
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors'  => $errors,
            ];
        }
        
        /** @var Mail $mailer */
        $mailer = Factory::getMailer();

        $subject = Text::_('MOD_POPUP_FORM_EMAIL_SUBJECT_DEFAULT');

        $body  = Text::_('MOD_POPUP_FORM_EMAIL_BODY_INTRO') . "\n\n";
        foreach ($values as $fieldName => $item) {
            $body .= ($item['label'] ?? $fieldName) . ': ' . ($item['value'] ?? '') . "\n";
        }

        $mailer->addRecipient($emailTo);
        $mailer->setSubject($subject);
        $mailer->setBody($body);

        try {
            $send = $mailer->send();
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                Text::sprintf('MOD_POPUP_FORM_ERROR_SENDING_FAILED_WITH_REASON', $e->getMessage()),
                500,
                $e
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
