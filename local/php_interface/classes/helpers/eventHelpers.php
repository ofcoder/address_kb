<?php
use \kb\Model\DepartmentUpdateTable as Department;

class EventHelpers
{
    public static function changeDepartment($id, $parentId, $name, $userId = 0): void
    {
        $fields = [
            'UF_ID' => $id,
            'UF_PARENT_ID' => empty($parentId) ? 0 : $parentId,
            'UF_NAME' => $name,
            'UF_USER_ID' => $userId
        ];

        if ($userId) {
            $departmentId = Department::getList(['filter' => ['UF_USER_ID' => $userId]])->fetch()['ID'];
        } else {
            $departmentId = Department::getList(['filter' => ['UF_ID' => $id]])->fetch()['ID'];
        }

        if ($departmentId) {
            Department::update($departmentId, $fields);
        } else {
            Department::add($fields);
        }
    }
}