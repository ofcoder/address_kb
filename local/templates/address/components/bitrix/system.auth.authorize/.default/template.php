<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>

<div class="auth-block">
    <form class="auth-form" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
        <?if($arResult["BACKURL"] <> ''):?>
            <input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" aria-label="backurl"/>
        <?endif?>
        <input type="hidden" name="AUTH_FORM" value="Y" />
        <input type="hidden" name="TYPE" value="AUTH" />
        <?foreach ($arResult["POST"] as $key => $value):?>
            <input type="hidden" name="<?=$key?>" value="<?=$value?>" />
        <?endforeach?>
        <div class="input-body">
            <div class="input-container">
                <div class="input-name"><?=GetMessage('AUTH_EMAIL')?></div>
                <input required class="code-input auth_input" name="USER_LOGIN" value="<?=$_REQUEST['USER_LOGIN']?>" aria-label="USER_LOGIN"/>
            </div>
            <div class="input-container">
                <div class="password-input">
                    <div class="input-name"><?=GetMessage('AUTH_PASSWORD')?></div>
                    <? if (!empty($arParams['~AUTH_RESULT']['ERROR_TYPE']) && $_REQUEST['reset_pass'] != 'y'): ?>
                    <div class="input-name deny"><?=GetMessage('DENY')?></div>
                    <? endif; ?>
                </div>
                <input required type="password" class="code-input auth_input city" name="USER_PASSWORD" aria-label="USER_PASSWORD"/>
            </div>
            <?if($arResult["CAPTCHA_CODE"]):?>
                <div class="input-container">
                    <input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />

                    <div class="input-name"><?= GetMessage("AUTH_CAPTCHA_PROMT")?>:</div>
                    <img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" height="40" alt="CAPTCHA" />
                    <input required class="code-input auth_input" id="popup-input-id" name="captcha_word" autocomplete="off" aria-label="captcha_word"/>
                </div>
            <?endif;?>
        </div>
        <div class="login-buttons">
            <button name="submit" class="auth-btn">
                <img src="<?=$templateFolder?>/images/login-btn.svg" width="16px" height="13px" alt="loginImg">
                <?=GetMessage('AUTH_AUTHORIZE')?>
            </button>
            <? if ($_REQUEST['submit'] != 'reset'): ?>
                <button name="submit" class="send-pass" value="reset"><?=GetMessage('SEND_PASS')?></button>
            <? else:?>
                <div class="send-pass sent"><?=GetMessage('SENT_PASS')?></div>
            <? endif; ?>
        </div>
        <a class="support-btn" href="mailto:1505@krasnoe-beloe.ru"><?=GetMessage('SUPPORT')?></a>
    </form>
</div>