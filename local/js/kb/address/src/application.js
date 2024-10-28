import {BitrixVue} from 'ui.vue3';
import {createPinia} from 'ui.vue3.pinia';
import {Application} from './components/address';
import {HistoryApp} from './components/history';
import {shopStore} from './stores/shop';
import {historyStore} from './stores/history';

export class AddressProgram
{
    #store;
    #addressApp;
    #addressNode;
    #historyApp;
    #historyNode;

    constructor(addressNode, historyNode): void
    {
        this.#store = createPinia();
        this.#addressNode = document.querySelector(addressNode);
        this.#historyNode = document.querySelector(historyNode);
    }

    start(mode, site_path): void
    {
        this.#addressApp = BitrixVue.createApp({
            beforeCreate() {
                this.$bitrix.Data.set('site_path', site_path);
            },
            name: 'Address Application',
            data() {
                return {
                    mode: mode
                }
            },
            components: {
                Application
            },
            // language=Vue
            template: '<Application :mode="this.mode"/>',
        }).use(this.#store).mount(this.#addressNode);

        this.#historyApp = BitrixVue.createApp({
            beforeCreate() {
                this.$bitrix.Data.set('site_path', site_path);
            },
            name: 'History Application',
            data() {
                return {
                    mode: mode
                }
            },
            components: {
                HistoryApp
            },
            // language=Vue
            template: '<HistoryApp :mode="this.mode"/>',
        }).use(this.#store).mount(this.#historyNode);
    }

    getShopStore(): Object
    {
        return shopStore();
    }

    getHistoryStore(): Object
    {
        return historyStore();
    }
}