import {defineStore} from 'ui.vue3.pinia';
export const historyStore = defineStore('history', {
    state: () => ({
        histories: [
            {
                "ID": "",
                "UF_SHOP_NUMBER": "",
                "UF_DATE": "",
                "UF_USER_FULLNAME": "",
                "UF_STATUS_AFTER": "",
                "UF_STATUS_BEFORE": "",
            }
        ],
    }),
});