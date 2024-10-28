<?php
function UpdateExtranetUsersFromWMS()
{
  \Bitrix\Main\Diag\Debug::dumpToFile('start \n', 'userUpdate', '/local/logs/wms_logs.txt');
  $conn_id = ftp_connect(FTP_WMS_SERVER);
  $login_result = ftp_login($conn_id, FTP_WMS_USER_NAME, FTP_WMS_USER_PASS);

  \Bitrix\Main\Diag\Debug::dumpToFile(FTP_WMS_USER_NAME . ' ' . FTP_WMS_USER_PASS, 'userUpdate', '/local/logs/wms_logs.txt');

  $local_file = dirname(__DIR__, 3) . '/upload/learning/wms_' . date_format(date_create(), 'd-m-Y-H:i:s') . '.csv';
  $handle = fopen($local_file, 'w+');

  $mode = ftp_pasv($conn_id, true);
  $dirs = ftpRecursiveFileListing($conn_id, '/');
  foreach ($dirs as $dir) {
    try {
      ftp_fget($conn_id, $handle, $dir);
    } catch (Exception $e) {
      \Bitrix\Main\Diag\Debug::dumpToFile("При скачивании " . $dir . " произошла проблема\n" . $e->getMessage(), $dir, '/local/logs/wms_errors.txt');
    }
  }

  rewind($handle);
  $workGroups = $workCities = $existCities = $addFields = $existUsers = $updateFields = [];

  $rsUser = \Bitrix\Main\UserGroupTable::getList([
    'order' => ['USER.ID' => 'DESC'],
    'filter' => [
      'USER.ACTIVE' => 'Y',
      'GROUP_ID' => 29,
      '!USER.XML_ID' => false,
      '!USER.WORK_CITY' => false,
    ],
    'select' => [
      'ID' => 'USER.ID',
      'LAST_NAME' => 'USER.LAST_NAME',
      'LOGIN' => 'USER.LOGIN',
      'NAME' => 'USER.NAME',
      'EMAIL' => 'USER.EMAIL',
      'SECOND_NAME' => 'USER.SECOND_NAME',
      'WORK_CITY' => 'USER.WORK_CITY',
      'WORK_POSITION' => 'USER.WORK_POSITION',
      'XML_ID' => 'USER.XML_ID',
      'UF_EMPLOYMENT_DATE' => 'USER.UF_EMPLOYMENT_DATE',
    ],
  ]);
  while ($arUser = $rsUser->fetch()) {
    $existUsers[$arUser['LOGIN']] = $arUser;
  }

  $rsCities = kb\Model\WorkCitiesTable::getList();
  while ($arCity = $rsCities->fetch()) {
    $existCities[] = $arCity['UF_CITY'];
  }

  while (($data = fgetcsv($handle, 1000, "|")) !== false) {
    if (!is_numeric(trim($data[4], ' \n\r\t\v\x00')) || str_contains(trim($data[4]), ".")) {
      continue;
    }

    $workGroupTrimmed = mb_strtolower(trim(preg_replace('/[0-9*]+/', '', $data[3]), ' \n\r\t\v\x00.'));
    $workCroupCamel = mb_strtoupper(mb_substr($workGroupTrimmed, 0, 1)) . mb_substr($workGroupTrimmed, 1);
    if (!in_array($workCroupCamel, $workGroups) && !is_numeric($workCroupCamel) && !empty($workCroupCamel)) {
      $workGroups[] = $workCroupCamel;
    }

    // $workCityTrimmed = trim($data[0], '﻿');
    $workCityTrimmed = removeBOM($data[0]);

    if (!in_array($workCityTrimmed, $workCities)) {
      $workCities[] = $workCityTrimmed;
    }

    $partsOfName = explode(' ', trim(preg_replace('/[0-9*]+/', '', $data[1]), ' \n\r\t\v\x00.'));
    $uniqueKey = str_replace(' ', '_', translite($workCityTrimmed) . trim($data[4]) . '@krasnoe-beloe.ru');
    if (!empty($existUsers[$uniqueKey]) && CModule::IncludeModule("socialnetwork")) { // Проверяем на существующих пользователей
      $badSonet = AgentsHelpers::getSonetGroupByName($workCroupCamel);
      if ($existUsers[$uniqueKey]['LAST_NAME'] != $partsOfName[0]
        || $existUsers[$uniqueKey]['NAME'] != $partsOfName[1]
        || $existUsers[$uniqueKey]['SECOND_NAME'] != $partsOfName[2]
        || $existUsers[$uniqueKey]['UF_EMPLOYMENT_DATE'] != $data[2]
        || $existUsers[$uniqueKey]['WORK_POSITION'] != $workCroupCamel
        || $badSonet
      ) {
        $fieldsToUpdate = [
          'ID' => $existUsers[$uniqueKey]['ID'],
          'LAST_NAME' => $partsOfName[0],
          'NAME' => $partsOfName[1],
          'SECOND_NAME' => $partsOfName[2] ?? '',
          'WORK_POSITION' => $workCroupCamel,
          'UF_EMPLOYMENT_DATE' => $data[2] ?? '',
        ];

        if ($existUsers[$uniqueKey]['WORK_POSITION'] != $workCroupCamel || $badSonet) {
          $fieldsToUpdate['SONET_GROUP_ID'] = $workCroupCamel;
        }

        $updateFields[] = $fieldsToUpdate;
      }
    } else {
      if (!empty($workCroupCamel)) {
        $addFields[] = [
          'LAST_NAME' => $partsOfName[0],
          'NAME' => $partsOfName[1] ?? '',
          'SECOND_NAME' => $partsOfName[2] ?? '',
          'WORK_CITY' => $workCityTrimmed,
          'XML_ID' => trim($data[4]),
          'EMAIL' => $uniqueKey,
          'EXTRANET' => 'Y',
          'SONET_GROUP_ID' => $workCroupCamel,
          'WORK_POSITION' => $workCroupCamel,
          'UF_EMPLOYMENT_DATE' => $data[2] ?? '',
        ];
      }
    }
  }
  fclose($handle);
  ftp_close($conn_id);
  $workGroups = AgentsHelpers::setSonetGroups($workGroups); // Переопределение SONET_GROUP_ID

  global $USER;
  $USER = new CUser;
  $USER->Authorize(1, false, false);

  foreach ($addFields as $addField) {
    $addField['SONET_GROUP_ID'] = [array_search($addField['SONET_GROUP_ID'], $workGroups)];
    try {
      \Bitrix\Rest\Api\User::userAdd($addField);
    } catch (Exception $e) {
      \Bitrix\Main\Diag\Debug::dumpToFile("Ошибка при добавлении пользователя" . $e->getMessage() . "\n", 'userAdd', '/local/logs/wms_errors.txt');
      \Bitrix\Main\Diag\Debug::dumpToFile( $addField, 'addField', '/local/logs/wms_errors.txt');
    }
  }

  foreach ($updateFields as $updateField) {
    try {
      if (!empty($updateField['SONET_GROUP_ID']) && CModule::IncludeModule("socialnetwork")) {
        $sonetId = CSocNetUserToGroup::GetList([], ["USER_ID" => $updateField['ID']])->fetch()['ID'];
        if ($sonetId) {
          CSocNetUserToGroup::Delete($sonetId);
        }
        CSocNetUserToGroup::Add([
          "USER_ID" => $updateField['ID'],
          "GROUP_ID" => array_search($updateField['SONET_GROUP_ID'], $workGroups),
          "ROLE" => SONET_ROLES_USER,
          "DATE_CREATE" => new \Bitrix\Main\Type\DateTime(),
          "DATE_UPDATE" => new \Bitrix\Main\Type\DateTime(),
          "INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
          "INITIATED_BY_USER_ID" => 1,
          "MESSAGE" => false,
        ]);
        unset($updateField['SONET_GROUP_ID']);
      }

      \Bitrix\Rest\Api\User::userUpdate($updateField);
    } catch (Exception $e) {
      \Bitrix\Main\Diag\Debug::dumpToFile("Ошибка при обновлении пользователя " . $e->getMessage() . "\n", 'userUpdate', '/local/logs/wms_errors.txt');

    }
  }

  unset($USER);

  foreach ($workCities as $workCity) {
    if (!in_array($workCity, $existCities)) {
      try {
        kb\Model\WorkCitiesTable::add(['UF_CITY' => $workCity]);
      } catch (Exception $e) {
        \Bitrix\Main\Diag\Debug::dumpToFile("Ошибка при добавлении города" . $e->getMessage() . "\n", $workCity, '/local/logs/wms_errors.txt');
      }
    }
  }

  return "UpdateExtranetUsersFromWMS();";
}

function UpdateAddressProgramUsersFrom1c()
{
  $data = getResponce(ODINASS_SHOPS_SERVER . 'employees', '', ODINASS_SHOPS_INFO);
  $users = json_decode($data, true);

  $rsShops = kb\Model\ShopsTable::getList([ //К какой роли относится
    'select' => ['ID', 'UF_ADMIN_ID', 'UF_ZRU_ID', 'UF_SUPERVISOR', 'UF_RU_ID', 'UF_NUMBER'],
  ]);
  $ru = $zru = $supervisor = $admin = [];
  while ($arShop = $rsShops->fetch()) {
    if (!empty($arShop['UF_ADMIN_ID'])) {
      $admin[] = $arShop['UF_ADMIN_ID'];
    }
    if (!empty($arShop['UF_SUPERVISOR'])) {
      $supervisor[] = $arShop['UF_SUPERVISOR'];
    }
    if (!empty($arShop['UF_ZRU_ID'])) {
      $zru[] = $arShop['UF_ZRU_ID'];
    }
    if (!empty($arShop['UF_RU_ID'])) {
      $ru[] = $arShop['UF_RU_ID'];
    }
  }

  foreach ($users as $user) {
    $arUser = \Bitrix\Main\UserTable::getList([
      'select' => ['ID', 'XML_ID'],
      'filter' => ['XML_ID' => $user['Номер']],
      'cache' => [
        'ttl' => 3600,
      ],
    ])->fetch();

    $newUser = new CUser;
    if (empty($arUser) && $user['Активен']) {
      $role = '';
      if (in_array($user['Номер'], $admin)) {
        $role = USER_GROUP_ID_SHOPS_ADMIN;
      }
      if (in_array($user['Номер'], $supervisor)) {
        $role = USER_GROUP_ID_SUPERVISOR;
      }
      if (in_array($user['Номер'], $zru)) {
        $role = USER_GROUP_ID_ZRU;
      }
      if (in_array($user['Номер'], $ru)) {
        $role = USER_GROUP_ID_RU;
      }

      $partsOfName = explode(' ', trim(preg_replace('/[0-9*]+/', '', $user['ФИО']), ' \n\r\t\v\x00.'));
      $password = rand(10000000, 99999999);
      $arFields = [
        "NAME" => $partsOfName[1],
        "LAST_NAME" => $partsOfName[0],
        "SECOND_NAME" => $partsOfName[2],
        "EMAIL" => $user['Email'],
        "LOGIN" => $user['Email'],
        "ACTIVE" => "Y",
        "GROUP_ID" => [USER_GROUP_ID_ADDRESS_PROGRAM, $role],
        "PASSWORD" => $password,
        "CONFIRM_PASSWORD" => $password,
        "PERSONAL_PHONE" => $user['Телефон'],
        "XML_ID" => $user['Номер'],
      ];
      try {
        $ID = $newUser->Add($arFields);
      } catch (Exception $e) {
        \Bitrix\Main\Diag\Debug::dumpToFile("Ошибка при добавлении пользователя" . $e->getMessage() . "\n", 'userAdd', '/local/logs/address_errors.txt');
      }
    } else if (!empty($arUser) && !$user['Активен']){
      global $USER;
      $USER = new CUser;
      $USER->Authorize(1, false, false);

      $newUser->Delete($arUser['ID']);

      unset($USER);
    }
  }

  return "UpdateAddressProgramUsersFrom1c();";
}

function UpdateAddressProgramShopsFrom1c()
{
  $data = getResponce(ODINASS_SHOPS_SERVER . 'shops', '', ODINASS_SHOPS_INFO);
  $shops = json_decode($data, true);

  $rsShops = kb\Model\ShopsTable::getList([
    'select' => ['ID', 'UF_ADMIN_ID', 'UF_ZRU_ID', 'UF_SUPERVISOR', 'UF_RU_ID', 'UF_NUMBER', 'UF_LONGITUDE', 'UF_LATITUDE'],
  ]);
  $existShops = [];
  while ($arShop = $rsShops->fetch()) {
    $existShops[$arShop['UF_NUMBER']] = $arShop;
  }

  foreach ($shops as $shop)
  {
    if (!empty($existShops[$shop['Номер']]['ID']) && $shop['Статус'] == 'Закрыт') {
      kb\Model\ShopsTable::delete($existShops[$shop['Номер']]['ID']);
      continue;
    }

    if (empty($shop['Номер']) || $shop['Статус'] == 'Закрыт')
      continue;

    if (empty($existShops[$shop['Номер']])) {
      kb\Model\ShopsTable::add([
        'UF_ADDRESS' => $shop['Адрес'],
        'UF_REGION' => $shop['Регион'],
        'UF_NUMBER' => $shop['Номер'],
        'UF_CITY' => $shop['Город'],
        'UF_TERRITORY' => $shop['Территория'],
        'UF_PEOPLE' => $shop['ЧисленностьНаселения'],
        'UF_PHONE' => $shop['Телефон'],
        'UF_ENTITY' => $shop['ЮридическоеЛицо'],
        'UF_EMAIL' => $shop['Email'],
        'UF_ADMIN_ID' => $shop['АдминистраторМагазина'],
        'UF_ZRU_ID' => $shop['ЗРУ'],
        'UF_SUPERVISOR' => $shop['Супервайзер'],
        'UF_RU_ID' => $shop['РУ'],
        'UF_STATUS' => '1',
        'UF_COMMENT' => '',
        'UF_ADDRESS_SUM' => '0',
        'UF_LONGITUDE' => $shop['Долгота'],
        'UF_LATITUDE' => $shop['Широта'],
      ]);
    } else if (
      $existShops[$shop['Номер']]['UF_RU_ID'] != $shop['РУ']
      || $existShops[$shop['Номер']]['UF_ZRU_ID'] != $shop['ЗРУ']
      || $existShops[$shop['Номер']]['UF_SUPERVISOR'] != $shop['Супервайзер']
      || $existShops[$shop['Номер']]['UF_ADMIN_ID'] != $shop['АдминистраторМагазина']
      || $existShops[$shop['Номер']]['UF_PHONE'] != $shop['Телефон']
      || $existShops[$shop['Номер']]['UF_LONGITUDE'] != $shop['Долгота']
      || $existShops[$shop['Номер']]['UF_LATITUDE'] != $shop['Широта']
    ) {
      $updateFields = [
        'UF_ADMIN_ID' => $shop['АдминистраторМагазина'],
        'UF_ZRU_ID' => $shop['ЗРУ'],
        'UF_SUPERVISOR' => $shop['Супервайзер'],
        'UF_RU_ID' => $shop['РУ'],
        'UF_PHONE' => $shop['Телефон'],
        'UF_LONGITUDE' => $shop['Долгота'],
        'UF_LATITUDE' => $shop['Широта'],
      ];

      kb\Model\ShopsTable::update($existShops[$shop['Номер']]['ID'], $updateFields);
    }
  }

  return "UpdateAddressProgramShopsFrom1c();";
}

/*
* Обновляет данные сотрудников ДТО в highload-блоке Магазины
* Берет данные из  /upload/1c/dto/Shops.xml
* Обновляет телефон и признак сотрудника ДТО в таблице Пользователей
* Добавляет пользователя в группу ДТО
* @return function UpdateScheduleDto() или ошибку
*
*/
function UpdateScheduleDto()
{
  \Bitrix\Main\Diag\Debug::dumpToFile("START========================================================================", '========================================================================'.(new DateTime())->format("y:m:d h:i:s"), "/local/logs/update_dto_log.txt");
  if (!CModule::IncludeModule("socialnetwork"))
  {
    \Bitrix\Main\Diag\Debug::dumpToFile("Модуль socialnetwork не подключен", (new DateTime())->format("y:m:d h:i:s") . 'Modul_error', '/local/logs/update_dto_log.txt');
    return;
  }

  $users = $emails = $shops = $shopsId = $phones = $shopsPersonals = $phoneIds = [];
  $positions = ['st','engineer','deputy_head_engineer','head_engineer'];

  //Выборка из файла /upload/1c/dto/Shops.xml данных по магазину
  $fileName = 'Shops.xml';
  $filePath = $_SERVER["DOCUMENT_ROOT"] . '/upload/1c/dto/' . $fileName;
  if (file_exists($filePath)) {
    $stream = fopen($filePath, 'r');
  } else{
    \Bitrix\Main\Diag\Debug::dumpToFile("Файл $filePath не найден", (new DateTime())->format("y:m:d h:i:s") . 'Файл_error', '/local/logs/update_dto_log.txt');
    return;
  }

  if (($data = fread($stream, filesize($filePath))))
  {
    $previous = libxml_use_internal_errors(true);
    $xml = simplexml_load_file($filePath);

    if (!$xml)
    {
      $errors = getXMLErrorString();
      libxml_use_internal_errors($previous);
      \Bitrix\Main\Diag\Debug::dumpToFile("Ошибки парсинга файла $filePath: " . print_r($errors), (new DateTime())->format("y:m:d h:i:s") .'--XML_error', '/local/logs/update_dto_log.txt');
      throw new UnexpectedValueException($errors);

    }

    $shops = json_decode(json_encode($xml), true)['shop'];

    //формирование массива $users с ключом email
    //формирование массива $emails из email
    //формирование массива $phones из phone
    $phone_regex = "/^[0-9]{11}$/";
    $email_regex = "/^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$/i";
    foreach ($shops as $shop)
    {
      $dto = $shop['dto'];
      $shopNumber = $shop['shop_id'];
      //наполнение массива $shopsPersonals с ключом номер магазина и значением массивом сотрудников $dto
      $shopsPersonals[$shopNumber] = $dto;

      foreach ($positions as $position)
      {
        //наполнение массива $phones
        try
        {
          if (!is_array($dto[$position]['phone']) && preg_match($phone_regex, $dto[$position]['phone']))
          {
            $userPhone = $dto[$position]['phone'];
            if (isset($userPhone) && !in_array($userPhone, $phones))
              $phones[] = $userPhone;
          }
          else
          {
            \Bitrix\Main\Diag\Debug::dumpToFile("PHONE ERORR " . $dto[$position]['phone'] . " ФИО: " . $dto[$position]['name'] . " № магазина: " .  $shop['shop_id'], (new DateTime())->format("y:m:d h:i:s") . " phone_error", "/local/logs/update_dto_log.txt");
          }
        }
        catch(Exception  $ex)
        {
          \Bitrix\Main\Diag\Debug::dumpToFile("ERROR IN TRY PHONE: " . $dto[$position]['phone'] . " Сообщение об ошибке: " . $ex->getMessage() . "\n" . " Номер магазина: " .  $shop['shop_id'],  (new DateTime())->format("y:m:d h:i:s") . '--phone_catch_error', '/local/logs/update_dto_log.txt');
        }
        //наполнение массива $emails и $users
        try
        {
          if(!is_array($dto[$position]['email']))
            if(filter_var($dto[$position]['email'], FILTER_SANITIZE_EMAIL) && preg_match($email_regex, $dto[$position]['email']))
              $userEmail = $dto[$position]['email'];

          if (isset($userEmail) && $userEmail)
          {
            if (!in_array($userEmail, $emails))
              $emails[] =  $userEmail;
            $users[$userEmail]['position'] = $position;
            if (isset($userPhone))
              $users[$userEmail]['phone'] = $userPhone;
          }
          else
          {
            \Bitrix\Main\Diag\Debug::dumpToFile("EMAIL: " . $dto[$position]['email'] . " Phone: " . $dto[$position]['phone'] . " ФИО: " . $dto[$position]['name'] . " № магазина: " .  $shop['shop_id'], (new DateTime())->format("y:m:d h:i:s") . " emails_width_error ", "/local/logs/update_dto_log.txt");
          }
        }
        catch(Exception  $ex)
        {
          \Bitrix\Main\Diag\Debug::dumpToFile("EMAIL: " . $dto[$position]['email'] . " Сообщение об ошибке: " . $ex->getMessage() . "\n" . " Номер магазина: " .  $shop['shop_id'],  (new DateTime())->format("y:m:d h:i:s") . '--emails_catch_error', '/local/logs/update_dto_log.txt');
        }
        unset($userPhone, $userEmail);
      }
    }
  }

  fclose($stream);

  //Выборка пользователей из таблицы пользователей фильтруя  по $emails
  $arUsers = \Bitrix\Main\UserTable::getList([
    'filter' => ['EMAIL' => $emails],
    'select' => ['ID', 'EMAIL', 'PERSONAL_PHONE', 'UF_DTO_FIELD'],
  ]);

  //Обновление пользователя по email его полей UF_DTO_FIELD и PERSONAL_PHONE данные берутся из $users (из xml)
  while ($bitrixUser = $arUsers->fetch())
  {
    $bitrixUserEmail = $bitrixUser['EMAIL'];
    $users[$bitrixUserEmail]['id'] = $bitrixUser['ID'];
    //Обновление тех пользователей у которых не заполнено поле UF_DTO_FIELD
    if (!$bitrixUser['UF_DTO_FIELD'])
    {
      $user = new CUser;
      $user->Update($bitrixUser['ID'], [
        "PERSONAL_PHONE" => $users[$bitrixUserEmail]['phone'],
        "UF_DTO_FIELD" => $users[$bitrixUserEmail]['position'],
      ]);
      //Добавление пользователя в группу ДТО
      CSocNetUserToGroup::Add([
        "USER_ID" => $bitrixUser['ID'],
        "GROUP_ID" => DTO_GROUP_ID,
        "ROLE" => SONET_ROLES_USER,
        "DATE_CREATE" => new \Bitrix\Main\Type\DateTime(),
        "DATE_UPDATE" => new \Bitrix\Main\Type\DateTime(),
        "INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
        "INITIATED_BY_USER_ID" => 1,
        "MESSAGE" => false,
      ]);
    }
    unset($bitrixUserEmail);
  }

  //Обновление highload-блока Магазины
  //Формирование массива $shopsId с ключом номер магазина и значением id из highload-блока Магазины
  $ormShops = kb\Model\ShopsTable::getList(['select' => ['ID', 'UF_NUMBER']]);

  //наполнение массива $shopsId номер магазина=>id магазина в highload-блоке
  while ($bitrixShop = $ormShops->fetch()) {
    $shopsId[$bitrixShop['UF_NUMBER']] = $bitrixShop['ID'];
  }

  //Создание массива $phoneIds телефон=> id пользователя по фильтру телефонов из массива $phones
  $arUsers1 = \Bitrix\Main\UserTable::getList([
    'filter' => ['PERSONAL_PHONE' => $phones],
    'select' => ['ID', 'PERSONAL_PHONE'],
  ]);

  while ($phoneUser = $arUsers1->fetch())
  {
    if(!empty($phoneUser['PERSONAL_PHONE']))
    {
      $phoneIds[$phoneUser['PERSONAL_PHONE']] = $phoneUser['ID'];
    }
    else{
      \Bitrix\Main\Diag\Debug::dumpToFile("PHONE " . $phoneUser['PERSONAL_PHONE'] . $phoneUser['ID'], (new DateTime())->format("y:m:d h:i:s") . " user_phone_empty ", "/local/logs/update_dto_log.txt");
    }
  }

  //Обновление highload-блока данными из $shopsPersonals(xml) и  $users(xml) по наличию номера магазина в массивах $shopsId
  if (count($shopsPersonals) > 0)
  {
    foreach ($shopsPersonals as $shopNumber => $shop)
    {
      $idSt = (!empty($shop['st']['phone']) && !is_array($shop['st']['phone']))? $phoneIds[$shop['st']['phone']]: '0';
      $idEng = (!empty($shop['engineer']['phone']) && !is_array($shop['engineer']['phone']))? $phoneIds[$shop['engineer']['phone']]: '0';
      $idDeng = (!empty($shop['deputy_head_engineer']['phone']) && !is_array($shop['deputy_head_engineer']['phone']))? $phoneIds[$shop['deputy_head_engineer']['phone']]: '0';
      $idHeng = (!empty($shop['head_engineer']['phone']) && !is_array($shop['head_engineer']['phone']))? $phoneIds[$shop['head_engineer']['phone']]: '0';

      $fields = [
        'UF_USER_ID_ST' => $idSt,
        'UF_USER_ID_ENGINEER' => $idEng,
        'UF_USER_ID_DEPUTY_HEAD_ENGINEER' => $idDeng,
        'UF_USER_ID_HEAD_ENGINEER' => $idHeng,
      ];

      if (!empty($shopsId[$shopNumber]))
      {
        $resultUpdateShops = kb\Model\ShopsTable::update($shopsId[$shopNumber], $fields);
      }
      else{
        \Bitrix\Main\Diag\Debug::dumpToFile("shop number: " . $shopNumber , (new DateTime())->format("y:m:d h:i:s") . " shopsId_empty ", "/local/logs/update_dto_log.txt");
      }
    }

  }

  //Распределение по сотрудников ДТО по группам для распределения прав
  $roleIds = array(
    'st' => array(46),
    'engineer' => array(47),
    'deputy_head_engineer' => array(48),
    'head_engineer' => array(48),

  );
  //\Bitrix\Main\Diag\Debug::dumpToFile($user, 'user', '/local/logs/update_user_group.txt');
  foreach($users as $userDTO)
  {
    $userRole = $userDTO['position'];
    $userID = $userDTO['id'];
    $groupIds = $roleIds[$userRole];

    //\Bitrix\Main\Diag\Debug::dumpToFile($groupIds, 'groupIds', '/local/logs/update_user_group.txt');

    if(!empty($userRole) && !empty($userRole) && !empty($groupIds))
      CUser::SetUserGroup($userID, array_merge(CUser::GetUserGroup($userID), $roleIds[$userRole]));
  }
  Bitrix\Main\Diag\Debug::dumpToFile(count($users), 'count $users', '/local/logs/update_user_group.txt');
  Bitrix\Main\Diag\Debug::dumpToFile(count($phoneIds), 'count $phoneIds', '/local/logs/update_user_group.txt');
  Bitrix\Main\Diag\Debug::dumpToFile(count($shopsPersonals), 'count $shopsPersonals', '/local/logs/update_user_group.txt');
  unset($user,$emails,$shops,$shopsId,$phones,$shopsPersonals,$phoneIds);

  return 'UpdateScheduleDto();';
}