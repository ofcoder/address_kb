import './css/addresslist.css';
import {AddressDetail} from "./addressdetail";
export const AddressList = {
    components: {
        AddressDetail
    },
    props: {
        shopDetail: {default: {}},
        isClick: {default: 0},
        marketer: {default: false},
    },
    emits: ['update:shopDetail', 'update:isClick'],
    computed: {
        statusImg() {
            let url = this.$Bitrix.Data.get('site_path');
            switch (this.shopDetail.UF_STATUS) {
                case '2':
                    url += '/status-wait.svg';
                    break;
                case '3':
                    url += '/status-done.svg';
                    break;
                case '4':
                    url += '/status-edit.svg';
                    break;
                case '1':
                default:
                    url += '/status-new.svg';
                    break;
            }
            return url;
        },
        statusName() {
            let name = 'STATUS_';
            switch (this.shopDetail.UF_STATUS) {
                case '1':
                case '2':
                case '3':
                case '4':
                    name += this.shopDetail.UF_STATUS;
                    break;
                default:
                    name += '1';
                    break;
            }
            return name;
        },
        isClickInput: {
            get() {
                return this.isClick;
            },
            set(newValue){
                this.$emit('update:isClick', newValue);
            }
        },
        isClickBool() {
            return this.isClickInput === this.shopDetail.ID;
        }
    },
    methods: {
        setClick() {
            if (this.isClickBool) {
                this.isClickInput = 0;
            } else {
                this.isClickInput = this.shopDetail.ID;
                map.setCenter([this.shopDetail.UF_LATITUDE, this.shopDetail.UF_LONGITUDE]);
            }
        }
    },
    // language=Vue
    template: `
        <div class="address-shop-list" @click="setClick()" :class="[{isActive: isClickBool}, isClickBool ? 'status-' + shopDetail.UF_STATUS : '']">
            <div class="shop-status">
                <img :src="statusImg" alt="statusImg" :class="statusName">
                <div class="shop-number">
                    <div>{{ this.shopDetail.UF_NUMBER }}</div>
                    <div v-if="isClickBool">{{'- ' +  $Bitrix.Loc.getMessage(statusName) }}</div>
                </div>
                <img v-if="isClickBool" :src="this.$Bitrix.Data.get('site_path') + '/toggle.svg'" alt="toggleImg">
            </div>
            <div class="totalFlats" v-if="shopDetail.UF_ADDRESS_SUM > 0">
                {{ $Bitrix.Loc.getMessage('FLATS_COUNT', {'#COUNT#': Number(this.shopDetail.UF_ADDRESS_SUM).toLocaleString('ru-RU')}) }}
            </div>
            <div class="totalFlatsZero" v-else>{{ $Bitrix.Loc.getMessage('FLATS_COUNT_ZERO') }}</div>
        </div>
        <div v-if="isClickBool" class="shop-ap" :class="[isClickBool ? 'shop-status-' + shopDetail.UF_STATUS : '']">
            <AddressDetail v-model:detailShop="shopDetail" v-model:isClick="isClickInput" :isAdmin="false" :marketer="marketer"/>
        </div>
    `
}