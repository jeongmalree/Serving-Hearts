<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location:../USER-VERIFICATION/index.php');
    exit();
}

include '../UI/sidebar.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/top-bar.css">
    <link rel="stylesheet" href="../CSS/fonts.css">
    <link rel="stylesheet" href="../CSS/sidebar.css">
    
    <title>Document</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #map {
            height: 100vh;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    
    <script>
        let streetView;
        let isStreetViewActive = false;
        let map;
        var markers = [];

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: new google.maps.LatLng(14.5764, 121.0851), // Pasig, Philippines
                zoom: 13,
                mapTypeControl: false,  // Disable map type control (removes map/satellite buttons)
                streetViewControl: true,  // Keep the Street View control (Pegman icon)
                zoomControl: true,  // Optional: Keep zoom controls if you want them
                fullscreenControl: false  // Remove full-screen control
            });

            loadMarkers();

            // Initialize the Street View service
            streetView = map.getStreetView();
            streetView.setVisible(false);  // Initially hide Street View

            // Listen for changes in the Street View panorama
            streetView.addListener('visible_changed', function() {
                if (streetView.getVisible()) {
                    document.body.classList.add('mapsConsumerlibappresolution_high'); // Apply class to hide the top bar
                    document.querySelector('.top-bar').classList.add('hidden');  // Hide the top bar when Street View is active
                } else {
                    document.body.classList.remove('mapsConsumerlibappresolution_high'); // Remove class when back to map
                    document.querySelector('.top-bar').classList.remove('hidden');  // Show the top bar when Street View is inactive
                }
            });
        }

        function loadMarkers() {
            var infoWindow = new google.maps.InfoWindow();

            // Fetch XML data
            downloadUrl('http://localhost/Serving%20Hearts/EVENT_MODULE/maps/xml.php', function(data, status) {
                if (status === 200) {
                    var xml = data.responseXML;
                    var markersData = xml.documentElement.getElementsByTagName('marker');
                    Array.prototype.forEach.call(markersData, function(markerElem) {
                        var id = markerElem.getAttribute('id');
                        var name = markerElem.getAttribute('name');
                        var event_id = markerElem.getAttribute('event_id');
                        var address = markerElem.getAttribute('address');
                        var datetime = markerElem.getAttribute('datetime');
                        var imagePath = markerElem.getAttribute('image_path');
                        var status = markerElem.getAttribute('status');
                        var point = new google.maps.LatLng(
                            parseFloat(markerElem.getAttribute('lat')),
                            parseFloat(markerElem.getAttribute('lng'))
                        );

                        var iconUrl = (status === 'ended') ? 
                            'http://localhost/Serving%20Hearts/EVENT_MODULE/maps/markers/grey_marker.png' : 
                            'http://localhost/Serving%20Hearts/EVENT_MODULE/maps/markers/red_marker.png';

                        var marker = new google.maps.Marker({
                            map: map,
                            position: point,
                            icon: {
                                url: iconUrl,
                                scaledSize: new google.maps.Size(50, 50),
                                origin: new google.maps.Point(0, 0),
                                anchor: new google.maps.Point(15, 30)
                            }
                        });

                        var infowincontent = createInfoWindowContent(name, event_id, address, datetime, imagePath, status);
                        marker.addListener('click', function() {
                            infoWindow.setContent(infowincontent);
                            infoWindow.open(map, marker);
                        });

                        markers.push(marker);
                    });
                } else {
                    console.error('Error loading XML data');
                }
            });
        }

        function createInfoWindowContent(name, event_id, address, datetime, imagePath, status) {
        var infowincontent = document.createElement('div');
        infowincontent.style.cssText = 'font-family: Arial, sans-serif; font-size: 16px; padding: 20px; width: 300px; height: 300px; overflow: auto;';

        if (imagePath) {
            // Replace 'LOCATOR' with 'EVENT_MODULE' in the image path
            if (imagePath.includes('LOCATOR')) {
                imagePath = imagePath.replace('LOCATOR', 'EVENT_MODULE');
            }

            // Now, the imagePath should be correct
            var img = document.createElement('img');
            img.src = imagePath;  // Use the updated image path
            img.style.width = '280px';
            img.style.height = 'auto';
            img.style.marginBottom = '15px';  // Space between image and text

            // Append the image to the content
            infowincontent.appendChild(img);
        }

        // Adding event details with spacing
        infowincontent.innerHTML += `
            <div style="margin-bottom: 10px;"><strong>Event ID:</strong> ${event_id}</div>
            <div style="margin-bottom: 10px;"><strong>Event Name:</strong> ${name}</div>
            <div style="margin-bottom: 10px;"><strong>Status:</strong> 
                <span style="color:${status === 'active' ? 'green' : 'red'};">${status}</span>
            </div>
            <div style="margin-bottom: 10px;"><strong>Address:</strong> ${address}</div>
            <div style="margin-bottom: 15px;"><strong>Date and Time:</strong> ${datetime}</div>`;

        // Add "Book a Donation" button with padding and margin
        if (status === 'active') {
            var button = document.createElement('button');
            button.textContent = 'Book a Donation';
            button.style.cssText = 'margin-top: 10px; padding: 10px 15px; font-size: 15px; background-color: #C92A2A; color: white; border: none; border-radius: 6px; cursor: pointer; width: 280px; margin-top: 10px;';
            button.addEventListener('click', function() {
                window.location.href = "booking.php?event_id=" + event_id;
            });
            infowincontent.appendChild(button);
        }

        return infowincontent;
    }


        function downloadUrl(url, callback) {
            var request = new XMLHttpRequest();
            request.onreadystatechange = function() {
                if (request.readyState === 4) {
                    if (request.status === 200) {
                        callback(request, request.status);
                    } else {
                        console.error('HTTP error: ' + request.status);
                    }
                }
            };
            request.open('GET', url, true);
            request.send(null);
        }
    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDWX0HgQi1eVsifhHYNfR5DlEwDvEZ7AA4&callback=initMap"></script>
</body>
</html>
