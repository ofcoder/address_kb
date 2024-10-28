<?
namespace kb\Model;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;

/**
 * Class DepartmentUpdateTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_ID text optional
 * <li> UF_PARENT_ID text optional
 * <li> UF_NAME text optional
 * <li> UF_USER_ID text optional
 * </ul>
 *
 * @package Bitrix\Kb
 **/

class DepartmentUpdateTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kb_department_update';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('DEPARTMENT_UPDATE_ENTITY_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_ID',
                [
                    'title' => Loc::getMessage('DEPARTMENT_UPDATE_ENTITY_UF_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_PARENT_ID',
                [
                    'title' => Loc::getMessage('DEPARTMENT_UPDATE_ENTITY_UF_PARENT_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_NAME',
                [
                    'title' => Loc::getMessage('DEPARTMENT_UPDATE_ENTITY_UF_NAME_FIELD'),
                ]
            ),
            new TextField(
                'UF_USER_ID',
                [
                    'title' => Loc::getMessage('DEPARTMENT_UPDATE_ENTITY_UF_USER_ID_FIELD'),
                ]
            ),
        ];
    }
}