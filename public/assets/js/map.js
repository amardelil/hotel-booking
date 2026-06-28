// Initialize Google Map on room detail page
document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;
    
    const lat = parseFloat(mapContainer.dataset.lat);
    const lng = parseFloat(mapContainer.dataset.lng);
    const address = mapContainer.dataset.address;
    
    if (isNaN(lat) || isNaN(lng)) return;
    
    // Create map
    const map = new google.maps.Map(mapContainer, {
        center: { lat: lat, lng: lng },
        zoom: 15,
        mapTypeId: 'roadmap'
    });
    
    // Add marker
    new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: map,
        title: address
    });
});

