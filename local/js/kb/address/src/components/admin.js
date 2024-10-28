import './css/admin.css';
import {AddressDetail} from "./addressdetail";
export const Admin = {
    components: {
        AddressDetail
    },
    props: {
        shopDetail: {default: []}
    },
    emits: ['update:shopDetail'],
    // language=Vue
    template: `
        <div class="admin-container">
            <template v-if="shopDetail.length !== 0">
                <div class="shop-name">{{ $Bitrix.Loc.getMessage('SHOP', {'#NUMBER#': this.shopDetail.UF_NUMBER}) }}</div>
                <AddressDetail v-model:detailShop="shopDetail"/>
            </template>
            <template v-else>
                {{ $Bitrix.Loc.getMessage('ERROR') }}
            </template>
        </div>
    `
}