const mapBiding = new Map();

for (let setting of mapViewSettings) {

    const map = L.map(setting.id).setView(setting.center, setting.zoom);
    mapBiding.set(setting.id, map);

    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const markers = L.markerClusterGroup({
        spiderfyOnMaxZoom: true,
        spiderLegPolylineOptions: {
            opacity: 0.1,
        }
    });

    for (let i = 0; i < setting.markers.length; i++) {
        let marker = setting.markers[i];
        const title = marker.hover;
        const point = marker.point;
        marker = L.marker(new L.LatLng(point.lat, point.lon), {
            title: title
        });
        marker.bindPopup(title);
        markers.addLayer(marker);
    }

    map.addLayer(markers);

}