<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
CJSCore::Init(array("popup"));

\Bitrix\Main\UI\Extension::load('kb.address');
?>

<? if ($arResult['MODE'] == 'deny') {
    Bitrix\Iblock\Component\Tools::process404(
        'Не найден', //Сообщение
        true, // Нужно ли определять 404-ю константу
        true, // Устанавливать ли статус
        true, // Показывать ли 404-ю страницу
        SITE_DIR . '/404.php' // Ссылка на отличную от стандартной 404-ю
    );
    die();
} ?>

<div id="map"></div>
<div class="address-hover">
    <div class="hover" onclick="changeToggleIcon()">
        <div class="hover-text">Добавление адреса</div>
        <div class="hover-toggle hover-toggle-up"></div>
    </div>
    <div class="address-container" id="application"></div>
</div>

<script>
    const application = new BX.KB.AddressProgram('#application', '#historyApp');
    application.start('<?=$arResult['MODE']?>', '<?=SITE_TEMPLATE_PATH?>/images');
    const storeShop = application.getShopStore();
    const historyShop = application.getHistoryStore();

    storeShop.shops = <?=json_encode($arResult['SHOPS'])?>;
    storeShop.filters = <?=json_encode($arResult['FILTERS'])?>;
    historyShop.histories = <?=json_encode($arResult['HISTORIES'])?>;
</script>
<?php
//log2file($arResult,'$arResult', '/home/bitrix/www/local/logs/');
?>