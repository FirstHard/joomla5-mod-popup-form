<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/** @var \Joomla\CMS\Application\SiteApplication $app */
$app = Factory::getApplication();
$doc = $app->getDocument();
$wa  = $doc->getWebAssetManager();

// Регистрируем и подключаем JS/CSS модуля
$wa->registerAndUseScript(
    'mod_popup_form',
    'modules/mod_popup_form/media/js/popupform.js',
    [],
    ['defer' => true]
);

$wa->registerAndUseStyle(
    'mod_popup_form',
    'modules/mod_popup_form/media/css/popupform.css'
);

$anchorHash       = $params->get('anchor_hash', 'callback'); // без "#"
$introText        = $params->get('intro_text', '');
$submitLabel      = $params->get('submit_label', Text::_('MOD_POPUP_FORM_SUBMIT_DEFAULT'));
$submittingLabel  = $params->get('submitting_label', Text::_('MOD_POPUP_FORM_SUBMITTING_DEFAULT'));
$successText      = $params->get('success_text', Text::_('MOD_POPUP_FORM_SUCCESS_DEFAULT'));

$moduleId = (int) $module->id;

// Конфиг полей из параметров
// Конфиг полей из параметров
$formFields = $params->get('form_fields', []);

// Если вернулась JSON-строка — декодируем
if (is_string($formFields)) {
    $decoded = json_decode($formFields, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $formFields = $decoded;
    }
}

// Если это Registry — в массив
if ($formFields instanceof \Joomla\Registry\Registry) {
    $formFields = $formFields->toArray();
}

// Если это объект (stdClass) — тоже в массив
if (is_object($formFields)) {
    $formFields = (array) $formFields;
}

// Если в параметрах ничего не настроено — используем дефолтные поля
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
?>

<div
    id="mod-popup-form-<?php echo $moduleId; ?>"
    class="mod-popup-form<?php echo $moduleclass_sfx; ?>"
    data-anchor-hash="<?php echo htmlspecialchars($anchorHash, ENT_QUOTES, 'UTF-8'); ?>"
    data-ajax-url="<?php echo htmlspecialchars($ajaxUrl, ENT_QUOTES, 'UTF-8'); ?>"
    data-submit-label="<?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?>"
    data-submitting-label="<?php echo htmlspecialchars($submittingLabel, ENT_QUOTES, 'UTF-8'); ?>"
    data-success-text="<?php echo htmlspecialchars($successText, ENT_QUOTES, 'UTF-8'); ?>"
    data-module-id="<?php echo $moduleId; ?>">
    <!-- Затемнение -->
    <div class="mpf-overlay" data-mpf-close></div>

    <!-- Попап -->
    <div class="mpf-popup">
        <button type="button" class="mpf-close-btn" aria-label="<?php echo Text::_('JCLOSE'); ?>" data-mpf-close>
            &times;
        </button>

        <div class="mpf-content">
            <?php if (!empty($introText)) : ?>
                <div class="mpf-intro">
                    <?php echo $introText; ?>
                </div>
            <?php endif; ?>

            <div class="mpf-alert alert alert-danger d-none" role="alert"></div>

            <form class="mpf-form" novalidate>
                <?php foreach ($formFields as $idx => $fieldCfg) :

                    // Приводим к массиву верхний уровень
                    if ($fieldCfg instanceof \Joomla\Registry\Registry) {
                        $fieldCfg = $fieldCfg->toArray();
                    } elseif (is_object($fieldCfg)) {
                        $fieldCfg = (array) $fieldCfg;
                    } else {
                        $fieldCfg = (array) $fieldCfg;
                    }

                    // Subform обычно заворачивает данные внутрь ключа "field"
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

                    // Нормализуем имя поля: только латиница, цифры и _
                    $fieldName = preg_replace('#[^a-zA-Z0-9_]#', '_', $rawName);
                    if ($fieldName === '') {
                        $fieldName = 'field_' . ($idx + 1);
                    }

                    $label          = $fieldCfg['label'] ?? $fieldName;
                    $type           = $fieldCfg['type'] ?? 'text';
                    $placeholder    = $fieldCfg['placeholder'] ?? '';
                    $required       = (int)($fieldCfg['required'] ?? 0) === 1;
                    $showLabel      = (int)($fieldCfg['show_label'] ?? 1) === 1;
                    $labelPosition  = $fieldCfg['label_position'] ?? 'top';
                    $emailValidate  = (int)($fieldCfg['email_validate'] ?? 0) === 1;

                    $fieldId = 'mpf-' . $fieldName . '-' . $moduleId;

                    $wrapperClasses = [
                        'mb-3',
                        'mpf-field',
                        'mpf-field--' . $type,
                        'mpf-label-' . $labelPosition,
                    ];
                ?>

                    <div class="<?php echo implode(' ', $wrapperClasses); ?>">
                        <?php if ($showLabel) : ?>
                            <label for="<?php echo $fieldId; ?>" class="form-label">
                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            </label>
                        <?php endif; ?>

                        <?php if ($type === 'textarea') : ?>
                            <textarea
                                class="form-control rounded-0"
                                id="<?php echo $fieldId; ?>"
                                name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $required ? 'required' : ''; ?>
                                data-type="textarea"
                                <?php echo $emailValidate ? 'data-email-validate="1"' : ''; ?>
                                placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>"></textarea>
                        <?php else : ?>
                            <input
                                type="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                                class="form-control rounded-0"
                                id="<?php echo $fieldId; ?>"
                                name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $required ? 'required' : ''; ?>
                                data-type="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $emailValidate ? 'data-email-validate="1"' : ''; ?>
                                placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>

                        <div class="invalid-feedback">
                            <?php echo Text::sprintf('MOD_POPUP_FORM_ERROR_FIELD_REQUIRED_GENERIC', $label); ?>
                        </div>
                    </div>

                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary mpf-submit-btn btn-modern text-center">
                    <?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?>
                </button>
            </form>

            <div class="mpf-success d-none">
                <?php echo nl2br(htmlspecialchars($successText, ENT_QUOTES, 'UTF-8')); ?>
            </div>
        </div>
    </div>
</div>