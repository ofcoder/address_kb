<?
namespace kb\Model;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;

/**
 * Class ShopsApHistoryTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_SHOP_NUMBER text optional
 * <li> UF_DATE datetime optional
 * <li> UF_USER_FULLNAME text optional
 * <li> UF_STATUS_AFTER text optional
 * <li> UF_STATUS_BEFORE text optional
 * </ul>
 *
 * @package Bitrix\Kb
 **/

class ShopsApHistoryTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kb_shops_ap_history';
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
                    'title' => Loc::getMessage('SHOPS_AP_HISTORY_ENTITY_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_SHOP_NUMBER',
                [
                    'title' => Loc::getMessage('SHOPS_AP_HISTORY_ENTITY_UF_SHOP_NUMBER_FIELD'),
                ]
            ),
            new DatetimeField(
                'UF_DATE',
                [
                    'title' => Loc::getMessage('SHOPS_AP_HISTORY_ENTITY_UF_DATE_FIELD'),
                ]
            ),
            new TextField(
                'UF_USER_FULLNAME',
                [
                    'title' => Loc::getMessage('SHOPS_AP_HISTORY_ENTITY_UF_USER_FULLNAME_FIELD'),
                ]
            ),
            new TextField(
                'UF_STATUS_AFTER',
                [
                    'title' => Loc::getMessage('SHOPS_AP_HISTORY_ENTITY_UF_STATUS_AFTER_FIELD'),
                ]
            ),
            new TextField(
                'UF_STATUS_BEFORE',
                [
                    'title' => Loc::getMessage('SHOPS_AP_HISTORY_ENTITY_UF_STATUS_BEFORE_FIELD'),
                ]
            ),
        ];
    }
}