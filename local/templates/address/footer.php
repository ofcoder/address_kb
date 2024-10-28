<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

    if (!$USER->IsAuthorized()) {
        $APPLICATION->ShowCSS();
        $APPLICATION->ShowHeadStrings();
        $APPLICATION->ShowHeadScripts();
    }
?>
        <div id="instructions">
            <?$APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                Array(
                    "AREA_FILE_SHOW" => "file",
                    "AREA_FILE_SUFFIX" => "inc",
                    "COMPOSITE_FRAME_MODE" => "A",
                    "COMPOSITE_FRAME_TYPE" => "AUTO",
                    "EDIT_TEMPLATE" => "",
                    "PATH" => SITE_DIR."include/instructions.php"
                )
            );?>
        </div>
        <div id="history">
            <div class="history">
                <div class="history-title">История изменения статусов АП</div>
                <div id="historyApp"></div>
            </div>
        </div>
    </div>
</body>
</html>
