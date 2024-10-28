<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Адресная Программа"); ?>

<? $APPLICATION->IncludeComponent('kb:address.program', '.default', [], false); ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>