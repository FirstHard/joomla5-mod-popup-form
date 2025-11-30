<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Captcha\Captcha;

/** @var \Joomla\CMS\Application\SiteApplication $app */
$app = Factory::getApplication();
$doc = $app->getDocument();
$wa  = $doc->getWebAssetManager();

// Register assets
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

Text::script('MOD_POPUP_FORM_ERROR_VALIDATION');
Text::script('MOD_POPUP_FORM_ERROR_CAPTCHA_INVALID');

$displayMode      = $params->get('display_mode', 'popup');
$anchorHash       = $params->get('anchor_hash', 'callback');
$introText        = (string) $params->get('intro_text', '');
$introTextPosition = (string) $params->get('intro_text_position', 'top');
$introTextAllowHtml = (int) $params->get('intro_text_allow_html', 1);

if ($introTextPosition !== 'top' && $introTextPosition !== 'left') {
    $introTextPosition = 'top';
}

if ($introTextAllowHtml) {
    $introTextHtml = $introText;
} else {
    $introTextHtml = nl2br(htmlspecialchars($introText, ENT_QUOTES, 'UTF-8'));
}

$submitLabel      = $params->get('submit_label', Text::_('MOD_POPUP_FORM_SUBMIT_DEFAULT'));
$submittingLabel  = $params->get('submitting_label', Text::_('MOD_POPUP_FORM_SUBMITTING_DEFAULT'));
$successText      = $params->get('success_text', Text::_('MOD_POPUP_FORM_SUCCESS_DEFAULT'));

$captchaPlugin    = (string) $params->get('captcha_plugin', '');

$moduleId = (int) $module->id; // must be defined before using in captcha and IDs

$captchaHtml = '';

if ($captchaPlugin !== '') {
    try {
        $captcha = Captcha::getInstance($captchaPlugin);

        if ($captcha) {
            $captchaHtml = $captcha->display(
                'mod_popup_form_captcha_' . $moduleId,
                'mod_popup_form_captcha_' . $moduleId
            );
        }
    } catch (\Throwable $e) {
        $captchaHtml = '';
    }
}

// Form fields config
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
    class="mod-popup-form<?php echo $moduleclass_sfx; ?> mod-popup-form--<?php echo $displayMode; ?>"
    data-display-mode="<?php echo htmlspecialchars($displayMode, ENT_QUOTES, 'UTF-8'); ?>"
    data-anchor-hash="<?php echo htmlspecialchars($anchorHash, ENT_QUOTES, 'UTF-8'); ?>"
    data-ajax-url="<?php echo htmlspecialchars($ajaxUrl, ENT_QUOTES, 'UTF-8'); ?>"
    data-submit-label="<?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?>"
    data-submitting-label="<?php echo htmlspecialchars($submittingLabel, ENT_QUOTES, 'UTF-8'); ?>"
    data-success-text="<?php echo htmlspecialchars($successText, ENT_QUOTES, 'UTF-8'); ?>"
    data-module-id="<?php echo $moduleId; ?>">

    <?php if ($displayMode === 'popup') : ?>
        <div class="mpf-overlay" data-mpf-close></div>

        <div class="mpf-popup">
            <button type="button" class="mpf-close-btn" aria-label="<?php echo Text::_('JCLOSE'); ?>" data-mpf-close>
                &times;
            </button>

            <div class="mpf-content mpf-intro-position-<?php echo htmlspecialchars($introTextPosition, ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($introText !== '') : ?>
                    <div class="mpf-intro">
                        <?php echo $introTextHtml; ?>
                    </div>
                <?php endif; ?>

                <div class="mpf-alert alert alert-danger d-none" role="alert"></div>

                <form class="mpf-form" novalidate>
                    <?php foreach ($formFields as $idx => $fieldCfg) :

                        if ($fieldCfg instanceof Registry) {
                            $fieldCfg = $fieldCfg->toArray();
                        } elseif (is_object($fieldCfg)) {
                            $fieldCfg = (array) $fieldCfg;
                        } else {
                            $fieldCfg = (array) $fieldCfg;
                        }

                        if (isset($fieldCfg['field'])) {
                            $inner = $fieldCfg['field'];

                            if ($inner instanceof Registry) {
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

                    <?php if (!empty($captchaHtml)) : ?>
                        <div class="mb-3 mpf-field mpf-field--captcha">
                            <?php echo $captchaHtml; ?>
                            <div class="invalid-feedback">
                                <?php echo Text::_('MOD_POPUP_FORM_ERROR_CAPTCHA_INVALID'); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="hidden" name="contact_id" id="mpf-contact-id-<?php echo (int)$moduleId; ?>" value="">

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary mpf-submit-btn">
                            <?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    </div>
                </form>

                <div class="mpf-success d-none">
                    <?php echo nl2br(htmlspecialchars($successText, ENT_QUOTES, 'UTF-8')); ?>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="mpf-content">
            <?php if (!empty($introText)) : ?>
                <div class="mpf-intro">
                    <?php echo $introText; ?>
                </div>
            <?php endif; ?>

            <div class="mpf-alert alert alert-danger d-none" role="alert"></div>

            <form class="mpf-form" novalidate>
                <?php foreach ($formFields as $idx => $fieldCfg) :

                    if ($fieldCfg instanceof Registry) {
                        $fieldCfg = $fieldCfg->toArray();
                    } elseif (is_object($fieldCfg)) {
                        $fieldCfg = (array) $fieldCfg;
                    } else {
                        $fieldCfg = (array) $fieldCfg;
                    }

                    if (isset($fieldCfg['field'])) {
                        $inner = $fieldCfg['field'];

                        if ($inner instanceof Registry) {
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

                <?php if (!empty($captchaHtml)) : ?>
                    <div class="mb-3 mpf-field mpf-field--captcha">
                        <?php echo $captchaHtml; ?>
                        <div class="invalid-feedback">
                            <?php echo Text::_('MOD_POPUP_FORM_ERROR_CAPTCHA_INVALID'); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="contact_id" id="mpf-contact-id-<?php echo (int)$moduleId; ?>" value="">

                <div class="text-center">
                    <button type="submit" class="btn btn-primary mpf-submit-btn">
                        <?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                </div>
            </form>

            <div class="mpf-success d-none">
                <?php echo nl2br(htmlspecialchars($successText, ENT_QUOTES, 'UTF-8')); ?>
            </div>
        </div>
    <?php endif; ?>
</div>