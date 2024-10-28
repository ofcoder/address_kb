import './css/addressitem.css';
export const AddressItem = {
    methods: {
        removeItem(){
            if (this.status !== '2') {
                this.$emit('remove');
            }
        },
    },
    props: {
        street: { default: '' },
        house: { default: 0 },
        flatCount: { default: 0 },
        status: { default: 1 },
        isEdit: { default: true },
        isAdmin: { default: true }
    },
    emits: [
        'update:flatCount',
        'update:house',
        'update:isEdit',
        'update:status',
        'update:street',
        'remove'
    ],
    computed: {
        streetInput: {
            get: function(){
                return this.street;
            },
            set: function(newValue){
                this.$emit('update:street', newValue)
            }
        },
        houseInput: {
            get: function(){
                return this.house;
            },
            set: function(newValue){
                this.$emit('update:house', newValue)
            }
        },
        flatCountInput: {
            get: function(){
                return this.flatCount;
            },
            set: function(newValue){
                this.$emit('update:flatCount', newValue)
            }
        },
    },
    // language=Vue
    template: `
        <div class="address-item">
            <div class="address-first">
                <input v-model="streetInput" :readonly="status !== '1' || !isEdit && !isAdmin"> 
            </div>
            <div class="address-second">
                <input v-model="houseInput" :readonly="status !== '1' || !isEdit && !isAdmin">
            </div>
            <div class="address-third">
                <input v-model="flatCountInput" :readonly="status !== '1' || !isEdit && !isAdmin" type="number" min="1" step="1">
            </div>
            <div v-if="status === '1' && (isEdit || isAdmin)" class="delete-item" @click="removeItem">
                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOSIgaGVpZ2h0PSIzIiB2aWV3Qm94PSIwIDAgOSAzIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgogICAgPHBhdGggZD0iTTguNjI1IDAuNzVDOC44MTI1IDAuNzUgOSAwLjkzNzUgOSAxLjEyNVYxLjg3NUM5IDIuMDg1OTQgOC44MTI1IDIuMjUgOC42MjUgMi4yNUgwLjM3NUMwLjE2NDA2MiAyLjI1IDAgMi4wODU5NCAwIDEuODc1VjEuMTI1QzAgMC45Mzc1IDAuMTY0MDYyIDAuNzUgMC4zNzUgMC43NUg4LjYyNVoiIGZpbGw9IiM2Qzc1N0QiLz4KPC9zdmc+Cg==" />
            </div>
        </div>
    `
}