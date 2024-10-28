import {defineStore} from 'ui.vue3.pinia';
export const shopStore = defineStore('shop', {
    state: () => ({
        shops: [
            {
                "ID": "",
                "UF_ADDRESS": "",
                "UF_ADDRESS_SUM": "",
                "UF_NUMBER": "",
                "UF_CITY":  "",
                "UF_REGION":  "",
                "UF_TERRITORY":  "",
                "UF_PEOPLE":  "",
                "UF_PHONE": "",
                "UF_ENTITY": "",
                "UF_EMAIL": "",
                "UF_ADMIN_ID": "",
                "UF_STATUS": "",
                "UF_LATITUDE": "",
                "UF_LONGITUDE": "",
                "UF_COMMENT": "",
                "UF_RU_ID": "",
                "UF_ZRU_ID": "",
                "UF_SUPERVISOR": "",
                "UF_VERTEX_COORDS": "",
                "ADDRESS_PROGRAM": []
            }
        ],
        filters: {
            "UF_ZRU": [],
            "UF_SUPERVISOR": [],
            "UF_CITY": [],
            "UF_REGION": [],
            "UF_TERRITORY": [],
        }
    }),
});