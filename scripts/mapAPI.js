let map;
let marker;
let geocoder;
let selectedPlace = null;

// Initialize the map when Google Maps API is loaded
function initMap() {
  geocoder = new google.maps.Geocoder();

  // Default center (can be set to a default location)
  const defaultLocation = { lat: 15.2993, lng: 74.1240 };

  // Create map centered at the default location
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 10,
    center: defaultLocation,
    mapTypeControl: true,
    streetViewControl: false,
    fullscreenControl: true,
  });

  // Create a search box
  const searchBox = new google.maps.places.SearchBox(
    document.getElementById("searchBox")
  );

  // Bias the search box results towards the current map viewport
  map.addListener("bounds_changed", () => {
    searchBox.setBounds(map.getBounds());
  });

  // Listen for the event fired when the user selects a prediction
  searchBox.addListener("places_changed", () => {
    const places = searchBox.getPlaces();

    if (places.length === 0) {
      return;
    }

    // For each place, get the location and center the map
    const bounds = new google.maps.LatLngBounds();

    places.forEach((place) => {
      if (!place.geometry || !place.geometry.location) {
        console.log("Returned place contains no geometry");
        return;
      }

      // Set the selected place
      selectedPlace = place;

      // Update the marker position
      if (marker) {
        marker.setPosition(place.geometry.location);
      } else {
        marker = new google.maps.Marker({
          map: map,
          position: place.geometry.location,
          draggable: true,
        });

        // Add event listener for marker drag
        marker.addListener("dragend", handleMarkerDrag);
      }

      // Update the selected location text
      document.getElementById(
        "selectedLocation"
      ).textContent = `Selected location: ${place.formatted_address}`;

      // Extend the bounds to include the new marker
      if (place.geometry.viewport) {
        bounds.union(place.geometry.viewport);
      } else {
        bounds.extend(place.geometry.location);
      }
    });

    map.fitBounds(bounds);
    map.setZoom(15); // Set zoom level after fitting bounds
  });

  // Add click event to the map to place the marker
  map.addListener("click", (event) => {
    placeMarker(event.latLng);
  });

  // Add search button functionality
  document.getElementById("searchButton").addEventListener("click", () => {
    const searchText = document.getElementById("searchBox").value;
    if (searchText.trim() !== "") {
      geocodeAddress(searchText);
    }
  });

  // Allow pressing Enter in search box
  document.getElementById("searchBox").addEventListener("keypress", (event) => {
    if (event.key === "Enter") {
      event.preventDefault();
      document.getElementById("searchButton").click();
    }
  });
}

// Function to place marker at a specific location
function placeMarker(location) {
  if (marker) {
    marker.setPosition(location);
  } else {
    marker = new google.maps.Marker({
      position: location,
      map: map,
      draggable: true,
    });

    // Add event listener for marker drag
    marker.addListener("dragend", handleMarkerDrag);
  }

  // Get address for the selected location
  geocoder.geocode({ location: location }, (results, status) => {
    if (status === "OK" && results[0]) {
      selectedPlace = {
        formatted_address: results[0].formatted_address,
        geometry: {
          location: location,
        },
      };

      document.getElementById(
        "selectedLocation"
      ).textContent = `Selected location: ${results[0].formatted_address}`;
    } else {
      document.getElementById(
        "selectedLocation"
      ).textContent = `Selected location: ${location.lat()}, ${location.lng()}`;
    }
  });
}

// Handle marker drag event
function handleMarkerDrag(event) {
  const location = event.latLng;

  // Get address for the selected location
  geocoder.geocode({ location: location }, (results, status) => {
    if (status === "OK" && results[0]) {
      selectedPlace = {
        formatted_address: results[0].formatted_address,
        geometry: {
          location: location,
        },
      };

      document.getElementById(
        "selectedLocation"
      ).textContent = `Selected location: ${results[0].formatted_address}`;
    } else {
      document.getElementById(
        "selectedLocation"
      ).textContent = `Selected location: ${location.lat()}, ${location.lng()}`;
    }
  });
}

// Geocode address function
function geocodeAddress(address) {
  geocoder.geocode({ address: address }, (results, status) => {
    if (status === "OK" && results[0]) {
      map.setCenter(results[0].geometry.location);
      map.setZoom(15);

      placeMarker(results[0].geometry.location);
    } else {
      alert("Geocode was not successful for the following reason: " + status);
    }
  });
}

// Modal operation functions
function openMapModal() {
  document.getElementById("overlay").style.display = "block";
  document.getElementById("mapModal").style.display = "block";

  // Trigger resize to ensure map displays correctly
  setTimeout(() => {
    google.maps.event.trigger(map, "resize");
  }, 100);
}

function closeMapModal() {
  document.getElementById("overlay").style.display = "none";
  document.getElementById("mapModal").style.display = "none";
}

// Event listeners for modal buttons
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("openMapBtn").addEventListener("click", openMapModal);
  document
    .getElementById("closeMapBtn")
    .addEventListener("click", closeMapModal);

  document
    .getElementById("confirmLocationBtn")
    .addEventListener("click", function () {
      if (selectedPlace) {
        document.getElementById("location").value =
          selectedPlace.formatted_address;
        closeMapModal();
      } else {
        alert("Please select a location first");
      }
    });
});
