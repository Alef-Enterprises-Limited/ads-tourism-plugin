(() => {
    'use strict';

    const settings = window.adsTourismMaps || {};
    const instances = new WeakMap();

    const decodeMarkers = (element) => {
        try {
            const markers = JSON.parse(element.dataset.markers || '[]');

            return Array.isArray(markers) ? markers : [];
        } catch (error) {
            return [];
        }
    };

    const announce = (element, message) => {
        const status = element.querySelector('[aria-live]');
        if (status) status.textContent = message;
    };

    const clearMarkers = (instance) => {
        instance.markers.forEach((marker) => marker.setMap(null));
        instance.markers = [];
    };

    const renderMarkers = (element, markerData, isUpdate = false) => {
        const instance = instances.get(element);

        if (!instance || !window.google?.maps) return;
        clearMarkers(instance);

        if (markerData.length === 0) {
            announce(element, settings.emptyMessage || 'No mapped tourism records are available.');
            return;
        }

        const bounds = new window.google.maps.LatLngBounds();
        markerData.forEach((data) => {
            const position = {lat: Number(data.latitude), lng: Number(data.longitude)};
            if (!Number.isFinite(position.lat) || !Number.isFinite(position.lng)) return;

            const marker = new window.google.maps.Marker({
                map: instance.map,
                position,
                title: String(data.title || ''),
            });
            const summary = String(data.summary || '');
            const title = String(data.title || '');
            const url = String(data.url || '');
            const content = document.createElement('div');
            const heading = document.createElement('strong');
            heading.textContent = title;
            content.append(heading);

            if (summary) {
                const paragraph = document.createElement('p');
                paragraph.textContent = summary;
                content.append(paragraph);
            }

            if (url) {
                const link = document.createElement('a');
                link.href = url;
                link.textContent = title;
                link.setAttribute('aria-label', title);
                content.append(link);
            }

            const information = new window.google.maps.InfoWindow({content});
            marker.addListener('click', () => information.open({anchor: marker, map: instance.map}));
            instance.markers.push(marker);
            bounds.extend(position);
        });

        if (instance.markers.length === 1) {
            instance.map.setCenter(instance.markers[0].getPosition());
            instance.map.setZoom(instance.zoom || 14);
        } else if (instance.markers.length > 1) {
            instance.map.fitBounds(bounds, 40);
        }

        if (isUpdate) {
            announce(element, settings.updatedMessage || 'Tourism map updated.');
        }
    };

    const initialize = (element) => {
        if (instances.has(element) || !window.google?.maps) return;

        const markers = decodeMarkers(element);
        if (markers.length === 0) return;
        const zoom = Math.max(0, Math.min(22, Number.parseInt(element.dataset.zoom, 10) || 0));
        const first = markers[0];
        const canvas = element.querySelector('.ads-tourism-map__canvas');
        if (!canvas) return;
        const map = new window.google.maps.Map(canvas, {
            center: {lat: Number(first.latitude), lng: Number(first.longitude)},
            zoom: zoom || 10,
            mapTypeControl: false,
            streetViewControl: false,
        });
        instances.set(element, {map, markers: [], zoom});
        renderMarkers(element, markers);
    };

    document.addEventListener('ads-tourism:results-updated', (event) => {
        const context = event.detail?.context;
        const markers = event.detail?.markers;
        if (!context || !Array.isArray(markers)) return;

        document.querySelectorAll('[data-ads-tourism-map][data-ads-tourism-context]')
            .forEach((element) => {
                if (element.dataset.adsTourismContext !== context) return;
                element.dataset.markers = JSON.stringify(markers);
                initialize(element);
                renderMarkers(element, markers, true);
            });
    });

    document.querySelectorAll('[data-ads-tourism-map][data-ads-tourism-provider="google"]')
        .forEach(initialize);
})();
