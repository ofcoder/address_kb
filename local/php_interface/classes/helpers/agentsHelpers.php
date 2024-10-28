<?php
/*$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 4);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
*/
use \Bitrix\Timeman\Rest;
use \Bitrix\Timeman\Model\Worktime\Record\WorktimeRecordTable;
class AgentsHelpers
{
  public static function setTimeman($userId, $mode, $time)
  {
    if (CModule::IncludeModule('timeman') && CModule::IncludeModule('main')) {
      $seconds = $time - strtotime('today');
      $timemanUser = new CTimeManUser($userId);

      if ($timemanUser->isDayExpired()) {
        $lastOutTime = WorktimeRecordTable::getlist([
          'filter' => [
            'USER_ID' => $userId,
            'CURRENT_STATUS' => ['OPENED', 'PAUSED'],
            '<RECORDED_START_TIMESTAMP' => strtotime('today 00:00:00 Europe/Moscow'),
          ],
          'order' => ['ID' => 'DESC'],
          'select' => ['ID', 'TIME_START'],
          'limit' => 1
        ])->fetch();

        if ($lastOutTime['ID']) {
          $ar = $timemanUser->CloseDay('65100', 'BY BITRIX');
          if (!$ar) {
            WorktimeRecordTable::update($lastOutTime['ID'], [
              'TIME_LEAKS' => 0,
              'DURATION' => 65100 - $lastOutTime['TIME_START'],
              'RECORDED_DURATION' => 65100 - $lastOutTime['RECORDED_DURATION'],
              'ACTUAL_BREAK_LENGTH' => 0
            ]);
            $timemanUser->CloseDay('65100', 'BY BITRIX');
          }
        }
      }

      switch ($mode) {
        case 1:
          if (!$timemanUser->isDayOpenedToday()) {
            $timemanUser->OpenDay();
          } else {
            $timemanUser->ReopenDay($seconds);
          }
          break;
        case 2:
          if ($seconds < '65100') {
            $timemanUser->PauseDay();
          } else {
            $timemanUser->CloseDay($seconds, 'CLOSE BY BITRIX');
          }
          break;
      }
    }
  }

  public static function setSonetGroups($workGroups = []): bool|array
  {
    if (!CModule::IncludeModule("socialnetwork")) {
      return false;
    }

    $arSelectedGroups = CSocNetGroup::GetList(
      ["ID" => "DESC"],
      ["NAME" => $workGroups, 'PROJECT' => 'N', 'ACTIVE' => 'Y', 'VISIBLE' => 'N', 'SUBJECT_ID' => '6', 'OWNER_ID' => '1', 'OPENED' => 'N'],
      false,
      false,
      ['ID', 'NAME']
    );

    $selectedGroups = [];
    while ($selectedGroup = $arSelectedGroups->fetch()) {
      $selectedGroups[$selectedGroup['ID']] = $selectedGroup['NAME'];
    }
    $workGroupsToAdd = array_diff($workGroups, $selectedGroups);

    foreach ($workGroupsToAdd as $workGroupToAdd) {
      $newGroupId = CSocNetGroup::CreateGroup(1, [
        'SITE_ID' => 'kb',
        'NAME' => $workGroupToAdd,
        'VISIBLE' => 'N',
        'OPENED' => 'N',
        'SUBJECT_ID' => '6',
        'INITIATE_PERMS' => SONET_ROLES_OWNER,
        'SPAM_PERMS' => SONET_ROLES_USER
      ]);

      $selectedGroups[$newGroupId] = $workGroupToAdd;
    }

    return $selectedGroups;
  }

  public static function getSonetGroupByName($name) {
    if (!CModule::IncludeModule("socialnetwork")) {
      return false;
    }

    return !CSocNetGroup::GetList(
      ["ID" => "DESC"],
      ["NAME" => $name, 'PROJECT' => 'N', 'ACTIVE' => 'Y', 'VISIBLE' => 'N', 'SUBJECT_ID' => '6', 'OWNER_ID' => '1', 'OPENED' => 'N'],
      false,
      false,
      ['ID', 'NAME']
    )->fetch();
  }
}