<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @global CMain $APPLICATION */
/** @global CUser $USER */

use \Bitrix\Main\Page\AssetLocation;
$asset = \Bitrix\Main\Page\Asset::getInstance();
?>
<!doctype html>
<html lang="<?= LANGUAGE_ID ?>">
<head>
    <link rel="icon" type="image/x-icon" href="<?= SITE_TEMPLATE_PATH ?>/favicon.ico"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no"/>
    <link rel="preconnect" href="//api-maps.yandex.ru">
    <link rel="dns-prefetch" href="//api-maps.yandex.ru">
    <?
    $asset->addString('<meta http-equiv="X-UA-Compatible" content="IE=edge" />', false, AssetLocation::BEFORE_CSS);
    if ($USER->IsAuthorized()) {
        $asset->addString('<script src="https://api-maps.yandex.ru/2.1/?&lang=ru_RU&apikey=' . YANDEX_KEY . '" type="text/javascript"></script>', false, AssetLocation::BEFORE_CSS);
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/head.js');
        $APPLICATION->ShowHead();
    } else {
        $APPLICATION->ShowMeta("keywords");
        $APPLICATION->ShowMeta("description");
    }
    ?>
    <title><? $APPLICATION->ShowTitle(); ?></title>
</head>
<body>
<? if ($USER->IsAdmin() && !defined("SKIP_SHOW_PANEL")): ?>
    <div id="panel"><? $APPLICATION->ShowPanel(); ?></div>
<? endif ?>
<div id="page-wr">
    <header class="head">
        <div class="head-left">
            <div class="head-logo">
                <a class="df px40" href="<?= $APPLICATION->GetCurPage() ?>">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/images/kb-logo.svg" alt="kb-logo" class="header-logo">
                </a>
                <div class="head-title">Адресная Программа</div>
            </div>
            <? if ($USER->IsAuthorized() && (CSite::InGroup([USER_GROUP_ID_RU, USER_GROUP_ID_MARKETER, USER_GROUP_ID_SUPERVISOR, USER_GROUP_ID_SHOPS_ADMIN, USER_GROUP_ID_ZRU]))): ?>
                <div class="head-burger" onclick="showMobileMenu()"></div>
            <? endif; ?>
        </div>
        <? if ($USER->IsAuthorized() && (CSite::InGroup([USER_GROUP_ID_RU, USER_GROUP_ID_MARKETER, USER_GROUP_ID_SUPERVISOR, USER_GROUP_ID_SHOPS_ADMIN, USER_GROUP_ID_ZRU]))): ?>
            <? $currentUser = \Bitrix\Main\UserTable::getList([
                'filter' => ['ID' => $USER->GetID()],
                'select' => ['WORK_POSITION'],
            ])->fetch(); ?>
        <div class="head-center">
            <?$APPLICATION->IncludeComponent(
                "bitrix:menu",
                ".default",
                array(
                    "ALLOW_MULTI_SELECT" => "N",
                    "CHILD_MENU_TYPE" => "left",
                    "COMPOSITE_FRAME_MODE" => "A",
                    "COMPOSITE_FRAME_TYPE" => "AUTO",
                    "DELAY" => "N",
                    "MAX_LEVEL" => "1",
                    "MENU_CACHE_GET_VARS" => array(
                    ),
                    "MENU_CACHE_TIME" => "3600",
                    "MENU_CACHE_TYPE" => "N",
                    "MENU_CACHE_USE_GROUPS" => "Y",
                    "ROOT_MENU_TYPE" => "top",
                    "USE_EXT" => "N",
                    "COMPONENT_TEMPLATE" => ".default"
                ),
                false
            );?>
            <div class="exit-popup dn">
                <a class="exit-btn" href="<?=SITE_DIR?>?logout=yes&<?=bitrix_sessid_get()?>">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/exit.svg" alt="kb-logo" />
                    <div>Выйти</div>
                </a>
            </div>
        </div>
        <div class="head-right">
            <div id="user-name" onclick="showMenu()" class="header-name"><?=empty($USER->GetFullName()) ? $USER->GetLogin() : $USER->GetFullName();?></div>
            <div class="header-position"><?=$currentUser['WORK_POSITION'] ??  '';?></div>
        </div>
       <? endif;?>
    </header>