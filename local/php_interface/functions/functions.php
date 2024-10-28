<?php

function getResponce($domain, $params = '', $auth = [])
{
  $headers = [
    "Accept: application/json",
    'Content-Type: application/json',
    'Content-Length:' . strlen($params),
  ];

  // Определение параметров запроса
  $CurlOptions = [
    CURLOPT_URL => $domain,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_FOLLOWLOCATION => 1,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_COOKIEFILE => '',
  ];

  if (!empty($params)) {
    $CurlOptions[CURLOPT_POST] = 1;
    $CurlOptions[CURLOPT_POSTFIELDS] = $params;
  } else {
    $CurlOptions[CURLOPT_HTTPGET] = 1;
  }

  if (!empty($auth)) {
    $username = $auth['user'];
    $password = $auth['password'];
    $CurlOptions[CURLOPT_USERPWD] = "$username:$password";
  }

  // Инициализация запроса
  $ch = curl_init();
  // установка параметров сеанса
  curl_setopt_array($ch, $CurlOptions);
  // Выполнение запроса, в переменной хранится ответ от сервера
  return curl_exec($ch);
}

function translite($source = false, $toLat = true)
{
  if ($source) {
    $rus = [
      'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
      'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
    ];
    $lat = [
      'A', 'B', 'V', 'G', 'D', 'E', 'Yo', 'Zh', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Shch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya',
      'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shch', 'y', 'y', 'y', 'e', 'yu', 'ya',
    ];
    if ($toLat) {
      $result = str_replace($rus, $lat, $source);
    } else {
      $result = str_replace($lat, $rus, $source);
    }
    return $result;
  }
}

function ftpRecursiveFileListing($ftpConnection, $path)
{
  static $allFiles = [];
  $contents = ftp_nlist($ftpConnection, $path);

  foreach ($contents as $currentFile) {
    if (!str_contains($currentFile, '.')) {
      ftpRecursiveFileListing($ftpConnection, $currentFile);
    } else {
      $allFiles[] = $currentFile;
    }
  }
  return $allFiles;
}
function updateSettings($field, $value)
{
  if (\Bitrix\Main\Loader::includeModule("askaron.settings")) {
    $arUpdateFields = [$field => $value];

    $obSettings = new CAskaronSettings;
    $res = $obSettings->Update($arUpdateFields);
    if (!$res) {
      echo $obSettings->LAST_ERROR;
    }
  }
}

/**
 * @throws Exception
 */
function random_str(int $length = 64, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
{
  if ($length < 1) {
    throw new \RangeException("Length must be a positive integer");
  }
  $pieces = [];
  $max = mb_strlen($keyspace, '8bit') - 1;
  for ($i = 0; $i < $length; ++$i) {
    $pieces[] = $keyspace[random_int(0, $max)];
  }
  return implode('', $pieces);
}

function db($a)
{
  echo '<pre>';
  var_export($a);
  echo '</pre>';
}

function my_dump($var)
{
  global $USER;
  if( ($USER->isAdmin() == 1) || ($REQUEST["dump"] === "Y"))
  {
    ?>
      <font style="text-align: left; font-size: 10px"><pre><?var_dump($var)?></pre></font><br>
    <?
  }

}
function log2file($var, $fn = null, $folder=__DIR__."/Log/")
{
  if(!file_exists($folder))
  {
    mkdir($folder, 0777, true);
  }
  $error = "";
  $fn = $fn ? "-" . str_replace(['\\', '/', ' '], '', $fn) : "";
  $fp = fopen($folder . date("Y") . "-log2file{$fn}.log", "a");
  $test = fwrite($fp, date("Y-m-d H:i:s") . ";" . print_r($var,true) . "\r\n");

  if (!$test) {
    $error = "Ошибка при записи в файл " . $folder . date("Y") . "-log2file{$fn}.log";

  }
  fclose($fp);

  return $error;
}
/**
 * Удалить BOM из строки
 * @param string $str - исходная строка
 * @return string $str - строка без BOM
 */
function removeBOM($str="") {
  if(substr($str, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
    $str = substr($str, 3);
  }
  return $str;
}