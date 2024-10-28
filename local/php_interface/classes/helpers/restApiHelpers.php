<?php
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 4);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

class RestApiHelpers
{
    public static function deactivateById($userId): string
    {
        $description = 'User was deactivated!';
        if ($userId) {
            $newEmail = "bx24_" . $userId['ID'] . "@krasnoe-beloe.ru";
            $user = new CUser;
            $fields = [
                'ACTIVE' => 'N',
                'LOGIN' => $newEmail,
                'EMAIL' => $newEmail,
            ];
            $user->Update($userId['ID'], $fields);
        } else {
            $description = 'User not found!';
        }

        return $description;
    }

    public static function getUserByField($fieldName, $fieldValue, $isActive = false)
    {
        $filter = [$fieldName => $fieldValue];
        if ($isActive) {
            $filter['ACTIVE'] = 'Y';
        }

        return \Bitrix\Main\UserTable::getList([
            'order' => ['ID' => 'DESC'],
            'filter' => $filter,
            'limit' => 1,
            'select' => ['ID'],
        ])->fetch();
    }

    public static function checkEmptyOrExist($query, $fieldsToCheck, $forEmpty = true): array
    {
        $errors = [];
        foreach ($fieldsToCheck as $fieldToCheck) {
            if ($forEmpty) {
                if (empty($query[$fieldToCheck])) {
                    $errors[] = 'Empty ' . $fieldToCheck;
                }
            } else {
                if (!array_key_exists($fieldToCheck, $query)) {
                    $errors[] = 'Empty ' . $fieldToCheck;
                }
            }
        }

        return $errors;
    }
}