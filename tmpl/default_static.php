<?php
defined('_JEXEC') or die;
?>
<div
    id="mod-popup-form-<?php echo (int) $moduleId; ?>"
    class="mod-popup-form mod-popup-form--static"
    data-display-mode="static"
    data-anchor-hash="<?php echo htmlspecialchars($anchorHash, ENT_QUOTES, 'UTF-8'); ?>"
    data-ajax-url="<?php echo htmlspecialchars($ajaxUrl, ENT_QUOTES, 'UTF-8'); ?>"
    data-submit-label="<?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?>"
    data-submitting-label="<?php echo htmlspecialchars($submittingLabel, ENT_QUOTES, 'UTF-8'); ?>"
    data-success-text="<?php echo htmlspecialchars($successText, ENT_QUOTES, 'UTF-8'); ?>"
    data-module-id="<?php echo (int) $moduleId; ?>">

    <div class="mpf-content">
        <?php $renderFormLayout(); ?>
    </div>
</div>
