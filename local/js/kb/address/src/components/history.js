import './css/history.css';
import {mapState} from "ui.vue3.pinia";
import {historyStore} from "../stores/history";
export const HistoryApp =
{
    data() {
        return {
            filterShopNumber: '',
        }
    },
    props: {
        mode: { default: 'admin' },
    },
    computed: {
        ...mapState(historyStore, ['histories']),
    },
    methods: {
        getFilter(histories) {
            return histories.filter((item, index)=> {
                return String(item.UF_SHOP_NUMBER).includes(this.filterShopNumber);
            })
        },
        statusImg(statusNumber) {
            let url = this.$Bitrix.Data.get('site_path');
            switch (statusNumber) {
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
        }
    },
    // language=Vue
    template: `
        <div v-if="mode !== 'admin'">
            <div>
                <input class="shopFilter" :placeholder="$Bitrix.Loc.getMessage('FILTERS_SHOP')" v-model="filterShopNumber">
            </div>
            <div class="historyTable">
                <div class="tableRow tableHeaderRow">
                    <div class="tableCell">{{ $Bitrix.Loc.getMessage('DATE') }}</div>
                    <div class="tableCell">{{ $Bitrix.Loc.getMessage('FILTERS_SHOP') }}</div>
                    <div class="tableCell">{{ $Bitrix.Loc.getMessage('USER') }}</div>
                    <div class="tableCell">{{ $Bitrix.Loc.getMessage('BEFORE') }}</div>
                    <div class="tableCell">{{ $Bitrix.Loc.getMessage('AFTER') }}</div>
                </div>
                <div class="tableRow" v-for="(value, index) in getFilter(this.histories)" :key="index">
                    <div class="tableCell">{{ value.UF_DATE }}</div>
                    <div class="tableCell">{{ value.UF_SHOP_NUMBER }}</div>
                    <div class="tableCell">{{ value.UF_USER_FULLNAME }}</div>
                    <div class="tableCell">
                        <img :src="statusImg(value.UF_STATUS_BEFORE)" alt="statusIcon"/>
                    </div>
                    <div class="tableCell">
                        <img :src="statusImg(value.UF_STATUS_AFTER)" alt="statusIcon"/>
                    </div>
                </div>
            </div>
        </div>
    `
};