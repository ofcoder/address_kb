<?
namespace kb\Model;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;

/**
 * Class WorkCitiesTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_CITY text optional
 * </ul>
 *
 * @package Bitrix\Kb
 **/

class WorkCitiesTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kb_work_cities';
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
                    'title' => Loc::getMessage('WORK_CITIES_ENTITY_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_CITY',
                [
                    'title' => Loc::getMessage('WORK_CITIES_ENTITY_UF_CITY_FIELD'),
                ]
            ),
        ];
    }
}