import './css/address.css';
import {Admin} from "./admin";
import {Super} from "./super";
import {mapState} from "ui.vue3.pinia";
import {shopStore} from "../stores/shop";
export const Application =
{
    components: {
		Admin, Super
	},
	props: {
		mode: { default: 'admin' },
	},
    data() {
        return {
            /* Текущие координаты области просмотра */
            sw_lat: 0,
            sw_lng: 0,
            ne_lat: 0,
            ne_lng: 0,
            center_lat: 0,
            center_lng: 0,
            /* Текущие координаты области просмотра */
        }
    },    
    computed: {
        ...mapState(shopStore, ['shops']),
        geoLocationsIsEmpty() {
            return this.sw_lat === 0 || this.ne_lat === 0 || this.sw_lng === 0 || this.ne_lng === 0
        },
        pointToDelete() {
            return this.shops.filter(shop => shop.UF_STATUS === '2')
        }
    },
    methods: {

        filteredShops() {
            const {sw_lat, sw_lng, ne_lat, ne_lng} = this;
            const _geoLocationsIsEmpty = this.geoLocationsIsEmpty;
            return this.shops.filter(shop => _geoLocationsIsEmpty || 
            (shop.UF_LATITUDE >= sw_lat &&
             shop.UF_LATITUDE <= ne_lat &&
             shop.UF_LONGITUDE >= sw_lng &&
             shop.UF_LONGITUDE <= ne_lng)
            )
        },


        getParam(name, location) {
            location = location || window.location.hash;
            var res = location.match(new RegExp('[#&]' + name + '=([^&]*)', 'i'));
            return (res && res[1] ? res[1] : false);
        },
        async setLocationHash(event) {
            const params = [
                'type=' + map.getType().split('#')[1],
                'center=' + map.getCenter(),
                'zoom=' + map.getZoom()
            ];
            window.location.hash = params.join('&');
            if (event) {
                const boundsNew = event.get('newBounds');
                if (boundsNew) {
                    const v_sw_lat = boundsNew[0][0];
                    const v_sw_lng = boundsNew[0][1];
                    const v_ne_lat = boundsNew[1][0];
                    const v_ne_lng = boundsNew[1][1];
                    if (  v_sw_lat < this.sw_lat 
                        || v_sw_lng < this.sw_lng 
                        || v_ne_lat > this.ne_lat 
                        || v_ne_lng > this.ne_lng) {

                            //* добавим сразу с удвоенным запасом */
                            const lat_additive = (v_ne_lat - v_sw_lat)/2;
                            const lng_additive = (v_ne_lng - v_sw_lng)/2;

                            this.sw_lat = v_sw_lat - lat_additive;
                            this.sw_lng = v_sw_lng - lng_additive;
                            this.ne_lat = v_ne_lat + lat_additive;
                            this.ne_lng = v_ne_lng + lng_additive;
                            await this.addGeoObjects();
                    }
                }
            }
      },
        setMapStateByHash() {
            const hashType = this.getParam('type'),
                hashCenter = this.getParam('center'),
                hashZoom = this.getParam('zoom');
            if (hashType) {
                map.setType('yandex#' + hashType);
            }
            if (hashCenter) {
                map.setCenter(hashCenter.split(','));
            }
            if (hashZoom) {
                map.setZoom(hashZoom);
            }
        },
        async initYandexMaps() {
            window.map = null;
            await ymaps.ready(async () => {
                window.map = new ymaps.Map(
                "map",
                {
                  center:
                    typeof this?.shops[0]?.UF_LONGITUDE !== "undefined" &&
                    typeof this?.shops[0]?.UF_LATITUDE !== "undefined"
                      ? [this.shops[0].UF_LATITUDE, this.shops[0].UF_LONGITUDE]
                      : ["55.749451", "37.542824"],
                  zoom: this.mode === "admin" ? 17 : 12,
                  controls: [
                    "geolocationControl",
                    "zoomControl",
                    "typeSelector",
                  ],
                },
                {
                  searchControlProvider: "yandex#search",
                  zoomControlSize: "small",
                  typeSelectorSize: "small",
                  zoomControlPosition: {
                    top: "350px",
                    right: "5px",
                  },
                  geolocationControlPosition: {
                    top: "450px",
                    right: "5px",
                  },
                  typeSelectorPosition: {
                    top: "130px",
                    right: "5px",
                  },
                }
              );
              window.map.events.add(["boundschange"], async (event) => {
                await this.setLocationHash(event);
              });
              // Try W3C Geolocation (Preferred)
              if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                  function (position) {
                    ymaps.ready(function () {
                      window.map.setCenter([
                        position.coords.latitude,
                        position.coords.longitude,
                      ]);
                    });
                  },
                  function () {
                    ymaps.ready(function () {
                      //window.map.setCenter([55.749451, 37.542824]);
                    });
                  }
                );
              }
              // Browser doesn't support Geolocation
              else {
                ymaps.ready(function () {
                  window.map.setCenter([55.749451, 37.542824]);
                });
              }

              if (this.shops.length !== 0) {
                await this.addGeoObjects();
              }

              this.setMapStateByHash();
            });
        },
        async addGeoObjects() {
            let features = [];
            let polygonsObject = [];
            let myIcon = ymaps.templateLayoutFactory.createClass(
                '<div class="map-name">'+ this.$Bitrix.Loc.getMessage('SHOP_NUMBER') +'{{ properties.iconCaption }}</div>'
            );
                const filteredShops = this.filteredShops();
                filteredShops.forEach((element, key) => {
                    features[key] = {
                        type: "Feature",
                        id: element.ID,
                        geometry: {type: "Point", coordinates: [element.UF_LATITUDE, element.UF_LONGITUDE]},
                        properties: {
                            balloonContentHeader: this.$Bitrix.Loc.getMessage('SHOP', {'#NUMBER#': element.UF_NUMBER}),
                            balloonContentBody: element.UF_ADDRESS + '<br>' +
                                this.$Bitrix.Loc.getMessage('PEOPLE', {'#COUNT#': element.UF_PEOPLE}) + '<br>' +
                                element.UF_ENTITY + '<br>' +
                                this.$Bitrix.Loc.getMessage('TIME') + '<br>' +
                                this.$Bitrix.Loc.getMessage('PHONE', {'#PHONE#': element.UF_PHONE}) + '<br>',
                            clusterCaption: this.$Bitrix.Loc.getMessage('SHOP', {'#NUMBER#': element.UF_NUMBER}),
                            iconCaption: element.UF_NUMBER,
                        },
                        options: {
                            preset: "islands#redDotIcon",
                            iconLayout: 'default#imageWithContent',
                            iconImageHref: this.$Bitrix.Data.get('site_path') + '/map-logo.svg',
                            iconImageSize: [36, 36],
                            iconShape: {type: 'Rectangle', coordinates: [[0, 36], [36, 0]]},
                            iconContentLayout: myIcon,
                        }
                    }
    
                    let polygonElement = {
                        type: "Feature",
                        id: element.ID,
                        geometry: {
                            type: "Polygon", // Описываем геометрию геообъекта.
                            coordinates: this.polygonCoordsAdd([element.UF_LATITUDE, element.UF_LONGITUDE], element.UF_VERTEX_COORDS ?? []),
                            fillRule: "nonZero" // Задаем правило заливки внутренних контуров по алгоритму "nonZero".
                        },
                        properties:{
                            // Описываем свойства геообъекта.
                            // Содержимое балуна.
                            hintContent: this.$Bitrix.Loc.getMessage('ADDRESS_PROGRAM', {'#COUNT#': element.UF_NUMBER}),
                            status: element.UF_STATUS,
                            id: element.ID,
                        },
                        options: {
                            fillColor: '#e75151',
                            strokeColor: '#ff0400',
                            opacity: 0.5,
                            strokeWidth: 5,
                            strokeStyle: 'solid'
                        }
                    };
    
                    polygonsObject.push(polygonElement);
                });
   
                let objectManager = new ymaps.ObjectManager({
                    clusterize: true,
                    clusterIconLayout: "default#pieChart",
                    clusterDisableClickZoom: false,
                });
                let polygonManager = new ymaps.ObjectManager();
                polygonManager.add(polygonsObject);
                objectManager.add(features);
                map.geoObjects.removeAll();
                map.geoObjects.add(polygonManager);
                map.geoObjects.add(objectManager);
    
                objectManager.objects.events.add(['balloonopen'], (e) => {
                    const coords = e.get('target').getData().geometry.coordinates;
                    map.setCenter(coords);
                });
                polygonManager.objects.events.add(['click'], (e) => {
                    let object = polygonManager.objects.getById(e.get('objectId'));
                    if (object.properties.status === '1' && this.mode !== 'marketer') {
                        let geoObject = new ymaps.GeoObject({
                            geometry: object.geometry,
                            properties: object.properties,
                        }, object.options);
    
                        geoObject.events.add(['click',], (e) => {
                            e.preventDefault();
                            geoObject.editor.startEditing();
                        });
                        geoObject.events.add(['dblclick'], (e)=> {
                            e.preventDefault();
                            geoObject.editor.stopEditing();
                            let vertexCoords = e.get('target').geometry.getCoordinates();
    
                            BX.ready(() => {
                                console.log("BX ready");
                                BX.ajax.runComponentAction('kb:address.program', 'setVertexCoords', {
                                    mode: 'class',
                                    data: {
                                        shopId: geoObject.properties.get('id'),
                                        coords: vertexCoords
                                    },
                                }).then(function (response) {
                                    // console.log(response);
                                }, function (response) {
                                    //console.log(response);
                                });
                            });
    
                        });
                        map.geoObjects.add(geoObject);
                        geoObject.editor.startEditing();
                        polygonManager.remove([e.get('objectId')]);
                    }
                });                
        },
        polygonCoordsAdd(coordsPlace, coordsVertex) {
            let polygonArray = coordsVertex
            //console.log("polygonArray" + polygonArray + "  --- coordsPlace: " + coordsPlace);
            if (coordsVertex.length === 0) {
                polygonArray = [
                    [
                        [(Number(coordsPlace[0]) + 0.00046).toFixed(6), (Number(coordsPlace[1]) - 0.00141).toFixed(6)],
                        [(Number(coordsPlace[0]) + 0.00046).toFixed(6), (Number(coordsPlace[1]) + 0.0030).toFixed(6)],
                        [(Number(coordsPlace[0]) - 0.00103).toFixed(6), (Number(coordsPlace[1]) + 0.0030).toFixed(6)],
                        [(Number(coordsPlace[0]) - 0.00103).toFixed(6), (Number(coordsPlace[1]) - 0.00141).toFixed(6)],
                    ]
                ];
            }

            return polygonArray;
        },
        deleteEditEvent() {
            map.geoObjects.removeAll();
            this.addGeoObjects();
        },
        exportExcel() {
            document.querySelector('#exportExcel').addEventListener('click',   () => {
                let excelArray = [];
                if (this.mode === 'admin') {
                    excelArray = this.shops[0].ADDRESS_PROGRAM.map(excelData => ({
                        [this.$Bitrix.Loc.getMessage('ADDRESS_STREET')]: excelData.UF_STREET,
                        [this.$Bitrix.Loc.getMessage('ADDRESS_HOUSE')]: excelData.UF_HOUSE,
                        [this.$Bitrix.Loc.getMessage('ADDRESS_COUNT_OF_FLATS')]: excelData.UF_FLAT_COUNT ?? 0,
                    }));
                } else {
                    excelArray = this.shops.map(excelData => ({
                        [this.$Bitrix.Loc.getMessage('ADDRESS_SHOP')]: excelData.UF_ADDRESS,
                        [this.$Bitrix.Loc.getMessage('NUMBER_SHOP')]: excelData.UF_NUMBER,
                        [this.$Bitrix.Loc.getMessage('FLAT_COUNT')]: excelData.UF_ADDRESS_SUM ?? 0,
                    }));
                }

                import("xlsx/dist/xlsx.mini.min").then((_XLSX) => {
                    const XLSX = _XLSX.default
                    const workbook = XLSX.utils.book_new();
                    const worksheet = XLSX.utils.json_to_sheet(excelArray);
                    XLSX.utils.book_append_sheet(workbook, worksheet, this.$Bitrix.Loc.getMessage('ADDRESS_PROGRAM_1'));
                    XLSX.writeFile(workbook, "export.xlsx");
                });
            });
        },
    },
    created: async function () {
        await this.initYandexMaps();
        this.exportExcel();
    },
    watch: {
        pointToDelete(newValue, oldValue) {
            if (oldValue.length > 0) {
                this.deleteEditEvent();
            }
        }
    },
	// language=Vue
	template: `
        <Admin v-if="mode === 'admin'" v-model:shopDetail="shops[0]"/>
        <Super v-else :marketer="mode === 'marketer'" v-model:shops="shops" @onGetAjax="deleteEditEvent()"/>
    `
};