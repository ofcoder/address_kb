<?php
namespace kb\Model;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;

/**
 * Class ShopEmployeesTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_SHOP_NUMBER int optional
 * <li> UF_USER_PHONE text optional
 * <li> UF_USER_EMAIL text optional
 * <li> UF_FIO text optional
 * <li> UF_USER_ID int optional
 * <li> UF_USER_POSITION int optional
 * </ul>
 *
 * @package Bitrix\Kb
 **/

class ShopEmployeesTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kb_shop_employees';
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
                    'title' => Loc::getMessage('SHOP_EMPLOYEES_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_SHOP_NUMBER',
                [
                    'title' => Loc::getMessage('SHOP_EMPLOYEES_ENTITY_UF_SHOP_NUMBER_FIELD'),
                ]
            ),
            new TextField(
                'UF_USER_PHONE',
                [
                    'title' => Loc::getMessage('SHOP_EMPLOYEES_ENTITY_UF_USER_PHONE_FIELD'),
                ]
            ),
            new TextField(
                'UF_USER_EMAIL',
                [
                    'title' => Loc::getMessage('SHOP_EMPLOYEES_ENTITY_UF_USER_EMAIL_FIELD'),
                ]
            ),
            new TextField(
                'UF_FIO',
                [
                    'title' => Loc::getMessage('SHOP_EMPLOYEES_ENTITY_UF_FIO_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_USER_ID',
                [
                    'title' => Loc::getMessage('SHOP_EMPLOYEES_ENTITY_UF_USER_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_USER_POSITION',
                [
                    'title' => Loc::getMessage('SHOP_EMPLOYEES_ENTITY_UF_USER_POSITION_FIELD'),
                ]
            ),
        ];
    }
}