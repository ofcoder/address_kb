<?php
function GetGlobalID()
{
    global $GLOBAL_IBLOCK_ID;
    global $GLOBAL_FORUM_ID;
    global $GLOBAL_BLOG_GROUP;
    global $GLOBAL_STORAGE_ID;
    $ttl = 2592000;
    $cache_id = 'id_to_code_';
    $cache_dir = '/bx/code';
    $obCache = new CPHPCache;

    if ($obCache->InitCache($ttl, $cache_id, $cache_dir))
    {
        $tmpVal = $obCache->GetVars();
        $GLOBAL_IBLOCK_ID = $tmpVal['IBLOCK_ID'];
        $GLOBAL_FORUM_ID = $tmpVal['FORUM_ID'];
        $GLOBAL_BLOG_GROUP = $tmpVal['BLOG_GROUP'];
        $GLOBAL_STORAGE_ID = $tmpVal['STORAGE_ID'];

        unset($tmpVal);
    }
    else
    {
        if (CModule::IncludeModule("iblock"))
        {
            $res = CIBlock::GetList(
                Array(),
                Array("CHECK_PERMISSIONS" => "N")
            );

            while ($ar_res = $res->Fetch())
            {
                $GLOBAL_IBLOCK_ID[$ar_res["CODE"]] = $ar_res["ID"];
            }
        }

        if (CModule::IncludeModule("forum"))
        {
            $res = CForumNew::GetList(
                Array()
            );

            while ($ar_res = $res->Fetch())
            {
                $GLOBAL_FORUM_ID[$ar_res["XML_ID"]] = $ar_res["ID"];
            }
        }

        if (CModule::IncludeModule("blog"))
        {
            $arFields = Array("ID", "SITE_ID");

            $dbGroup = CBlogGroup::GetList(array(), array(), false, false, $arFields);
            while ($arGroup = $dbGroup->Fetch())
            {
                $GLOBAL_BLOG_GROUP[$arGroup["SITE_ID"]] = $arGroup["ID"];
            }
        }

        if (CModule::IncludeModule('disk'))
        {
            $dbDisk = Bitrix\Disk\Storage::getList([
                'filter' => [
                    '=ENTITY_TYPE' => Bitrix\Disk\ProxyType\Common::className(),
                    '=MODULE_ID' => 'disk',
                ]
            ]);
            if ($commonStorage = $dbDisk->Fetch())
            {
                $GLOBAL_STORAGE_ID['shared_files'] = $commonStorage['ID'];
            }
        }

        if ($obCache->StartDataCache())
        {
            $obCache->EndDataCache(array(
                'IBLOCK_ID' => $GLOBAL_IBLOCK_ID,
                'FORUM_ID' => $GLOBAL_FORUM_ID,
                'BLOG_GROUP' => $GLOBAL_BLOG_GROUP,
                'STORAGE_ID' => $GLOBAL_STORAGE_ID,
            ));
        }
    }
}

\Bitrix\Main\Loader::registerAutoLoadClasses(
    null, // не указываем имя модуля
    [
        // ключ - имя класса, значение - путь относительно корня сайта к файлу с классом
        'RestApi' => '/local/php_interface/classes/handlers/restApi.php',
        'EventHandlers' => '/local/php_interface/classes/handlers/events.php',
        'EventHelpers' => '/local/php_interface/classes/helpers/eventHelpers.php',
        'AgentsHelpers' => '/local/php_interface/classes/helpers/agentsHelpers.php',
        'RestApiHelpers' => '/local/php_interface/classes/helpers/restApiHelpers.php',
        '\\kb\\Model\\WorkCitiesTable' => '/local/php_interface/kb/Model/workcitiestable.php',
        '\\kb\\Model\\ShopsTable' => '/local/php_interface/kb/Model/shopstable.php',
        '\\kb\\Model\\ShopsApTable' => '/local/php_interface/kb/Model/shopsaptable.php',
        '\\kb\\Model\\ShopsApHistoryTable' => '/local/php_interface/kb/Model/shopsaphistorytable.php',
        '\\kb\\Model\\DepartmentUpdateTable' => '/local/php_interface/kb/Model/departmentupdatetable.php',
        '\\kb\\Model\\TelegramChatTable' => '/local/php_interface/kb/Model/telegramchattable.php',
    ]
);

CJSCore::RegisterExt('custom_stuff', 
  array(
    'js' => '/local/js/custom_stuff.js',
    'lang' => '/local/lang/' . LANGUAGE_ID . '/custom_stuff.js.php',
    'css' => '/local/css/custom_stuff.css',
    'rel' => array(
      'ajax',
      'popup'
    )
  )
);



$arJsConfig = [
    'events_handler' => [
        'js' => '/local/js/events.js',
        'css' => '',
        'rel' => [],
    ],
	'custom_portal' => [
        'js' => '/local/js/custom_portal.js',
        'lang' => '/local/lang/' . LANGUAGE_ID . '/custom_portal.js.php',
		'css' => '/local/css/custom_portal.css',
		'rel' => array(
		  'ajax',
		  'popup'
		)
    ],	
];

global $USER;
if (isset($USER) && is_object($USER)) {
    if (!CSite::inGroup([1])) {
        $arJsConfig['yandex_metrics'] = [
            'js' => '/local/js/metrics.js',
            'css' => '',
            'rel' => [],
        ];
    }
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if($request->isAdminSection() && $APPLICATION->GetCurPage(false) == '/bitrix/admin/php_command_line.php') {
    $arJsConfig['admin_section'] = [
        'css' => '/local/css/admin.css',
        'rel' => [],
    ];
}

if (str_contains($APPLICATION->GetCurPage(), 'crm/type/31/kanban')) {
    $arJsConfig['invoice_section'] = [
        'css' => '/local/css/invoice.css',
        'rel' => [],
    ];
}

foreach ($arJsConfig as $ext => $arExt) {
    \CJSCore::RegisterExt($ext, $arExt);
}

\CJSCore::Init('custom_portal');

if ($request->isAdminSection() && $APPLICATION->GetCurPage(false) == '/bitrix/admin/php_command_line.php') {
    \CJSCore::Init('admin_section');
}
if (str_contains($APPLICATION->GetCurPage(), 'crm/type/31/kanban')) {
    \CJSCore::Init('invoice_section');
}

AddEventHandler("forum", "onBeforeMessageUpdate", ["EventHandlers", "onBeforeMessageUpdateHandler"]);
AddEventHandler("tasks", "onBeforeTaskUpdate", ["EventHandlers", "onBeforeTaskUpdateHandler"]);
AddEventHandler("main", "onBuildGlobalMenu", ["EventHandlers", "onBuildGlobalMenuHandler"]);
AddEventHandler("main", "onAdminListDisplay", ["EventHandlers", "onAdminListDisplayHandler"]);
AddEventHandler("main", "onBeforeUserUpdate", ["EventHandlers", "onBeforeUserUpdateHandler"]);
AddEventHandler("iblock", "onAfterIBlockSectionUpdate", ["EventHandlers", "onAfterIBlockSectionUpdateHandler"]);
AddEventHandler("iblock", "onAfterIBlockSectionAdd", ["EventHandlers", "onAfterIBlockSectionAddHandler"]);
AddEventHandler('rest', 'onRestServiceBuildDescription', ['RestApi', 'OnRestServiceBuildDescriptionHandler']);
AddEventHandler('main', 'OnEpilog',  ['EventHandlers','onEpilogHandler']);
AddEventHandler('main', 'OnEndBufferContent',  ['EventHandlers','deleteKernelScripts']);
AddEventHandler('imopenlines', 'OnChatFinish',  ['EventHandlers','onChatFinish']);
AddEventHandler('tasks', 'OnTaskAdd',  ['EventHandlers','OnTaskAddCheckToUnmute']);

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/functions/agents.php")) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/functions/agents.php");
}
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/functions/functions.php")) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/functions/functions.php");
}
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/api/crest.php")) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/api/crest.php");
}
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/constants.php")) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/constants.php");
}