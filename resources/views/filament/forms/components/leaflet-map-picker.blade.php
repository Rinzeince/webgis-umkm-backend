<div
    wire:ignore
    x-data="{
        lat: $wire.entangle('data.latitude'),
        lng: $wire.entangle('data.longitude'),
        map: null,
        marker: null,
        isSelfUpdating: false,

        init() {
            if (typeof L === 'undefined') {
                if (!document.getElementById('leaflet-css')) {
                    let css = document.createElement('link');
                    css.id = 'leaflet-css';
                    css.rel = 'stylesheet';
                    css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(css);
                }
                if (!document.getElementById('leaflet-js')) {
                    let js = document.createElement('script');
                    js.id = 'leaflet-js';
                    js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    js.onload = () => this.initMap();
                    document.head.appendChild(js);
                } else {
                    document.getElementById('leaflet-js').addEventListener('load', () => this.initMap());
                }
            } else {
                this.$nextTick(() => this.initMap());
            }

            this.$watch('lat', value => {
                if (!this.isSelfUpdating) {
                    this.updateMarkerFromInputs();
                }
            });
            this.$watch('lng', value => {
                if (!this.isSelfUpdating) {
                    this.updateMarkerFromInputs();
                }
            });
        },

        initMap() {
            if (this.map) return;

            let defaultLat = parseFloat(this.lat) || -6.8436;
            let defaultLng = parseFloat(this.lng) || 107.5028;
            let initialZoom = (this.lat && this.lng) ? 14 : 11;

            let container = this.$refs.mapContainer;
            if (!container) return;

            this.map = L.map(container, {
                preferCanvas: true
            }).setView([defaultLat, defaultLng], initialZoom);

            // Opsi 1: Esri Light Gray Canvas (Cadangan / Bebas API Key)
            // L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
            //     attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ',
            //     maxZoom: 18
            // }).addTo(this.map);

            // Opsi 2: CARTO Light Basemap (Aktif dengan API Key)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?api_key=eyJhbGciOiJIUzI1NiJ9.eyJhIjoiYWNfajEyNXN5cTEiLCJqdGkiOiJmOTllNmM4NiIsImV4cCI6MTgxOTI5NTUyMH0.6SkCRPb29xLr8sThlB0PnmIIbXtu0RHQ5JIpsXvv9Ak', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                maxZoom: 19
            }).addTo(this.map);

            // Load GeoJSON Polygons for Kecamatan Boundaries
            fetch('/geojson/kecamatan_kbb.geojson')
                .then(res => res.json())
                .then(geoJsonData => {
                    let colors = ['#00684A', '#2563eb', '#7c3aed', '#db2777', '#ea580c', '#16a34a', '#0284c7', '#d97706', '#059669', '#4f46e5', '#c026d3', '#dc2626', '#ca8a04', '#0d9488', '#9333ea', '#e11d48'];
                    L.geoJSON(geoJsonData, {
                        style: function(feature) {
                            let id = feature.properties ? feature.properties.id_kecamatan : 1;
                            let color = colors[(id - 1) % colors.length];
                            return {
                                color: '#0f172a',
                                weight: 1.5,
                                opacity: 0.8,
                                fillColor: color,
                                fillOpacity: 0.35
                            };
                        },
                        onEachFeature: function(feature, layer) {
                            if (feature.properties && feature.properties.nama_kecamatan) {
                                layer.bindTooltip('Kec. ' + feature.properties.nama_kecamatan, {
                                    sticky: true,
                                    direction: 'top'
                                });
                            }
                        }
                    }).addTo(this.map);
                })
                .catch(err => console.warn('Could not load kecamatan GeoJSON overlay:', err));

            this.marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(this.map);

            this.marker.on('dragend', (e) => {
                let position = e.target.getLatLng();
                this.updateCoords(position.lat, position.lng);
            });

            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                this.updateCoords(e.latlng.lat, e.latlng.lng);
            });

            // Handle map resize observer for Filament modals/grid recalculations
            let resizeObserver = new ResizeObserver(() => {
                if (this.map) {
                    this.map.invalidateSize();
                }
            });
            resizeObserver.observe(container);

            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 300);
        },

        updateCoords(newLat, newLng) {
            let formattedLat = parseFloat(newLat).toFixed(8);
            let formattedLng = parseFloat(newLng).toFixed(8);

            this.isSelfUpdating = true;
            this.lat = formattedLat;
            this.lng = formattedLng;

            $wire.set('data.latitude', formattedLat, false);
            $wire.set('data.longitude', formattedLng, false);

            setTimeout(() => {
                this.isSelfUpdating = false;
            }, 200);
        },

        updateMarkerFromInputs() {
            if (!this.map || !this.marker) return;
            let currentLat = parseFloat(this.lat);
            let currentLng = parseFloat(this.lng);
            if (!isNaN(currentLat) && !isNaN(currentLng)) {
                let newLatLng = new L.LatLng(currentLat, currentLng);
                this.marker.setLatLng(newLatLng);
                if (!this.map.getBounds().contains(newLatLng)) {
                    this.map.panTo(newLatLng);
                }
            }
        }
    }"
    class="w-full space-y-2 col-span-2"
    style="grid-column: span 2 / span 2;"
>
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
        <span class="font-medium">📍 Klik peta atau geser marker untuk memilih titik koordinat UMKM secara presisi (dengan batas wilayah polygon kecamatan):</span>
        <span class="font-mono text-primary-600 dark:text-primary-400 font-bold" x-text="lat && lng ? `Lat: ${lat}, Lng: ${lng}` : 'Klik peta untuk pilih koordinat'"></span>
    </div>
    <div
        x-ref="mapContainer"
        style="height: 380px; width: 100%; border-radius: 0.75rem; z-index: 1;"
        class="border border-gray-300 dark:border-gray-700 shadow-inner bg-gray-100 dark:bg-gray-800"
    ></div>
</div>
