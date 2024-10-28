<?
namespace kb\Model;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;

/**
 * Class TelegramChatTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_LAST_MESSAGE text optional
 * <li> UF_TELEGRAM_USERNAME text optional
 * <li> UF_TELEGRAM_NAME text optional
 * <li> UF_TELEGRAM_LAST_NAME text optional
 * <li> UF_TELEGRAM_CHAT_ID text optional
 * <li> UF_USER_ID text optional
 * <li> UF_CHAT_ID text optional
 * <li> UF_IS_OPENED text optional
 * <li> UF_IS_TEST text optional
 * </ul>
 *
 * @package Bitrix\Kb
 **/

class TelegramChatTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_kb_telegram_chat';
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
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_LAST_MESSAGE',
                [
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_UF_LAST_MESSAGE_FIELD'),
                ]
            ),
            new TextField(
                'UF_TELEGRAM_USERNAME',
                [
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_UF_TELEGRAM_USERNAME_FIELD'),
                ]
            ),
            new TextField(
                'UF_TELEGRAM_NAME',
                [
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_UF_TELEGRAM_NAME_FIELD'),
                ]
            ),
            new TextField(
                'UF_TELEGRAM_LAST_NAME',
                [
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_UF_TELEGRAM_LAST_NAME_FIELD'),
                ]
            ),
            new TextField(
                'UF_TELEGRAM_CHAT_ID',
                [
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_UF_TELEGRAM_CHAT_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_USER_ID',
                [
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_UF_USER_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_CHAT_ID',
                [
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_UF_CHAT_ID_FIELD'),
                ]
            ),
            new TextField(
                'UF_IS_OPENED',
                [
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_UF_IS_OPENED_FIELD'),
                ]
            ),
            new TextField(
                'UF_IS_TEST',
                [
                    'title' => Loc::getMessage('TELEGRAM_CHAT_ENTITY_UF_IS_TEST_FIELD'),
                ]
            ),
        ];
    }
}