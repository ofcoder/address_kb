<?
namespace kb\Model;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;

/**
 * Class ShopsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_ADDRESS text optional
 * <li> UF_NUMBER text optional
 * <li> UF_CITY text optional
 * <li> UF_REGION text optional
 * <li> UF_TERRITORY text optional
 * <li> UF_PEOPLE text optional
 * <li> UF_PHONE text optional
 * <li> UF_ENTITY text optional
 * <li> UF_EMAIL text optional
 * <li> UF_ADMIN_ID text optional
 * <li> UF_STATUS text optional
 * <li> UF_COMMENT text optional
 * <li> UF_RU_ID text optional
 * <li> UF_ZRU_ID text optional
 * <li> UF_SUPERVISOR text optional
 * <li> UF_DENY_REASON text optional
 * <li> UF_ADDRESS_SUM text optional
 * <li> UF_VERTEX_COORDS text optional
 * <li> UF_USER_ID_ST int optional
 * <li> UF_USER_ID_ENGINEER int optional
 * <li> UF_USER_ID_DEPUTY_HEAD_ENGINEER int optional
 * <li> UF_USER_ID_HEAD_ENGINEER int optional
 * <li> UF_LATITUDE text optional
 * <li> UF_LONGITUDE text optional
 * </ul>
 *
 * @package Bitrix\Kb
 **/

class ShopsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kb_shops';
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
                    'title' => Loc::getMessage('SHOPS_ENTITY_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_ADDRESS',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_ADDRESS_FIELD'),
                ]
            ),
            new TextField(
                'UF_NUMBER',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_NUMBER_FIELD'),
                ]
            ),
            new TextField(
                'UF_CITY',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_CITY_FIELD'),
                ]
            ),
            new TextField(
                'UF_REGION',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_REGION_FIELD'),
                ]
            ),
            new TextField(
                'UF_TERRITORY',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_TERRITORY_FIELD'),
                ]
            ),
            new TextField(
                'UF_PEOPLE',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_PEOPLE_FIELD'),
                ]
            ),
            new TextField(
                'UF_PHONE',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_PHONE_FIELD'),
                ]
            ),
            new TextField(
                'UF_ENTITY',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_ENTITY_FIELD'),
                ]
            ),
            new TextField(
                'UF_EMAIL',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_EMAIL_FIELD'),
                ]
            ),
            new TextField(
                'UF_ADMIN_ID',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_ADMIN_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_STATUS',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_STATUS_FIELD'),
                ]
            ),
            new TextField(
                'UF_COMMENT',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_COMMENT_FIELD'),
                ]
            ),
            new TextField(
                'UF_RU_ID',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_RU_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_ZRU_ID',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_ZRU_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_SUPERVISOR',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_SUPERVISOR_FIELD'),
                ]
            ),
            new TextField(
                'UF_DENY_REASON',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_DENY_REASON_FIELD'),
                ]
            ),
            new TextField(
                'UF_ADDRESS_SUM',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_ADDRESS_SUM_FIELD'),
                ]
            ),
            new TextField(
                'UF_VERTEX_COORDS',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_VERTEX_COORDS_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_USER_ID_ST',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_USER_ID_ST_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_USER_ID_ENGINEER',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_USER_ID_ENGINEER_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_USER_ID_DEPUTY_HEAD_ENGINEER',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_USER_ID_DEPUTY_HEAD_ENGINEER_FIELD'),
                ]
            ),
            new IntegerField(
                'UF_USER_ID_HEAD_ENGINEER',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_USER_ID_HEAD_ENGINEER_FIELD'),
                ]
            ),
            new TextField(
                'UF_LATITUDE',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_LATITUDE_FIELD'),
                ]
            ),
            new TextField(
                'UF_LONGITUDE',
                [
                    'title' => Loc::getMessage('SHOPS_ENTITY_UF_LONGITUDE_FIELD'),
                ]
            ),
        ];
    }
}