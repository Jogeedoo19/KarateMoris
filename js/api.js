/* function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                let latitude = position.coords.latitude;
                let longitude = position.coords.longitude;
                
                // Call Nominatim API to get address
                let nominatimUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`;

                fetch(nominatimUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            document.getElementById('txtaddr').value = data.display_name;
                        } else {
                            alert("Could not fetch address.");
                        }
                    })
                    .catch(error => console.error("Error fetching address:", error));
            },
            (error) => {
                alert("Geolocation failed: " + error.message);
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}

 */
function searchLocation() {
    let query = document.getElementById('locationSearch').value;
    if (query.length < 3) { // Only search when 3 or more characters are typed
        document.getElementById('suggestionsList').style.display = "none";
        return;
    }

    let nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${query}`;

    fetch(nominatimUrl)
        .then(response => response.json())
        .then(data => {
            let suggestionsList = document.getElementById('suggestionsList');
            suggestionsList.innerHTML = ""; // Clear previous suggestions

            if (data.length === 0) {
                suggestionsList.style.display = "none";
                return;
            }

            data.forEach(location => {
                let listItem = document.createElement("li");
                listItem.textContent = location.display_name;
                listItem.style.cursor = "pointer";
                listItem.style.padding = "5px";
                listItem.style.borderBottom = "1px solid #ddd";

                listItem.onclick = function() {
                    document.getElementById('locationSearch').value = location.display_name;
                    document.getElementById('txtaddr').value = location.display_name; // Save in hidden field
                    suggestionsList.style.display = "none";
                };

                suggestionsList.appendChild(listItem);
            });

            suggestionsList.style.display = "block";
        })
        .catch(error => console.error("Error fetching location data:", error));
}
