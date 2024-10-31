<? use \kb\Model\TelegramChatTable;

class EventHandlers
{
    public static function onBeforeMessageUpdateHandler()
    {
        global $USER;
        $userFields = CUser::GetByID($USER->GetId())->fetch();

        if ($userFields['UF_DEPARTMENT']) {
            foreach (UNEDITABLE_DEPARTMENT as $noneditable) {
                if (in_array($noneditable, $userFields['UF_DEPARTMENT'])) {
                    global $APPLICATION;
                    $APPLICATION->ThrowException('Комментарии нельзя редактировать');
                    return false;
                }
            }
        }
    }
    public static function onBeforeTaskUpdateHandler($taskId, $editFields)
    {
        $task = new \Bitrix\Tasks\Item\Task($taskId);
        if ($task['CREATED_BY'] != $editFields['CHANGED_BY']) {
            global $USER;
            $userFields = CUser::GetByID($USER->GetId())->fetch();

            if ($userFields['UF_DEPARTMENT']) {
                foreach (UNEDITABLE_DEPARTMENT as $noneditable) {
                    if (in_array($noneditable, $userFields['UF_DEPARTMENT'])) {
                        global $APPLICATION;
                        $APPLICATION->ThrowException('Задачу нельзя редактировать');
                        return false;
                    }
                }
            }
        }
    }
    public static function onBuildGlobalMenuHandler(&$aGlobalMenu, &$aModuleMenu)
    {
        if (CSite::InGroup([34])) {
            foreach($aModuleMenu as $key => $aMenu) {
                if ($aMenu["section"] == 'support' || $aMenu["section"] == 'bizproc') {
                    unset($aModuleMenu[$key]);
                }
            }
            unset($aGlobalMenu["global_menu_desktop"]);
        }
    }
    public static function onAfterIBlockSectionUpdateHandler($fields) {
        if ($fields['IBLOCK_ID'] == '1') {
            EventHelpers::changeDepartment($fields['ID'], $fields['IBLOCK_SECTION_ID'], trim($fields['SEARCHABLE_CONTENT']));
        }
    }
    public static function onAfterIBlockSectionAddHandler($fields) {
        if ($fields['IBLOCK_ID'] == '1') {
            EventHelpers::changeDepartment($fields['ID'], $fields['IBLOCK_SECTION_ID'], trim($fields['SEARCHABLE_CONTENT']));
        }
    }
    public static function onBeforeUserUpdateHandler($fields) {
        if (CUser::GetByID($fields['ID'])->fetch()['UF_DEPARTMENT'] !== $fields['UF_DEPARTMENT'] && $fields['UF_DEPARTMENT']) {
            $section = CIBlockSection::GetList([], ['ID' => $fields['UF_DEPARTMENT']])->Fetch();
            EventHelpers::changeDepartment(reset($fields['UF_DEPARTMENT']), $section['IBLOCK_SECTION_ID'], $section['NAME'], $fields['ID']);
        }
    }

    public static function onChatFinish($fields)
    {
        $orm = TelegramChatTable::getList([
            'order' => ['ID' => 'DESC'],
            'limit' => 1,
            'filter' => ['UF_USER_ID' => $fields->getUser('USER_ID'), 'UF_IS_OPENED' => 1],
            'select' => ['ID', 'UF_TELEGRAM_CHAT_ID', 'UF_IS_TEST']
        ])->fetch();

        if ($orm) {
            $telegramKey = $orm['UF_IS_TEST'] ? TELEGRAM_KEY_TEST : TELEGRAM_KEY_PROD;
            getResponce(TELEGRAM_API . $telegramKey . '/sendMessage?chat_id='.$orm['UF_TELEGRAM_CHAT_ID'].'&text='. TELEGRAM_CHAT_CLOSE ,'');
            TelegramChatTable::update($orm['ID'], [
                'UF_IS_OPENED' => 0
            ]);
        }
    }

    public static function onEpilogHandler()
    {
        if (SITE_ID == 's1' || SITE_ID == 'kb') {
            CUtil::InitJSCore(['events_handler']);
            CUtil::InitJSCore(['yandex_metrics']);
        }
    }
    static function deleteKernelScripts(&$content)
    {
        global $USER;

        if (defined("ADMIN_SECTION")) {
            return;
        }

        if (SITE_ID == 'ap') {
            if (is_object($USER) && $USER->IsAuthorized()) {
                $ar_patterns_to_remove = [
                    '/<script[^>]+?>var _ba = _ba[^<]+<\/script>/',
                    '/<script.+?src="\/bitrix\/js\/pull\/protobuf+.+?(\.min|)\.js\?\d+"><\/script\>/',
                    '/<script.+?src="\/bitrix\/js\/pull\/client\/pull.client+.+?(\.min|)\.js\?\d+"><\/script\>/',
                    '/<script.+?src="\/bitrix\/js\/rest\/client\/rest.client+.+?(\.min|)\.js\?\d+"><\/script\>/',
                    '/<link.+?href=".+?bitrix\/js\/intranet\/intranet-common(\.min|)\.css\?\d+"[^>]+>/',
                    '/<link.+?href=".+?kernel_main\/kernel_main_v1(\.min|)\.css\?\d+"[^>]+>/',
                    '/<link.+?href=".+?bitrix\/themes\/.default\/pubstyles(\.min|)\.css\?\d+"[^>]+>/',
                    '/<link.+?href=".+?bitrix\/js\/fileman\/sticker(\.min|)\.css\?\d+"[^>]+>/',
                    '/<link.+?href=".+?bitrix\/js\/ui\/fonts\/opensans\/ui\.font\.opensans(\.min|)\.css\?\d+"[^>]+>/',
                ];
            } else {
                $ar_patterns_to_remove = [
                    '/<script.+?src=".+?js\/main\/core\/.+?(\.min|)\.js\?\d+"><\/script\>/',
                    '/<script.+?src="\/bitrix\/js\/.+?(\.min|)\.js\?\d+"><\/script\>/',
                    '/<link.+?href="\/bitrix\/js\/.+?(\.min|)\.css\?\d+".+?>/',
                    '/<link.+?href="\/bitrix\/components\/.+?(\.min|)\.css\?\d+".+?>/',
                    '/<script.+?src="\/bitrix\/.+?kernel_main.+?(\.min|)\.js\?\d+"><\/script\>/',
                    '/<link.+?href=".+?kernel_main\/kernel_main(\.min|)\.css\?\d+"[^>]+>/',
                    '/<link.+?href=".+?main\/popup(\.min|)\.css\?\d+"[^>]+>/',
                    '/<script.+?>if\(\!window\.BX\)window\.BX.+?<\/script>/',
                    '/<script[^>]+?>\(window\.BX\|\|top\.BX\)\.message[^<]+<\/script>/',
                    '/<script[^>]+?>var _ba = _ba[^<]+<\/script>/',
                    '/<script[^>]+?>.+?bx-core.*?<\/script>/',
                    '/<script[^>]*?>[\s]*BX\.(setCSSList|setJSList)[^<]+<\/script>/',
                    '#<script[^>]*?>[^<]+BX\.ready[^<]+<\/script>#',
                ];
            }
        }

        if (!empty($ar_patterns_to_remove)) {
            $content = preg_replace($ar_patterns_to_remove, "", $content);
            $content = preg_replace("/\n{2,}/", "\n", $content);
        }
    }

    public static function OnTaskAddCheckToUnmute($idTask, $arTask)
    {
        $excludeGroups = [35];
        $auditors = $arTask['AUDITORS'];
        if (is_array($auditors))
        {
            \CModule::IncludeModule('tasks');
            foreach ($auditors as $auditorId)
            {
                $auditorGroups = \CUser::GetUserGroup($auditorId);
                foreach ($excludeGroups as $excludeGroup) {
                    if (in_array($excludeGroup, $auditorGroups)) {
                        \Bitrix\Tasks\Internals\UserOption::delete($idTask, $auditorId, \Bitrix\Tasks\Internals\UserOption\Option::MUTED);
                        break;
                    }
                }
            }
        }
    }
}