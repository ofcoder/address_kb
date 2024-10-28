<?php
if ($_REQUEST['submit'] == 'reset' && !empty($_REQUEST['USER_LOGIN'])) {
    $user = CUser::GetByLogin($_REQUEST['USER_LOGIN'])->fetch();
    if (!empty($user['ID'])) {
        $password = rand(10000000, 99999999);

        $update = new CUser();
        $ar = $update->Update($user['ID'], [
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
        ]);
        CEvent::Send("AP_PASS_RESET", SITE_ID, [
            'EMAIL' => $_REQUEST['USER_LOGIN'],
            'PASS' => $password
        ]);
    }
}