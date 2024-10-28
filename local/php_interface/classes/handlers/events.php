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
    public static function onAdminListDisplayHandler(&$list)
    {
        if ($list->table_id == "t_certification_admin" || $list->table_id == 't_gradebook_admin') {
            $extraFields = [
                'USER_WORK_POSITION' => 'Должность',
                'USER_UF_EMPLOYMENT_DATE' => 'Дата принятия на работу',
                'USER_XML_ID' => 'Штрихкод пропуска',
                'USER_WORK_CITY' => 'Город',
                'COURSE_ID' => 'ID курса обучения',
            ];

            if ($list->table_id == 't_gradebook_admin') {
                $extraFields['DATE_BEST_TEST'] = 'Дата сдачи';
            }

            $headerArray = $fieldsArray = [];
            foreach ($extraFields as $key => $extraField) {
                $headerArray[$key] = [
                    "id"=> $key,
                    "content"=> $extraField,
                    "sort"=> mb_strtolower($key),
                    "default"=> true,
                    "__sort"=> 0,
                ];
            }

            $list->arVisibleColumns = array_merge($list->aHeaders, array_keys($extraFields));
            $list->aHeaders = array_merge($list->aHeaders, $headerArray);
            $list->aVisibleHeaders = array_merge($list->aVisibleHeaders, $headerArray);

            $userIds = $testIds = [];
            foreach ($list->aRows as $row) { // здесь мы вклиниваемся в контекстное меню каждой строки таблицы
                $row->aHeaders = array_merge($row->aHeaders, $headerArray);
                $row->aHeadersID = array_merge($row->aHeadersID, array_keys($extraFields));
                $row->aFields = array_merge($row->aFields, $fieldsArray);
                $userIds[] = $row->arRes['USER_ID'];
                $testIds[] = $row->arRes['TEST_ID'];
            }

            if ($list->table_id == 't_gradebook_admin' && CModule::IncludeModule("learning")) {
                $res = CTestAttempt::GetList(
                    ["SCORE" => "DESC"],
                    ["TEST_ID" => $testIds, "STUDENT_ID" => $userIds]
                );

                $bestAttempt = [];
                while ($arAttempt = $res->fetch()) {
                    if (empty($bestAttempt[$arAttempt['STUDENT_ID'] . '_' . $arAttempt['TEST_ID']])) {
                        $bestAttempt[$arAttempt['STUDENT_ID'] . '_' . $arAttempt['TEST_ID']]['DATE_BEST_TEST'] = $arAttempt['DATE_END'];
                    }
                }
            }

            $rsUsers = \Bitrix\Main\UserTable::getList([
                'filter' => ['ID' => $userIds],
                'select' => ['ID', 'XML_ID', 'WORK_POSITION', 'UF_EMPLOYMENT_DATE', 'WORK_CITY'],
            ]);

            $userFields = [];
            while ($arUser = $rsUsers->fetch()) {
                $userFields[$arUser['ID']] = [
                    'USER_WORK_POSITION' => $arUser['WORK_POSITION'],
                    'USER_UF_EMPLOYMENT_DATE' => $arUser['UF_EMPLOYMENT_DATE'],
                    'USER_XML_ID' => $arUser['XML_ID'],
                    'USER_WORK_CITY' => $arUser['WORK_CITY'],
                ];
            }

            foreach ($list->aRows as $key => $row) {
                $row->arRes = array_merge($row->arRes, $userFields[$row->arRes['USER_ID']]);

                if ($list->table_id == 't_gradebook_admin') {
                    if (!empty($bestAttempt[$row->arRes['STUDENT_ID'] . '_' . $row->arRes['TEST_ID']])) {
                        $row->arRes = array_merge($row->arRes, $bestAttempt[$row->arRes['STUDENT_ID'] . '_' . $row->arRes['TEST_ID']]);
                    }

                    if (!empty($_REQUEST['filter_date_from']) && strtotime($row->arRes['DATE_BEST_TEST']) < strtotime($_REQUEST['filter_date_from'])) {
                        unset($list->aRows[$key]);
                    }
                    if (!empty($_REQUEST['filter_date_to']) && strtotime($row->arRes['DATE_BEST_TEST']) > strtotime($_REQUEST['filter_date_to'] . ' 23:59:59')) {
                        unset($list->aRows[$key]);
                    }
                }

                if (!empty($_REQUEST['filter_city']) && $userFields[$row->arRes['USER_ID']]['USER_WORK_CITY'] != $_REQUEST['filter_city']) {
                    unset($list->aRows[$key]);
                }
            }
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