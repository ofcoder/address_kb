import './css/addressdetail.css';
import {AddressItem} from "./addressitem";
import {mapState} from "ui.vue3.pinia";
import {historyStore} from "../stores/history";
export const AddressDetail = {
    data() {
        return {
            isEdit: false,
            error: false,
            errorText: ''
        }
    },
    props: {
        detailShop: {default: {}},
        isAdmin: {default: true},
        isClick: {default: true},
        marketer: {default: false}
    },
    emits: [
        'update:detailShop',
        'update:isClick'
    ],
    components: {
        AddressItem
    },
    computed: {
        ...mapState(historyStore, ['histories']),
        detailShopAdmin: {
            get: function(){
                return this.detailShop;
            },
            set: function(newValue){
                this.$emit('update:detailShop', newValue)
            }
        },
        isClickValue: {
            get: function(){
                return this.isClick;
            },
            set: function(newValue){
                this.$emit('update:isClick', newValue)
            }
        },
        totalFlats() {
            return this.detailShopAdmin.ADDRESS_PROGRAM.reduce((acc, item) => acc + +item.UF_FLAT_COUNT, 0);
        },
    },
    watch: {
        detailShopAdmin: {
            deep: true,
            handler() {
                if (this.detailShopAdmin.UF_ADDRESS_SUM !== 0 && this.detailShopAdmin.UF_ADDRESS_SUM !== this.totalFlats) {
                    this.detailShopAdmin.UF_ADDRESS_SUM = this.totalFlats;
                }
                this.saveAddresses();
            }
        },
    },
    methods: {
        addNewAddress(){
            if (this.detailShopAdmin.UF_STATUS !== '2') {
                this.detailShopAdmin.ADDRESS_PROGRAM.push({
                    'ID': '0',
                    'UF_STREET': '',
                    'UF_HOUSE': '',
                    'UF_FLAT_COUNT': '',
                    'UF_NUMBER': this.detailShopAdmin.ID
                })
            }
        },
        deleteAddress(key) {
            this.detailShopAdmin.ADDRESS_PROGRAM.splice(key,1);
        },
        saveAddresses() {
            BX.ready(() => {
                BX.ajax.runComponentAction('kb:address.program', 'addAddressProgram', {
                    mode: 'class',
                    data: {
                        shopNumber: this.detailShopAdmin.ID,
                        addresses: this.detailShopAdmin.ADDRESS_PROGRAM,
                        comment: this.detailShopAdmin.UF_COMMENT,
                        totalFlats: this.detailShopAdmin.UF_ADDRESS_SUM
                    },
                }).then(function (response) {
                }, function (response) {
                    console.log(response);
                });
            });
        },
        changeStatus(element) {
            if (confirm(this.$Bitrix.Loc.getMessage('CONFIRM'))) {
                this.detailShopAdmin.UF_STATUS = element;
                let _this = this;
                BX.ready(() => {
                    BX.ajax.runComponentAction('kb:address.program', 'setStatus', {
                        mode: 'class',
                        data: {
                            shopId: this.detailShopAdmin.ID,
                            status: this.detailShopAdmin.UF_STATUS
                        },
                    }).then(function (response) {
                        if (response.data.STATUS === 'OK') {
                            _this.histories.push(response.data.FIELDS);
                        } else {
                            _this.error = true;
                            _this.errorText = response.data.FIELDS.TEXT;
                        }
                    }, function (response) {
                        console.log(response);
                    });
                });
            }
        },
        close() {
            if (this.isEdit) {
                this.isClickValue = 0;
            }
            this.isEdit = !this.isEdit;
        }
    },
    // language=Vue
    template: `
      <div class="shop-detail">
        <template v-if="!error">
          <div class="address-shop">{{ this.detailShopAdmin.UF_ADDRESS }}</div>
          <div v-if="totalFlats > 0 || isEdit || isAdmin" class="address-program">
            <div class="address-header">
              <div class="address-first">{{ $Bitrix.Loc.getMessage('ADDRESS_STREET') }}</div>
              <div class="address-second">{{ $Bitrix.Loc.getMessage('ADDRESS_HOUSE') }}</div>
              <div class="address-third">{{ $Bitrix.Loc.getMessage('ADDRESS_COUNT_OF_FLATS') }}</div>
              <div v-if="detailShopAdmin.UF_STATUS === '1' && (isEdit || isAdmin)" class="delete-item-col"></div>
            </div>
            <div class="address-body">
              <template v-for="(value, index) in detailShopAdmin.ADDRESS_PROGRAM" :key="index">
                <AddressItem v-model:isEdit="isEdit" :isAdmin="isAdmin" v-model:status="detailShopAdmin.UF_STATUS"
                             v-model:street="value.UF_STREET" v-model:house="value.UF_HOUSE"
                             v-model:flatCount="value.UF_FLAT_COUNT" @remove="deleteAddress(index)"/>
              </template>
            </div>
            <div v-if="isAdmin || isEdit" class="address-footer">
              <button class="add-address" @click="addNewAddress"
                      :disabled="detailShopAdmin.UF_STATUS  !== '1' || !isEdit && !isAdmin">
                <img :src="this.$Bitrix.Data.get('site_path') + '/plus.svg'" alt="plusImg" aria-label="plusImg"/>
                {{ $Bitrix.Loc.getMessage('ADDRESS_ADD') }}
              </button>
              <div class="result-count">
                <div>{{ $Bitrix.Loc.getMessage('ADDRESS_RESULT_COUNT_OF_FLATS')}} &nbsp;</div>
                <div class="count">{{totalFlats}}</div>
              </div>
            </div>
          </div>
          <div v-if="detailShopAdmin.UF_COMMENT !== '' || isEdit || isAdmin" class="address-comments">
            <textarea
                :readonly="!isAdmin && (detailShopAdmin.UF_STATUS === '2' || !isEdit) || detailShopAdmin.UF_STATUS !== '1' && isAdmin"
                :placeholder="this.$Bitrix.Loc.getMessage('ADDRESS_COMMENT')" class="address-comment"
                v-model="detailShopAdmin.UF_COMMENT">{{ detailShopAdmin.UF_COMMENT }}</textarea>
          </div>
          <div class="ap-buttons">
            <button v-if="detailShopAdmin.UF_STATUS === '1' && !isAdmin" class="address-save-edit" @click="close">
              <img :src="$Bitrix.Data.get('site_path') + '/settings.svg'" alt="editImg">
              {{ isEdit ? $Bitrix.Loc.getMessage('SAVE_CLOSE') : $Bitrix.Loc.getMessage('EDIT') }}
            </button>
            <template v-if="marketer">
              <button v-if="detailShopAdmin.UF_STATUS === '2'" class="address-reject" @click="changeStatus('1')">
                <img :src="$Bitrix.Data.get('site_path') + '/reject.svg'" alt="statusImg">
                {{ $Bitrix.Loc.getMessage('REJECT') }}
              </button>
              <button v-if="detailShopAdmin.UF_STATUS === '2'" class="address-accept" @click="changeStatus('3')">
                <img :src="$Bitrix.Data.get('site_path') + '/approve.svg'" alt="editImg">
                {{ $Bitrix.Loc.getMessage('APPROVE_2') }}
              </button>
              <button v-if="detailShopAdmin.UF_STATUS === '4'" class="address-accept" @click="changeStatus('1')">
                <img :src="$Bitrix.Data.get('site_path') + '/approve.svg'" alt="statusImg">
                {{ $Bitrix.Loc.getMessage('APPROVE') }}
              </button>
              <button v-if="detailShopAdmin.UF_STATUS === '4'" class="address-reject" @click="changeStatus('3')">
                <img :src="$Bitrix.Data.get('site_path') + '/reject.svg'" alt="statusImg">
                {{ $Bitrix.Loc.getMessage('REJECT') }}
              </button>
            </template>
            <template v-else>
              <button v-if="detailShopAdmin.UF_STATUS === '1' && isAdmin" class="address-accept deactivated add">
                {{ $Bitrix.Loc.getMessage('NEW_AP') }}
              </button>
              <button v-if="detailShopAdmin.UF_STATUS === '2'" class="address-accept deactivated">
                <img :src="$Bitrix.Data.get('site_path') + '/status-green.svg'" alt="statusImg">
                {{ $Bitrix.Loc.getMessage('ADDRESS_ACCEPT_SENT') }}
              </button>
              <button v-if="(detailShopAdmin.UF_STATUS === '3' || detailShopAdmin.UF_STATUS === '4') && isAdmin"
                      class="address-accept deactivated done">
                <img :src="$Bitrix.Data.get('site_path') + '/done2.svg'" alt="statusImg">
                {{ $Bitrix.Loc.getMessage('ADDRESS_ACCEPTED') }}
              </button>
              <template v-if="!isAdmin">
                <button v-if="detailShopAdmin.UF_STATUS === '1'"
                        :disabled="detailShopAdmin.ADDRESS_PROGRAM.length === 0" class="address-accept"
                        @click="changeStatus('2')">
                  <img :src="$Bitrix.Data.get('site_path') + '/status.svg'" alt="statusImg">
                  {{ $Bitrix.Loc.getMessage('ADDRESS_ACCEPT') }}
                </button>
                <button v-if="detailShopAdmin.UF_STATUS === '3'" class="address-request-edit"
                        @click="changeStatus('4')">
                  <img :src="$Bitrix.Data.get('site_path') + '/status.svg'" alt="editImg">
                  {{ $Bitrix.Loc.getMessage('EDIT_REQUEST') }}
                </button>
                <button v-if="detailShopAdmin.UF_STATUS === '4'" class="address-request-edit deactivated">
                  {{ $Bitrix.Loc.getMessage('EDIT_REQUEST_SENT') }}
                </button>
              </template>
            </template>
            <img v-if="!isAdmin" :src="$Bitrix.Data.get('site_path') + '/history.svg'" alt="history">
          </div>
        </template>
        <template v-else>
          <div class="error">{{ errorText }}</div>
        </template>
      </div>
    `
}