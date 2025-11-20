<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\CMS\Application\SiteApplication $app */
$app = Factory::getApplication();
$input = $app->getInput();

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');

// Хеш (без решётки)
$anchorHash = $params->get('anchor_hash', 'callback');

$ajaxModule = preg_replace('#^mod_#', '', $module->module);

// URL для AJAX-отправки через com_ajax
$ajaxUrl = Uri::root() . 'index.php?option=com_ajax'
    . '&module=' . $ajaxModule
    . '&method=submit'
    . '&format=json';

require ModuleHelper::getLayoutPath('mod_popup_form', $params->get('layout', 'default'));
