import './css/super.css';
import {AddressList} from "./addresslist";
import {Filter} from "./filter";
import {shopStore} from "../stores/shop";

export const Super = {
    emits: ['onGetAjax', 'update:shops'],
    props: {
        marketer: {default: false},
        shops: {default: []}
    },
    components: {
        AddressList, Filter
    },
    data() {
        return {
            filterShopNumber: '',
            filterStatus: '',
            filterCity: '',
            filterTerritory: '',
            filterRegion: '',
            filterZru: '',
            filterSupervisor: '',
            clickId: 0
        }
    },
    methods: {
        getFilter(shops) {
            return shops.filter((item)=> {
                return (String(item.UF_NUMBER).includes(this.filterShopNumber) ||
                    String(item.UF_ADDRESS).includes(this.filterShopNumber)) &&
                    String(item.UF_STATUS).includes(this.filterStatus) &&
                    String(item.UF_CITY).includes(this.filterCity) &&
                    String(item.UF_TERRITORY).includes(this.filterTerritory) &&
                    String(item.UF_REGION).includes(this.filterRegion) &&
                    String(item.UF_ZRU_ID).includes(this.filterZru) &&
                    String(item.UF_SUPERVISOR).includes(this.filterSupervisor);
            })
        },
        getFilteredFlatsCount(shops) {
            return shops.reduce((acc, item) => acc + +item.UF_ADDRESS_SUM, 0);
        },
        getShopsMarketer() {
            if (this.marketer) {
                let body = document.querySelector('.address-hover');
                body.classList.toggle('ajax-processed');
                BX.ready(() => {
                    BX.ajax.runComponentAction('kb:address.program', 'getShops', {
                        mode: 'class',
                        data: {
                            shopsFilter: {
                                '%UF_NUMBER': this.filterShopNumber,
                                '%UF_ADDRESS': this.filterShopNumber,
                                'UF_STATUS': this.filterStatus,
                                '%UF_CITY': this.filterCity,
                                '%UF_TERRITORY': this.filterTerritory,
                                '%UF_REGION': this.filterRegion,
                                '%UF_ZRU_ID': this.filterZru,
                                '%UF_SUPERVISOR': this.filterSupervisor,
                            },
                            shopsSelect: ['UF_NUMBER', 'UF_ADDRESS', 'UF_STATUS', 'UF_CITY', 'UF_TERRITORY', 'UF_ZRU_ID',
                                'UF_SUPERVISOR', 'UF_PHONE', 'UF_LATITUDE', 'UF_LONGITUDE', 'UF_VERTEX_COORDS', 'UF_EMAIL', 'UF_PEOPLE']
                        },
                    }).then((response) => {
                        shopStore().shops = response.data['SHOPS'];
                        shopStore().histories = response.data['HISTORIES'];
                        if (response.data['SHOPS'].length !== 0) {
                            this.$emit('onGetAjax');
                        }
                        body.classList.toggle('ajax-processed');
                    }, function (response) {
                        console.log(response);
                    });
                });
            }
        }
    },
    watch: {
        filterShopNumber() {
            this.getShopsMarketer();
        },
        filterStatus() {
            this.getShopsMarketer();
        },
        filterCity() {
            this.getShopsMarketer();
        },
        filterTerritory() {
            this.getShopsMarketer();
        },
        filterRegion() {
            this.getShopsMarketer();
        },
        filterZru() {
            this.getShopsMarketer();
        },
        filterSupervisor() {
            this.getShopsMarketer();
        },
    },
    // language=Vue
    template: `
        <div class="super-container">
            <div class="shop-header">
                <div class="shop-count">{{ $Bitrix.Loc.getMessage('SHOPS_COUNT', {'#COUNT#': Number(getFilter(shops).length).toLocaleString('ru-RU')}) }}</div>
                <div class="shop-flats">{{ $Bitrix.Loc.getMessage('FLATS_COUNT', {'#COUNT#': Number(getFilteredFlatsCount(getFilter(shops))).toLocaleString('ru-RU')}) }}</div>
            </div>
            <div class="shop-list-container">
                <div v-if="getFilter(shops).length !== 0" class="shop-list">
                    <template v-for="(value, index) in getFilter(shops)" :key="index">
                        <AddressList v-model:shopDetail="value" :marketer="marketer" v-model:isClick="clickId"/>
                    </template>
                </div>
                <div v-else class="shop-not-found">{{ $Bitrix.Loc.getMessage('NOT_FOUND') }}</div>
                <Filter :isClick="marketer" v-model:shopNumber="filterShopNumber" v-model:status="filterStatus" v-model:region="filterRegion" 
                        v-model:territory="filterTerritory" v-model:zru="filterZru" v-model:supervisor="filterSupervisor" v-model:city="filterCity"
                />
            </div>
        </div>
    `,
}