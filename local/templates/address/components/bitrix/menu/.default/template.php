<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
    <div class="menu">
        <? foreach($arResult as $arItem):
            if($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1)
                continue;
            ?>
            <div <?=!empty($arItem['PARAMS']['ATTR_ID']) ? 'id='.$arItem['PARAMS']['ATTR_ID'] : ''?> class="menu-button" <?=!empty($arItem['PARAMS']['CLICK_JS']) ? 'onclick="'. $arItem['PARAMS']['CLICK_JS'] .'"' : ''?> href="<?=$arItem['LINK'];?>">
                <img src="<?=SITE_TEMPLATE_PATH . $arItem['PARAMS']['ICON']?>" alt="">
                <div><?=$arItem['TEXT']?></div>
            </div>
        <?endforeach?>
    </div>
<?endif?>