<?php

namespace Sprint\Migration;


class Version20231003142726 extends Version
{
    protected $description = "";

    protected $moduleVersion = "4.2.4";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $helper->Event()->saveEventType('AP_PASS_RESET', array (
  'LID' => 'ru',
  'EVENT_TYPE' => 'email',
  'NAME' => 'Сброс пароля адресная программа',
  'DESCRIPTION' => '',
  'SORT' => '150',
));
            $helper->Event()->saveEventMessage('AP_PASS_RESET', array (
  'LID' => 
  array (
    0 => 'ap',
  ),
  'ACTIVE' => 'Y',
  'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
  'EMAIL_TO' => '#EMAIL#',
  'SUBJECT' => '#SITE_NAME#: Запрос на смену пароля',
  'MESSAGE' => 'Информационное сообщение сайта #SITE_NAME#
------------------------------------------

Ваш логин: #EMAIL#
Ваш новый пароль: #PASS#

Сообщение сгенерировано автоматически.',
  'BODY_TYPE' => 'text',
  'BCC' => '',
  'REPLY_TO' => '',
  'CC' => '',
  'IN_REPLY_TO' => '',
  'PRIORITY' => '',
  'FIELD1_NAME' => '',
  'FIELD1_VALUE' => '',
  'FIELD2_NAME' => '',
  'FIELD2_VALUE' => '',
  'SITE_TEMPLATE_ID' => '',
  'ADDITIONAL_FIELD' => 
  array (
  ),
  'LANGUAGE_ID' => 'ru',
  'EVENT_TYPE' => '[ AP_PASS_RESET ] Сброс пароля адресная программа',
));
        }

    public function down()
    {
        //your code ...
    }
}
