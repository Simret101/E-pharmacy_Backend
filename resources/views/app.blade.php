<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Maps Place Autocomplete</title>
    <style>
        /* Set the size of the map */
        #map {
            height: 400px;
            width: 100%;
        }
        /* Style the input field */
        #pac-input {
            margin-top: 10px;
            width: 300px;
            padding: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <input id="pac-input" type="text" placeholder="Search for a place">
    <div id="map"></div>
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- Load the Google Maps JavaScript API with the Places library -->
 
     <script async defer
        src="https://maps.gomaps.pro/maps/api/js?key=AlzaSyamLwTvC3jXwvj9bneoPKNZG8_iEBrRWXE&libraries=geometry,places&callback=initMap">
    </script>
    <!-- <script async defer
    src="./gogleplaces2.js">
</script> -->
    <script>
 
        let map;
        let autocomplete;
        let marker;
        let selectedPlace;
        let markers = [];
 
        function initMap() {
            // Create the map centered on a default location
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: -33.8688, lng: 151.2195 }, // Default to Sydney, Australia
                zoom: 13
            });
 
            const input = document.getElementById('pac-input');
 
            // Create the autocomplete object and bind it to the input field
            autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);
 
            // Set up the event listener for when the user selects a place
            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (!place.geometry) {
                    console.log("No details available for the input: '" + place.name + "'");
                    return;
                }
 
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17); // Zoom to 17 if the place has no viewport
                }
 
                // Place a marker on the selected location
                marker = new google.maps.Marker({
                    position: place.geometry.location,
                    map: map
                });

                // Store the selected place
                selectedPlace = place;
            });
        }

        // function savePlace() {
        //     if (!selectedPlace) {
        //         alert('Please select a place first.');
        //         return;
        //     }

        //     const placeData = {
        //         name: selectedPlace.name,
        //         address: selectedPlace.formatted_address,
        //         lat: selectedPlace.geometry.location.lat(),
        //         lng: selectedPlace.geometry.location.lng()
        //     };

        //     fetch('/save-place', {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
        //         },
        //         body: JSON.stringify(placeData)
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.success) {
        //             alert('Place saved successfully!');
        //         } else {
        //             alert('Failed to save place.');
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Error:', error);
        //         alert('An error occurred while saving the place.');
        //     });
        // }

    

        function clearMarkers() {
            markers.forEach(marker => marker.setMap(null));
            markers = [];
        }

        function updatePosition(newLat, newLng) {
            const latLng = { lat: newLat, lng: newLng };
            marker.setPosition(latLng);
            map.setCenter(latLng);
        }

        Echo.channel('trackerApp')
            .listen('PersonMoved', (e) => {
                updatePosition(e.lat, e.lng);
            });
    </script>
</body>
</html>