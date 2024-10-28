<?php
\Bitrix\Main\Loader::includeModule('rest');
\Bitrix\Main\Loader::includeModule('crm');

use \kb\Model\DepartmentUpdateTable,
  \kb\Model\TelegramChatTable,
  \Bitrix\Im\Model\MessageParamTable,
  \Bitrix\Crm\Item,
  \Bitrix\Crm\Service,
  \Bitrix\Crm\Service\Container;

class RestApi extends \IRestService
{
  public static function OnRestServiceBuildDescriptionHandler()
  {
    return [
      'kb' => [
        'kb.deactivate' => [
          'callback' => [__CLASS__, 'deactivateUser'],
          'options' => [],
        ],
        'kb.crupd' => [
          'callback' => [__CLASS__, 'crupd'],
          'options' => [],
        ],
        'kb.addtaskbyxml' => [
          'callback' => [__CLASS__, 'addTaskByXmlId'],
          'options' => [],
        ],
        'kb.oschanges' => [
          'callback' => [__CLASS__, 'departmentChanges'],
          'options' => [],
        ],
        'kb.oschangesclear' => [
          'callback' => [__CLASS__, 'departmentChangesDelete'],
          'options' => [],
        ],
        'kb.startbizproc.crm' => [
          'callback' => [__CLASS__, 'startBizProcCrm'],
          'options' => [],
        ],
        'kb.telegram.message.add' => [
          'callback' => [__CLASS__, 'telegramMessageAdd'],
          'options' => [],
        ],
        'kb.telegram.openline.add' => [
          'callback' => [__CLASS__, 'openLineAdd'],
          'options' => [],
        ],
        'kb.asterisk.task.add' => [
          'callback' => [__CLASS__, 'addTaskByAsterisk'],
          'options' => [],
        ],

      ],
    ];
  }

  public static function departmentChanges($query, $n, \CRestServer $server): array
  {
    $lastId = null;
    $departments = $users = [];
    $rs = DepartmentUpdateTable::getList();
    while ($ar = $rs->fetch()) {
      $lastId = $ar['ID'];
      if (!$ar['UF_USER_ID']) {
        unset($ar['UF_USER_ID']);
        $departments[] = $ar;
      } else {
        unset($ar['UF_PARENT_ID']);
        unset($ar['UF_NAME']);
        $users[] = $ar;
      }
    }

    return [
      'departments' => $departments,
      'users' => $users,
      'last' => $lastId,
    ];
  }

  public static function departmentChangesDelete($query, $n, \CRestServer $server): array
  {
    $errors = RestApiHelpers::checkEmptyOrExist($query, ['ID']);
    if ($errors) {
      CHTTP::SetStatus("404 Not Found");
      return [
        'error' => 'ERROR_CODE',
        'error_description' => $errors,
      ];
    }
    $departments = [];
    $rs = DepartmentUpdateTable::getList([
      'filter' => ['<=ID' => $query['ID']],
    ]);

    while ($ar = $rs->fetch()) {
      $departments[] = $ar;
    }

    foreach ($departments as $department) {
      DepartmentUpdateTable::delete($department['ID']);
    }

    return [
      'status' => 'OK',
    ];
  }

  public static function deactivateUser($query, $n, \CRestServer $server): array
  {
    $errors = RestApiHelpers::checkEmptyOrExist($query, ['LOGIN']);
    if ($errors) {
      CHTTP::SetStatus("404 Not Found");
      return [
        'error' => 'ERROR_CODE',
        'error_description' => $errors,
      ];
    }
    $userId = RestApiHelpers::getUserByField('=LOGIN', $query['LOGIN']);
    $description = RestApiHelpers::deactivateById($userId);

    return [
      'status' => 'OK',
      'description' => $description,
    ];
  }

  public static function crupd($query, $n, \CRestServer $server): array
  {
    $errors = RestApiHelpers::checkEmptyOrExist($query, ['LOGIN', 'EMAIL']);
    if ($errors) {
      CHTTP::SetStatus("404 Not Found");
      return [
        'error' => 'ERROR_CODE',
        'error_description' => $errors,
      ];
    }

    $userId = RestApiHelpers::getUserByField('=LOGIN', $query['LOGIN']);

    $user = new CUser;
    $fields = $query;
    $id = $userId['ID'];
    if ($userId) {
      if ($userId['ID'] == $query['ID']) {
        unset($query['ID']);
        $user->Update($userId['ID'], $fields);

        return [
          (int)$id,
        ];
      } else {
        RestApiHelpers::deactivateById($userId);
        unset($query['ID']);
        $text = \Bitrix\Rest\Api\User::userAdd($query);
        return $text['error'] ? [
          $text['error_description'],
        ] : [
          $text,
        ];
      }
    } else {
      unset($query['ID']);
      $text = \Bitrix\Rest\Api\User::userAdd($query);
      return $text['error'] ? [
        $text['error_description'],
      ] : [
        $text,
      ];
    }
  }

  public static function addTaskByXmlId($query, $n, \CRestServer $server): false|array
  {
    $errors = RestApiHelpers::checkEmptyOrExist($query['fields'], ['CREATED_BY', 'RESPONSIBLE_ID']);
    if ($errors) {
      CHTTP::SetStatus("404 Not Found");
      return [
        'error' => 'ERROR_CODE',
        'error_description' => $errors,
      ];
    }

    $userId = RestApiHelpers::getUserByField('=XML_ID', $query['fields']['CREATED_BY'], true)['ID'];
    $responsibleId = RestApiHelpers::getUserByField('=XML_ID', $query['fields']['RESPONSIBLE_ID'], true)['ID'];
    unset($query['fields']['XML_ID']);

    $taskId = 0;
    if ($userId != 1 && $responsibleId != 1 && !empty($responsibleId) && !empty($userId)) {
      $query['fields']['CREATED_BY'] = $userId;
      $query['fields']['RESPONSIBLE_ID'] = $responsibleId;
      $query['fields']['CHANGED_BY'] = $userId;

      if (!CModule::IncludeModule("tasks")) {
        return false;
      }
      $result = \CTaskItem::add($query['fields'], $userId);
      $taskId = $result->getData(false)['ID'];
    } else {
      $result['error_description'] = (empty($responsibleId) || $responsibleId == 1 ? 'responsible' : 'created_by') . ' user not found';
    }

    return [
      'status' => !$result['error_description'] ? 'OK' : 'error',
      'description' => $result['error_description'] ?? 'task was created id = ' . $taskId,
    ];
  }
  public static function startBizProcCrm($query, $n, \CRestServer $server): array
  {
    $finish = [];

    $errors = RestApiHelpers::checkEmptyOrExist($query, ['REGISTER_NAME', 'DOCUMENT']);
    if ($errors) {
      CHTTP::SetStatus("404 Not Found");
      return [
        'error' => 'ERROR_CODE',
        'error_description' => $errors,
      ];
    }

    $factory = Container::getInstance()->getFactory(\CCrmOwnerType::SmartInvoice);

    $filesAr = [];

    foreach($query['DOCUMENT'] as $key => $document)
    {
      $arFile["MODULE_ID"] = "crm";
      $arFile["content"] = base64_decode($document['SRC']);
      $arFile["name"] = $document['NAME'];

      $fileId = CFile::SaveFile($arFile, "crm/invoices");
      $filesAr[] = \CFile::MakeFileArray($fileId);
    }

    $fields = [
      'UF_CRM_SMART_INVOICE_FILES' => $filesAr
    ];

    $docTitle = $query['REGISTER_NAME'];
    if(!empty($query['NUMBER'])){
      $docTitle .= ' №' . $query['NUMBER'];
    }
    if(!empty($query['DATE'])){
      $docTitle .= ' от ' . $query['DATE'];
    }

    $data = [
      'ASSIGNED_BY_ID' => 1,
      'COMPANY_ID' => 0,
      'TITLE' => $docTitle
    ];

    $newItem = $factory->createItem($data);
    $newItem->setFromCompatibleData($fields);
    $item = $newItem->save();

    if($item->getId() && self::initialBizprocInvoice(464, $item->getId()) != 'error') {// заменить на try catch

      //self::initialBizprocInvoice(464, $item->getId());

      return [
        'status' => 'OK',
        'description' =>  $item->getId(),
      ];

    } else {
      return [
        'status' => 'ERROR',
        'description' => 'Не удалось добавить элемент',
      ];
    }
  }

  private static function initialBizprocInvoice($bpId, $invoiceId, $userId = 16)
  {
    if (CModule::IncludeModule('bizproc'))
    {
      $arErrorsTmp = array();
      $arWorkflowParameters = array();
      $arParameters = array_merge($arWorkflowParameters, array("TargetUser" => "user_$userId"));
      $errorMessage = '';

      $wfId = CBPDocument::StartWorkflow(
        $bpId,
        array("crm", "Bitrix\Crm\Integration\BizProc\Document\SmartInvoice", "SMART_INVOICE_$invoiceId"),
        $arParameters,
        $arErrorsTmp
      );

      if (count($arErrorsTmp) > 0)
      {
        foreach ($arErrorsTmp as $e)
          $errorMessage .= "[".$e["code"]."] ".$e["message"]. $e["file"];

        self::log2file($errorMessage,'errorMessage--initialBizprocInvoice','/home/bitrix/www/local/logs/');
      }

      return $wfId;
    }
    else
    {
      return 'error';
    }

  }

  public static function telegramMessageAdd($query, $n, \CRestServer $server): array|bool
  {
    $errors = RestApiHelpers::checkEmptyOrExist($query, ['chat_id', 'username']);
    $errors += RestApiHelpers::checkEmptyOrExist($query, ['message'], false);
    if ($errors) {
      CHTTP::SetStatus("404 Not Found");
      return [
        'error' => 'ERROR_CODE',
        'error_description' => $errors,
      ];
    }

    $chat_id = htmlspecialchars($query['chat_id']);
    $username = htmlspecialchars($query['username']);
    $message = htmlspecialchars($query['message']);

    $info = TelegramChatTable::getList([
      'order' => ['ID' => 'DESC'],
      'filter' => ['UF_TELEGRAM_CHAT_ID' => $chat_id, 'UF_TELEGRAM_USERNAME' => $username],
      'select' => ['UF_LAST_MESSAGE', 'UF_TELEGRAM_NAME', 'UF_TELEGRAM_LAST_NAME', 'ID', 'UF_IS_OPENED', 'UF_IS_TEST'],
      'limit' => 1,
    ])->fetch();
    $getLineId = filter_var($info['UF_IS_TEST'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 'UF_OPENLINE_MONITA_ID_TEST' : 'UF_OPENLINE_MONITA_ID';

    $files = [];
    if (!empty($query['document_url'])) {
      $files[] = ['url' => $query['document_url']];
    }
    if (!empty($query['photo_url'])) {
      $files[] = ['url' => $query['photo_url']];
    }
    if (!empty($query['reply_to_message'])) {
      $orm = MessageParamTable::getList([
        'order' => ['ID' => 'DESC'],
        'select' => ['NAME' => 'USER.NAME', 'LAST_NAME' => 'USER.LAST_NAME', 'DATE_CREATE' => 'MESSAGE.DATE_CREATE'],
        'filter' => ['PARAM_VALUE' => $query['reply_to_message']['message_id'], 'PARAM_NAME' => 'CONNECTOR_MID'],
        'runtime' => [
          new \Bitrix\Main\Entity\ReferenceField(
            'MESSAGE',
            '\Bitrix\Im\Model\MessageTable',
            ['=this.MESSAGE_ID' => 'ref.ID',]
          ),
          new \Bitrix\Main\Entity\ReferenceField(
            'USER',
            '\Bitrix\Main\UserTable',
            ['=this.MESSAGE.AUTHOR_ID' => 'ref.ID',]
          ),
        ],
        'limit' => 1,
      ])->fetch();

      if ($orm) {
        $replyMessage =
          "------------------------------------------------------
{$orm["NAME"]} {$orm["LAST_NAME"]}[{$orm["DATE_CREATE"]->add("-2 hours")->toString()} мск]
{$query["reply_to_message"]["message"]}
------------------------------------------------------";

        $message = $replyMessage . $message;
      }
    }

    if ($info['UF_IS_OPENED']) {
      $arMessage = [
        'user' => [
          'id' => $chat_id,
          'name' => $info['UF_TELEGRAM_NAME'],
          'last_name' => $info['UF_TELEGRAM_LAST_NAME'],
          'url' => 'https://t.me/' . $username,
        ],
        'message' => [
          'id' => $query['message_id'],
          'disable_crm' => 'Y',
          'date' => time(),
          'text' => $message,
          'files' => $files,
        ],
        'chat' => [
          'id' => $chat_id,
          'url' => htmlspecialchars($_SERVER['HTTP_REFERER']),
        ],
      ];

      $result = CRest::call(
        'imconnector.send.messages', [
          'CONNECTOR' => TELEGRAM_CONNECTOR_ID,
          'LINE' => COption::GetOptionString("askaron.settings", $getLineId),
          'MESSAGES' => [$arMessage],
        ]
      )['result'];

      if ($result['SUCCESS']) {
        TelegramChatTable::update($info['ID'], [
          'UF_LAST_MESSAGE' => $info['UF_LAST_MESSAGE'] + 1,
        ]);
      }
    }

    return (bool)$info['UF_IS_OPENED'];
  }

  public static function openLineAdd($query, $n, \CRestServer $server)
  {
    $errors = RestApiHelpers::checkEmptyOrExist($query, ['chat_id', 'username']);
    $errors += RestApiHelpers::checkEmptyOrExist($query, ['is_test', 'first_name', 'last_name'], false);
    if ($errors) {
      CHTTP::SetStatus("404 Not Found");
      return [
        'error' => 'ERROR_CODE',
        'error_description' => $errors,
      ];
    }

    $first_name = htmlspecialchars($query['first_name'] ?: '');
    $last_name = htmlspecialchars($query['last_name'] ?: '');
    $chat_id = htmlspecialchars($query['chat_id']);
    $username = htmlspecialchars($query['username']);
    $topic = htmlspecialchars($query['topic']);

    $arMessage = [
      'user' => [
        'id' => $chat_id,
        'name' => $first_name,
        'last_name' => $last_name,
        'url' => 'https://t.me/' . $username,
      ],
      'message' => [
        'id' => $query['message_id'],
        'date' => time(),
        'text' => 'Новое обращение от ' . $username . ' ' . $last_name . ' ' . $first_name . '. Тема обращения - ' . $topic,
      ],
      'chat' => [
        'id' => $chat_id,
        'url' => htmlspecialchars($_SERVER['HTTP_REFERER']),
      ],
    ];

    $getLineId = filter_var($query['is_test'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 'UF_OPENLINE_MONITA_ID_TEST' : 'UF_OPENLINE_MONITA_ID';

    $result['error'] = 'error_save';
    $arMessage['message']['id'] = 0;
    $result = CRest::call(
      'imconnector.send.messages', [
        'CONNECTOR' => TELEGRAM_CONNECTOR_ID,
        'LINE' => COption::GetOptionString("askaron.settings", $getLineId),
        'MESSAGES' => [$arMessage],
      ]
    )['result'];

    if ($result['SUCCESS']) {
      TelegramChatTable::add([
        'UF_LAST_MESSAGE' => 0,
        'UF_TELEGRAM_USERNAME' => $username,
        'UF_TELEGRAM_NAME' => $first_name,
        'UF_TELEGRAM_LAST_NAME' => $last_name,
        'UF_TELEGRAM_CHAT_ID' => $chat_id,
        'UF_IS_OPENED' => 1,
        'UF_USER_ID' => reset($result['DATA']['RESULT'])['user'],
        'UF_CHAT_ID' => reset($result['DATA']['RESULT'])['chat']['id'],
        'UF_IS_TEST' => $query['is_test'],
      ]);
    }
    return $result;
  }

  /*
  * Обрабатывает запрос REST API завки ДТО из Asterisk и
  * Добавляет в смарт-процессе Заявки ДТО элемент
  * и запускает бизнес процесс
  * @param array ['SHOP_PHONE', 'DTO_PHONE'] - ID типа смарт-процесса
  * @return sring id элемента смарт-процесса или ошибку
  *
  */
  public static function addTaskByAsterisk($query, $n, \CRestServer $server)
  {
    $errors = RestApiHelpers::checkEmptyOrExist($query, ['SHOP_PHONE', 'DTO_PHONE']);
    if ($errors) {
      CHTTP::SetStatus("404 Not Found");
      return [
        'error' => 'ERROR_CODE',
        'error_description' => $errors,
      ];
    }
    \Bitrix\Main\Diag\Debug::dumpToFile($query, 'query', '1_logs.txt');
    \Bitrix\Main\Diag\Debug::dumpToFile($_SERVER['REQUEST_URI'], 'REQUEST_URI', '1_logs.txt');
    $smartTypeId = 182; //СП Заявки ДТО
    $smartShopsTypeId = 158; //СП Магазины
    $userId = 1;
    $bpTemplateId = 533;
    $smartIds = [];
    $filterShop = array('%UF_CRM_7_SHOP_PHONE' => self::phoneClear($query['SHOP_PHONE']));
    $smartIds = self::checkItemSmartProcess($smartShopsTypeId, $userId, $filterShop);
    \Bitrix\Main\Diag\Debug::dumpToFile($smartIds, '$smartIds', 'bp_logs.txt');
    $fields = [
      'UF_CRM_5_PHONE_ST' => self::phoneClear($query['DTO_PHONE']),
      'UF_CRM_5_PHONE_SHOP' => self::phoneClear($query['SHOP_PHONE']),
      'UF_CRM_5_TIME_PHONE' => $query['TIME'],
    ];
    if(count($smartIds) > 0) {
      $fields['UF_CRM_5_SHOP_ID'] = $smartIds[0];
    }
    \Bitrix\Main\Diag\Debug::dumpToFile($fields, '$fields', 'bp_logs.txt');
    $smartElemId = self::createSmartProcess($smartTypeId,$userId, $fields);
    \Bitrix\Main\Diag\Debug::dumpToFile($smartElemId, '$smartElemId', 'bp_logs.txt');
    if($smartElemId)
    {

      \Bitrix\Main\Diag\Debug::dumpToFile($smartElemId, 'smartElemId', '1_logs.txt');
      return [
        'status' => 'OK',
        'description' => $smartElemId,
      ];
    }else{
      return [
        'error' => 'ERROR_ADD_SP',
        'error_description' => 'Не удалось добавить элемент',
      ];
    }
  }
  /*
  * Добавляет в заданном смарт-процессе элемент
  *
  * @param integer $smartTypeId - ID типа смарт-процесса
  * @param integer $userId - ID пользователя
  * @param array $filter - массив значений полей элемента
  * @return sring id элемента смарт-процесса
  *
  */
  private static function createSmartProcess($smartTypeId = 182, $userId = 1, $fields=array())
  {

    $factory = Bitrix\Crm\Service\Container::getInstance()->getFactory($smartTypeId);

    $context = new \Bitrix\Crm\Service\Context();

    $context -> setUserId($userId);

    $item = $factory ->createItem($fields);

    $addOperation = $factory ->getAddOperation($item, $context);
    //для поддержки кастомизации и отключения проверок в виде:
    $addOperation->disableCheckAccess();// - отключение проверки доступа
    $addOperation->disableCheckFields();// - отключение проверки обязательности полей

    $operationResult = $addOperation->launch();


    if ( $operationResult->isSuccess() )
    {
      /**
       * Operation success!
       */

      return $item->getId();

    }
    else
    {
      /**
       * Some errors
       * Get error with:
       * $operationResult->getErrors();
       *
       * Get error messages:
       * $operationResult->getErrorMessages();
       */

      return $operationResult->getErrors() .'<==>'. $operationResult->getErrorMessages();
    }

  }
  /*
  * Добавляет в заданном смарт-процессе элемент
  *
  * @param integer $smartTypeId - ID типа смарт-процесса
  * @param integer $userId - ID пользователя
  * @param array $filter - массив значений полей элемента
  * @return sring id элемента смарт-процесса
  *
  */
  private static function createSmartProcessOld($smartTypeId = 182,$userId = 1, $fields=array())
  {
    $container = Service\Container::getInstance();
    $factory = $container->getFactory( $smartTypeId );
    $item = $factory->createItem($fields);
    // Setup other fields with $item->set* methods

    $saveOperation = $factory->getAddOperation($item);

    $operationResult = $saveOperation->launch();


  }

  /*
  * Проверяет есть ли в заданном смарт-процессе элемент по фильтру
  *
  * @param integer $smartTypeId - ID типа смарт-процесса
  * @param integer $userId - ID пользователя
  * @param array $filter - массив значений для фильтра
  * @return sring телефон
  *
  */
  private static function checkItemSmartProcess($smartTypeId = 158, $userId = 1, $filter=array())
  {
    $container = \Bitrix\Crm\Service\Container::getInstance();
    $factoryShop = $container->getFactory( $smartTypeId );
    $elements = $factoryShop->getItems(array(
      'filter' => $filter
    ));
    $elementsIds = array();
    foreach ($elements as $element)
    {
      $elementsIds[] = $element->getId();
    }
    //\Bitrix\Main\Diag\Debug::dumpToFile($elementsIds, '$elementsIds', 'bp_logs.txt');
    return $elementsIds;
  }


  /*
  * Приводит телефон к виду 79991117788
  *
  * @param string $phone - телефон
  * @return sring телефон
  *
  */
  private static function phoneClear($t){
    $t = mb_eregi_replace("[^0-9]", '', $t);
    if(strlen($t) > 9){$data = '7'.substr($t, -10);}else{$data = '';}
    return $data;
  }
}