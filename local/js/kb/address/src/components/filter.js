import './css/filter.css';
import {mapState} from "ui.vue3.pinia";
import {shopStore} from "../stores/shop";
export const Filter = {
    data() {
        return {
            isClickInput: this.isClick,
        }
    },
    props: {
        shopNumber: {default: ''},
        status: {default: ''},
        region: {default: ''},
        territory: {default: ''},
        zru: {default: ''},
        supervisor: {default: ''},
        city: {default: ''},
        isClick: {default: false}
    },
    computed: {
        ...mapState(shopStore, ['filters']),
        shopNumberInput: {
            get: function(){
                return this.shopNumber;
            },
            set: function(newValue){
                this.$emit('update:shopNumber', newValue)
            }
        },
        statusInput: {
            get: function(){
                return this.status;
            },
            set: function(newValue){
                this.$emit('update:status', newValue)
            }
        },
        regionInput: {
            get: function(){
                return this.region;
            },
            set: function(newValue){
                this.$emit('update:region', newValue)
            }
        },
        territoryInput: {
            get: function(){
                return this.territory;
            },
            set: function(newValue){
                this.$emit('update:territory', newValue)
            }
        },
        zruInput: {
            get: function(){
                return this.zru;
            },
            set: function(newValue){
                this.$emit('update:zru', newValue)
            }
        },
        supervisorInput: {
            get: function(){
                return this.supervisor;
            },
            set: function(newValue){
                this.$emit('update:supervisor', newValue)
            }
        },
        cityInput: {
            get: function(){
                return this.city;
            },
            set: function(newValue){
                this.$emit('update:city', newValue)
            }
        },
        optionsStatus(){
            return [
                { text: this.$Bitrix.Loc.getMessage('STATUS_1'), value: '1' },
                { text: this.$Bitrix.Loc.getMessage('STATUS_2'), value: '2' },
                { text: this.$Bitrix.Loc.getMessage('STATUS_3'), value: '3' },
                { text: this.$Bitrix.Loc.getMessage('STATUS_4'), value: '4' },
            ];
        },
    },
    // language=Vue
    template: `
        <div class="filter">
            <div class="filterFields" v-if="isClickInput">
                <input type="search" :placeholder="$Bitrix.Loc.getMessage('FILTERS_SHOP')" v-model="shopNumberInput">
                <select v-model="statusInput" required>
                    <option value="" selected>{{ $Bitrix.Loc.getMessage('FILTERS_STATUS') }}</option>
                    <option v-for="option in optionsStatus" :key="option.id" :value="option.value">{{ option.text }}</option>
                </select>
                <select v-model="zruInput" required>
                    <option value="" selected>{{ $Bitrix.Loc.getMessage('FILTERS_ZRU') }}</option>
                    <option v-for="(option, key) in filters.UF_ZRU" :key="key" :value="key">{{ option }}</option>
                </select>
                <select v-model="supervisorInput" required>
                    <option value="" selected>{{ $Bitrix.Loc.getMessage('FILTERS_SUPERVISOR') }}</option>
                    <option v-for="(option, key) in filters.UF_SUPERVISOR" :key="key" :value="key">{{ option }}</option>
                </select>
                <input type="search" :placeholder="$Bitrix.Loc.getMessage('FILTERS_CITY')" v-model="cityInput" list="cityList">
                <datalist id="cityList">
                    <option v-for="option in filters.UF_CITY" :key="option.id" :value="option"/>
                </datalist>
                <select v-model="regionInput" required>
                    <option value="" selected>{{ $Bitrix.Loc.getMessage('FILTERS_REGION') }}</option>
                    <option v-for="(option, key) in filters.UF_REGION" :key="key" :value="option">{{ option }}</option>
                </select>
                <select v-model="territoryInput" required>
                    <option value="" selected>{{ $Bitrix.Loc.getMessage('FILTERS_TERRITORY') }}</option>
                    <option v-for="(option, key) in filters.UF_TERRITORY" :key="key" :value="option">{{ option }}</option>
                </select>
            </div>
            <div class="filterButton" @click="isClickInput = !isClickInput" :class="{filterActive: isClickInput}">
                <img class="filterImg" :src="$Bitrix.Data.get('site_path') + '/filter.svg'" alt="filter">
                {{ $Bitrix.Loc.getMessage('FILTERS') }} 
                <img class="toggle" v-if="isClickInput" :src="$Bitrix.Data.get('site_path') + '/toggle-up.svg'" alt="toggle-up">
                <img class="toggle" v-else :src="$Bitrix.Data.get('site_path') + '/toggle-down.svg'" alt="toggle-down">
            </div>
        </div>
    `,
}