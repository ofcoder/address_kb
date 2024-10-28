<?php
$_SERVER["DOCUMENT_ROOT"] = '/home/bitrix/www';

define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", 'Y');
define("NO_AGENT_STATISTIC",'Y');
define("NO_AGENT_CHECK", true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_before.php');

$conn = sqlsrv_connect(SQL_SKUD_SERVER, SQL_SKUD_INFO);

if ($conn) {
    $selectData = "SELECT a.rec_id, a.dt, a.mode, b.bitrixid FROM logs a JOIN listuser b ON a.orionid = b.orionid ORDER BY a.rec_id";
    $getTimeman = sqlsrv_query($conn, $selectData);
    $dateToday = new DateTime('0:00:00');

    if (sqlsrv_has_rows($getTimeman)) {
        global $USER;
        $USER->Authorize(1, false, false);

        while ($row = sqlsrv_fetch_array($getTimeman)) {
            if ($row['dt'] > $dateToday) {
                try {
                    AgentsHelpers::setTimeman($row['bitrixid'], $row['mode'], $row['dt']->getTimeStamp());

                    $delete = 'DELETE FROM logs WHERE rec_id = ' . $row['rec_id'];
                    sqlsrv_query($conn, $delete);
                } catch (\Exception $e) {
                    \Bitrix\Main\Diag\Debug::dumpToFile($row, 'row', '2_timeman_errors.txt');
                    \Bitrix\Main\Diag\Debug::dumpToFile($e, $row['bitrixid'], '2_timeman_errors.txt');
                }
            }
        }
        unset($USER);
    }

    /* Free the statement and connection resources. */
    sqlsrv_free_stmt($getTimeman);
    sqlsrv_close($conn);
} else {
    AddMessage2Log("Не удалось подключиться к БД");
}