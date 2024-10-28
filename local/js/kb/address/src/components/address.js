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
    computed: {
        ...mapState(shopStore, ['shops']),
        pointToDelete() {
            return this.shops.filter(shop => shop.UF_STATUS === '2')
        }
    },
    methods: {
        getParam(name, location) {
            location = location || window.location.hash;
            var res = location.match(new RegExp('[#&]' + name + '=([^&]*)', 'i'));
            return (res && res[1] ? res[1] : false);
        },
        setLocationHash() {
            const params = [
                'type=' + map.getType().split('#')[1],
                'center=' + map.getCenter(),
                'zoom=' + map.getZoom()
            ];
            window.location.hash = params.join('&');
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
        initYandexMaps() {
            window.map = null;
            ymaps.ready(() => {
                map = new ymaps.Map('map', {
                    center: typeof this?.shops[0]?.UF_LONGITUDE !== 'undefined' && typeof this?.shops[0]?.UF_LATITUDE !== 'undefined'? [this.shops[0].UF_LATITUDE, this.shops[0].UF_LONGITUDE] : ['55.749451', '37.542824'],
                    zoom: this.mode === 'admin' ? 17 : 12,
                    controls: ['geolocationControl', 'zoomControl', 'typeSelector']
                }, {
                    searchControlProvider: 'yandex#search',
                    zoomControlSize: 'small',
                    typeSelectorSize: 'small',
                    zoomControlPosition: {
                        top: '350px',
                        right: '5px',
                    },
                    geolocationControlPosition: {
                        top: '450px',
                        right: '5px',
                    },
                    typeSelectorPosition: {
                        top: '130px',
                        right: '5px',
                    },
                });
                if (this.shops.length !== 0) {
                    this.addGeoObjects();
                }
                map.events.add(['boundschange', 'typechange'], () => {
                    this.setLocationHash();
                });
                this.setMapStateByHash();
            });
        },
        addGeoObjects() {
            let features = [];
            let polygonsObject = [];
            let myIcon = ymaps.templateLayoutFactory.createClass(
                '<div class="map-name">'+ this.$Bitrix.Loc.getMessage('SHOP_NUMBER') +'{{ properties.iconCaption }}</div>'
            );

            this.shops.forEach((element, key) => {
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
                clusterize: false,
                clusterIconLayout: "default#pieChart",
                clusterDisableClickZoom: true,
            });
            let polygonManager = new ymaps.ObjectManager();
            polygonManager.add(polygonsObject);
            objectManager.add(features);
            map.geoObjects.add(polygonManager);
            map.geoObjects.add(objectManager);
            if (this.mode === 'marketer') {
                map.setBounds(map.geoObjects.getBounds(), {checkZoomRange:true}).then(() => {
                    if(map.getZoom() < 5) map.setZoom(5);
                });
            }

            objectManager.objects.events.add(['balloonopen'], (e) => {
                const coords = e.get('target').getData().geometry.coordinates;
                map.setCenter(coords);
            });
console.log("objectManager: " +objectManager);
            polygonManager.objects.events.add(['click'], (e) => {
                let object = polygonManager.objects.getById(e.get('objectId'));
console.log("object: " + object);
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
                                console.log(response);
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
            console.log("polygonArray" + polygonArray + "  --- coordsPlace: " + coordsPlace);
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
    created() {
        this.initYandexMaps();
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