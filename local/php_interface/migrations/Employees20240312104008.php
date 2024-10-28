<?php

namespace Sprint\Migration;


class Employees20240312104008 extends Version
{
    protected $description = "";

    protected $moduleVersion = "4.6.1";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $hlblockId = $helper->Hlblock()->saveHlblock(array (
            'NAME' => 'ShopEmployees',
            'TABLE_NAME' => 'b_kb_shop_employees',
            'LANG' =>
                array (
                    'ru' =>
                        array (
                            'NAME' => 'Работники магазинов',
                        ),
                    'en' =>
                        array (
                            'NAME' => 'Shop employees',
                        ),
                ),
        ));
        $helper->Hlblock()->saveField($hlblockId, array (
            'FIELD_NAME' => 'UF_SHOP_NUMBER',
            'USER_TYPE_ID' => 'hlblock',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'Y',
            'SETTINGS' =>
                array (
                    'DISPLAY' => 'CHECKBOX',
                    'LIST_HEIGHT' => 1,
                    'HLBLOCK_ID' => 'Shops',
                    'HLFIELD_ID' => 'UF_NUMBER',
                    'DEFAULT_VALUE' => 0,
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Номер магазина',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Номер магазина',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Номер магазина',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->Hlblock()->saveField($hlblockId, array (
            'FIELD_NAME' => 'UF_USER_PHONE',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'Y',
            'SETTINGS' =>
                array (
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Телефон пользователя',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Телефон пользователя',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Телефон пользователя',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->Hlblock()->saveField($hlblockId, array (
            'FIELD_NAME' => 'UF_USER_EMAIL',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'Y',
            'SETTINGS' =>
                array (
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Email пользователя',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Email пользователя',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'Email пользователя',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->Hlblock()->saveField($hlblockId, array (
            'FIELD_NAME' => 'UF_FIO',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'S',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'Y',
            'SETTINGS' =>
                array (
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'ФИО пользователя',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'ФИО пользователя',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'ФИО пользователя',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->Hlblock()->saveField($hlblockId, array (
            'FIELD_NAME' => 'UF_USER_ID',
            'USER_TYPE_ID' => 'employee',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'Y',
            'SETTINGS' =>
                array (
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'ID пользователя',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'ID пользователя',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => 'ID пользователя',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
        ));
        $helper->Hlblock()->saveField($hlblockId, array (
            'FIELD_NAME' => 'UF_USER_POSITION',
            'USER_TYPE_ID' => 'enumeration',
            'XML_ID' => '',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'Y',
            'SETTINGS' =>
                array (
                    'DISPLAY' => 'UI',
                    'LIST_HEIGHT' => 1,
                    'CAPTION_NO_VALUE' => '',
                    'SHOW_NO_VALUE' => 'Y',
                ),
            'EDIT_FORM_LABEL' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'LIST_COLUMN_LABEL' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'LIST_FILTER_LABEL' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'ERROR_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'HELP_MESSAGE' =>
                array (
                    'en' => '',
                    'ru' => '',
                ),
            'ENUM_VALUES' =>
                array (
                    0 =>
                        array (
                            'VALUE' => 'Техник',
                            'DEF' => 'N',
                            'SORT' => '500',
                            'XML_ID' => 'st',
                        ),
                    1 =>
                        array (
                            'VALUE' => 'Инженер',
                            'DEF' => 'N',
                            'SORT' => '500',
                            'XML_ID' => 'engineer',
                        ),
                    2 =>
                        array (
                            'VALUE' => 'Заместитель главного инженера',
                            'DEF' => 'N',
                            'SORT' => '500',
                            'XML_ID' => 'deputy_head_engineer',
                        ),
                    3 =>
                        array (
                            'VALUE' => 'Главный инженер',
                            'DEF' => 'N',
                            'SORT' => '500',
                            'XML_ID' => 'head_engineer',
                        ),
                ),
        ));
    }

    public function down()
    {
        //your code ...
    }
}
