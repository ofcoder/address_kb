<?
namespace kb\Model;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;

/**
 * Class ShopsApTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_STREET text optional
 * <li> UF_HOUSE text optional
 * <li> UF_FLAT_COUNT int optional
 * <li> UF_SHOP_NUMBER int optional
 * <li> UF_NUMBER int optional
 * </ul>
 *
 * @package Bitrix\Kb
 **/

class ShopsApTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kb_shops_ap';
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
                    'title' => Loc::getMessage('SHOPS_AP_ENTITY_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_STREET',
                [
                    'title' => Loc::getMessage('SHOPS_AP_ENTITY_UF_STREET_FIELD'),
                ]
            ),
            new TextField(
                'UF_HOUSE',
                [
                    'title' => Loc::getMessage('SHOPS_AP_ENTITY_UF_HOUSE_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_FLAT_COUNT',
                [
                    'title' => Loc::getMessage('SHOPS_AP_ENTITY_UF_FLAT_COUNT_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_NUMBER',
                [
                    'title' => Loc::getMessage('SHOPS_AP_ENTITY_UF_NUMBER_FIELD'),
                ]
            ),
        ];
    }
}