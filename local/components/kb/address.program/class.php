<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use kb\Model\ShopsTable,
    kb\Model\ShopsApTable,
    kb\Model\ShopsApHistoryTable,
    Bitrix\Main\Engine\ActionFilter;

class AddressProgram extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    private array $shopsNumber = [];
    private array $statusArray = ['12', '21', '23', '34', '43', '41'];

    private array $defaultSelect = ['UF_NUMBER', 'UF_ADDRESS', 'UF_STATUS', 'UF_CITY', 'UF_TERRITORY', 'UF_ZRU_ID',
        'UF_SUPERVISOR', 'UF_PHONE', 'UF_LATITUDE', 'UF_LONGITUDE', 'UF_VERTEX_COORDS', 'UF_EMAIL', 'UF_PEOPLE'];


    public function configureActions()
    {
        return [
            'addAddressProgram' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                ],
            ],
            'setStatus' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                ],
            ],
            'setVertexCoords' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                ],
            ],
            'getShops' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                ],
            ],
        ];
    }

    public function executeComponent()
    {
        $this->getMode();
        $this->setShops();

        if ($this->startResultCache()) {
            $this->includeComponentTemplate();
        }

        return $this->arResult;
    }

    private function setHistories($shopsNumber, $remote = false): array
    {
        $histories = $filter = [];
        if (!empty($shopsNumber)) {
            $filter = ['UF_SHOP_NUMBER' => $shopsNumber];
        }

        if (!empty($this->arResult['SHOPS']) && $this->arResult['MODE'] != 'admin' || $remote) {
            $rsHistory = ShopsApHistoryTable::getList([
                'filter' => $filter,
            ]);

            while ($history = $rsHistory->fetch()) {
                $history['UF_DATE'] = mb_substr($history['UF_DATE']->toString(), 0, 16);
                $histories[] = $history;
            }
        }
        return $histories;
    }

    private function setFilters($shopsNumber, $remote = false): array
    {
        $filters = $filter = [];
        if (!empty($shopsNumber)) {
            $filter = ['UF_NUMBER' => $shopsNumber];
        }

        if ($this->arResult['MODE'] != 'admin' || $remote) {
            $filters['UF_CITY'] = $this->getFilterByField('UF_CITY', $filter);
            $filters['UF_REGION'] = $this->getFilterByField('UF_REGION', $filter);
            $filters['UF_TERRITORY'] = $this->getFilterByField('UF_TERRITORY', $filter);
            $filters['UF_ZRU'] = $this->getAllUsersByField('UF_ZRU_ID', $filter);
            $filters['UF_SUPERVISOR'] = $this->getAllUsersByField('UF_SUPERVISOR', $filter);
        }
        return $filters;
    }

    private function getMode(): void
    {
        $mode = 'deny';
        if (CSite::InGroup([USER_GROUP_ID_SHOPS_ADMIN])) {
            $mode = 'admin';
        }
        if (CSite::InGroup([USER_GROUP_ID_SUPERVISOR])) {
            $mode = 'supervisor';
        }
        if (CSite::InGroup([USER_GROUP_ID_ZRU])) {
            $mode = 'zru';
        }
        if (CSite::InGroup([USER_GROUP_ID_RU])) {
            $mode = 'ru';
        }
        if (CSite::InGroup([USER_GROUP_ID_MARKETER])) {
            $mode = 'marketer';
        }

        $this->arResult['MODE'] = $mode;
    }

    private function setShops(): void
    {
        CModule::IncludeModule('highloadblock');

        $xmlId = $this->getUserXmlId();
        $this->arResult['SHOPS'] = match ($this->arResult['MODE']) {
            'admin' => $this->getShops(['UF_ADMIN_ID' => $xmlId], $this->defaultSelect),
            'supervisor' => $this->getShops(['UF_SUPERVISOR' => $xmlId], $this->defaultSelect),
            'ru' => $this->getShops(['UF_RU_ID' => $xmlId], $this->defaultSelect),
            'zru' => $this->getShops(['UF_ZRU_ID' => $xmlId], $this->defaultSelect),
            default => [],
        };
        $this->arResult['HISTORIES'] = $this->setHistories($this->shopsNumber);
        $this->arResult['FILTERS'] = $this->setFilters($this->shopsNumber);
    }

    private function getUserXmlId()
    {
        global $USER;
        $result = \Bitrix\Main\UserTable::getList([
            'filter' => ['ID' => $USER->GetID()],
            'select' => ['ID', 'XML_ID'],
            'limit' => 1,
            'cache' => ['ttl' => 3600],
        ]);

        $userId = '';
        if (!empty($result)) {
            $userId = $result->fetch()['XML_ID'];
        }

        return $userId;
    }

   	private function getShops($shopsFilter, $shopsSelect = ['*']): array
    {
        $shops = $shopsId = [];
        if (!empty($shopsFilter)) {
            $rsShops = ShopsTable::getList([
                'order' => ['UF_NUMBER'],
                'filter' => $shopsFilter,
                'select' => $shopsSelect,
                'cache' => ['ttl' => 3600],
            ]);
            while ($arShop = $rsShops->fetch()) {
                $arShop['UF_COMMENT'] = htmlspecialcharsback($arShop['UF_COMMENT']);
                $arShop['UF_VERTEX_COORDS'] = json_decode($arShop['UF_VERTEX_COORDS']);
                $arShop['ADDRESS_PROGRAM'] = [];
				$arShop['ID'] = $arShop['UF_NUMBER'];				
                $shops[] = $arShop;
                $shopsId[] = $arShop['UF_NUMBER'];
            }
			$this->shopsNumber = $shopsId;
            $rsAddress = ShopsApTable::getList([			
                'filter' => [
                    'UF_NUMBER' => $shopsId,
                ],			
                'cache' => ['ttl' => 3600],
            ]);
			
            while ($address = $rsAddress->fetch()) {
                $shops[array_search($address['UF_NUMBER'], $shopsId)]['ADDRESS_PROGRAM'][] = $address;			              
            }
            usort($shops, function($a, $b) {
                return $a['UF_NUMBER'] > $b['UF_NUMBER'];
            });		
        }
        return $shops;
    }

    private function getFilterByField($field = '', $extraFilters = []): array
    {
        $filter = ['!' . $field => false];
        if (!empty($extraFilters)) {
            foreach ($extraFilters as $key => $extraFilter) {
                $filter[$key] = $extraFilter;
            }
        }

        $rsShops = ShopsTable::getList([
            'order' => [$field],
            'select' => [$field],
            'filter' => $filter,
            'group' => [$field],
            'cache' => [
                'ttl' => 86400,
                'cache_joins' => true,
            ],
        ]);

        $filter = [];
        while ($arShop = $rsShops->fetch()) {
            $filter[] = $arShop[$field];
        }
        return $filter;
    }

    private function getAllUsersByField($field, $filter): array
    {
        $usersFilter['GROUP_ID'] = USER_GROUP_ID_ADDRESS_PROGRAM;
        $usersFilter['USER.ACTIVE'] = 'Y';
        if ($this->arResult['MODE'] != 'marketer') {
            $usersFilter['USER.XML_ID'] = $this->getFilterByField($field, $filter);
        } else {
            $usersFilter['USER.XML_ID'] = $this->getFilterByField($field);
        }

        $arUser = \Bitrix\Main\UserGroupTable::getList([
            'order' => ['LAST_NAME' => 'ASC', 'NAME' => 'ASC', 'SECOND_NAME' => 'ASC'],
            'filter' => $usersFilter,
            'select' => [
                'LAST_NAME' => 'USER.LAST_NAME',
                'NAME' => 'USER.NAME',
                'SECOND_NAME' => 'USER.SECOND_NAME',
                'XML_ID' => 'USER.XML_ID',
            ],
            'cache' => [
                'ttl' => 3600,
                'cache_joins' => true,
            ],
        ]);

        $selectList = [];
        while ($user = $arUser->fetch()) {
            $selectList[$user['XML_ID']] = $user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME'];
        }

        return $selectList;
    }

    public function getShopsAction($shopsFilter, $shopsSelect): array
    {
        $shopsAP = [];
        $remote = false;

        if (empty($shopsFilter['%UF_CITY'])) unset($shopsFilter['%UF_CITY']);
        if (empty($shopsFilter['%UF_NUMBER'])) unset($shopsFilter['%UF_NUMBER']);
        if (empty($shopsFilter['%UF_ADDRESS'])) unset($shopsFilter['%UF_ADDRESS']);
        if (empty($shopsFilter['%UF_REGION'])) unset($shopsFilter['%UF_REGION']);
        if (empty($shopsFilter['UF_STATUS'])) unset($shopsFilter['UF_STATUS']);
        if (empty($shopsFilter['%UF_SUPERVISOR'])) unset($shopsFilter['%UF_SUPERVISOR']);
        if (empty($shopsFilter['%UF_TERRITORY'])) unset($shopsFilter['%UF_TERRITORY']);
        if (empty($shopsFilter['%UF_ZRU_ID'])) unset($shopsFilter['%UF_ZRU_ID']);
        if (!empty($shopsFilter['%UF_ADDRESS'])) {
            $numberArray = $addressArray = $shopsFilter;
            $numberArray['%UF_NUMBER'] = $shopsFilter['%UF_NUMBER'];
            $addressArray['%UF_ADDRESS'] = $shopsFilter['%UF_ADDRESS'];
            unset($numberArray['%UF_ADDRESS']);
            unset($addressArray['%UF_NUMBER']);

            $shopsFilter = [
                'LOGIC' => 'OR',
                $numberArray,
                $addressArray,
            ];
            $remote = true;
        } else {
            foreach ($shopsFilter as $shopFilter) {
                if (!empty($shopFilter)) {
                    $remote = true;
                }
            }
        }

        if ($this->StartResultCache(36000)) {
            $shopsAP['SHOPS'] = $this->getShops($shopsFilter, $shopsSelect);
            $shopsAP['HISTORIES'] = $this->setHistories($this->shopsNumber, $remote);
            $shopsAP['FILTERS'] = $this->setFilters($this->shopsNumber, $remote);
        }

        return $shopsAP;
    }

    public function addAddressProgramAction($shopNumber, $addresses, $comment, $totalFlats)
    {
        if (!empty($comment) || !empty($totalFlats)) {
            ShopsTable::update($shopNumber, [
                'UF_COMMENT' => htmlspecialcharsEx($comment),
                'UF_ADDRESS_SUM' => $totalFlats,
            ]);
        }

        $existShopsAp = [];
        $arExist = ShopsApTable::getList([
            'filter' => ['UF_NUMBER' => $shopNumber],
            'select' => ['ID'],
        ]);
        while ($aExistShopsAp = $arExist->fetch()) {
            $existShopsAp[] = $aExistShopsAp['ID'];
        }

        foreach ($addresses as $address) {
            if (in_array($address['ID'], $existShopsAp)) {
                $existShopsAp = array_diff($existShopsAp, [$address['ID']]);
                ShopsApTable::update($address['ID'], $address);
            } else {
                ShopsApTable::add($address);
            }
        }

        if (!empty($existShopsAp)) {
            foreach ($existShopsAp as $deleteShopsAp) {
                ShopsApTable::delete($deleteShopsAp);
            }
        }

        return 'SAVED';
    }

    public function setStatusAction($shopId, $status)
    {
        global $USER;
        $shop = ShopsTable::getList([
            'select' => ['UF_STATUS', 'UF_NUMBER'],
            'filter' => ['ID' => $shopId],
            'cache' => ['ttl' => 3600],
        ])->fetch();

        $returnFields = [
            'STATUS' => 'ERROR',
            'FIELDS' => [
                'TEXT' => 'Обновите страницу, статус магазина изменился',
            ],
        ];
        if (in_array($shop['UF_STATUS'] . $status, $this->statusArray)) {
            ShopsTable::update($shopId, ['UF_STATUS' => $status]);

            $arUser = CUser::GetByID($USER->GetId())->fetch();

            $fullName = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
            if (empty($arUser['LAST_NAME']) && empty($arUser['NAME']) && empty($arUser['SECOND_NAME'])) {
                $fullName = $arUser['LOGIN'];
            }

            $fields = [
                'UF_SHOP_NUMBER' => $shop['UF_NUMBER'],
                'UF_STATUS_BEFORE' => $shop['UF_STATUS'],
                'UF_STATUS_AFTER' => $status,
                'UF_USER_FULLNAME' => $fullName,
                'UF_DATE' => new Bitrix\Main\Type\Datetime(),
            ];
            ShopsApHistoryTable::add($fields);
            $fields['UF_DATE'] = mb_substr($fields['UF_DATE']->toString(), 0, 16);
            $returnFields = [
                'STATUS' => 'OK',
                'FIELDS' => $fields,
            ];
        }

        return $returnFields;
    }

    public function setVertexCoordsAction($shopId, $coords)
    {
        ShopsTable::update($shopId, ['UF_VERTEX_COORDS' => json_encode($coords)]);
        return 'CHANGED';
    }
}