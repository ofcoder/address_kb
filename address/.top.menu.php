<?
$aMenuLinks = [
    [
        "Печать карты",
        "javascript:void(0);",
        [],
        ["ICON" => "/images/print.svg", "CLICK_JS" => "window.print()"],
        "CSite::InGroup([23,24,25,36,37])",
    ],
    [
        "Экспорт в Excel",
        "javascript:void(0);",
        [],
        ["ICON" => "/images/excel.svg", "CLICK_JS" => "", "ATTR_ID" => 'exportExcel'],
        "CSite::InGroup([23,24,25,36,37])",
    ],
    [
        "Инструкция",
        "javascript:void(0);",
        [],
        ["ICON" => "/images/history.svg", "CLICK_JS" => "showInstructions()"],
        "CSite::InGroup([23,24,25,36,37])",
    ],
    [
        "История",
        "javascript:void(0);",
        [],
        ["ICON" => "/images/faq.svg", "CLICK_JS" => "showHistory()"],
        "CSite::InGroup([24,25,36,37])",
    ],
];
?>