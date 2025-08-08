<?php
// src/Views/frontend/home.php
// This content will be injected into src/Views/layouts/frontend.php

// The $appUrl variable is passed from the HomeController, which gets it from $_ENV['APP_URL']
$appUrl = $appUrl ?? '/';

// Data for airports (will be passed from controller)
// This variable needs to be populated by the HomeController.
$airports = $airports ?? [];

// Define the API base URL based on the current frontend URL
// This is the CRUCIAL change: Use the same host as the frontend for API calls
$apiBaseUrl = str_replace(['http://', 'https://'], '//', $appUrl) . 'api/v1'; // Example: //taxi-booking.local/api/v1
?>
<!-- Carousel Start -->
<div class="header-carousel">
    <div id="carouselId" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
        <div class="carousel-inner" role="listbox">
            <div class="carousel-item active">
                <img src="/assets/frontend/img/carousel-2.jpg" class="img-fluid w-100" alt="First slide" />
                <div class="carousel-caption">
                    <div class="container py-4">
                        <div class="row g-5">
                            <div class="col-lg-6 fadeInLeft animated" data-animation="fadeInLeft" data-delay="1s" style="animation-delay: 1s;">
                                <div class="bg-secondary rounded p-5">
                                    <h4 class="text-white mb-4">CAR RESERVATION</h4>
                                    <form id="bookingStep1Form">
                                        <div class="row g-3">
                                            <!-- Service Type Selection -->
                                            <div class="col-12">
                                                <div class="btn-group w-100" role="group" aria-label="Service Type">
                                                    <input type="radio" class="btn-check" name="service_type" id="doorToDoor" autocomplete="off" checked value="door_to_door">
                                                    <label class="btn btn-outline-danger" for="doorToDoor">
                                                        <i class="fa fa-map-marker me-1 text-danger"></i> Door To Door
                                                    </label>

                                                    <input type="radio" class="btn-check" name="service_type" id="fromAirport" autocomplete="off" value="from_airport">
                                                    <label class="btn btn-outline-danger" for="fromAirport">
                                                        <i class="fa fa-plane-departure me-1 text-danger"></i> From Airport
                                                    </label>

                                                    <input type="radio" class="btn-check" name="service_type" id="toAirport" autocomplete="off" value="to_airport">
                                                    <label class="btn btn-outline-danger" for="toAirport">
                                                        <i class="fa fa-plane-arrival me-1 text-danger"></i> To Airport
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Pickup Location -->
                                            <div class="col-6 position-relative" id="pickupLocationField">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="pickup_location" placeholder="Pickup Location" name="pickup_location" autocomplete="off">
                                                    <label for="pickup_location" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-map-marker-alt me-1 text-danger"></i> Pickup Location
                                                    </label>
                                                </div>
                                                <!-- The suggestions will be injected here dynamically -->
                                            </div>

                                            <!-- Airport Selection -->
                                            <div class="col-6 d-none" id="airportSelectionField">
                                                <div class="form-floating">
                                                    <select class="form-select form-select-sm" id="airport_id" name="airport_id" aria-label="Airport Select">
                                                        <option value="">Select Airport</option>
                                                        <?php foreach ($airports as $airport): ?>
                                                            <option value="<?php echo htmlspecialchars($airport['id']); ?>"
                                                                data-from-tax-toll="<?php echo htmlspecialchars($airport['from_tax_toll']); ?>"
                                                                data-to-tax-toll="<?php echo htmlspecialchars($airport['to_tax_toll']); ?>">
                                                                <?php echo htmlspecialchars($airport['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="airport_id">
                                                        <i class="fa fa-plane me-1 text-danger"></i> Select Airport
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Dropoff Location -->
                                            <div class="col-6 position-relative" id="dropoffLocationField">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="dropoff_location" placeholder="Dropoff Location" name="dropoff_location" autocomplete="off">
                                                    <label for="dropoff_location" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-map-marker me-1 text-danger"></i> Dropoff Location
                                                    </label>
                                                </div>
                                                <!-- The suggestions will be injected here dynamically -->
                                            </div>

                                            <!-- Date and Time -->
                                            <div class="col-lg-6">
                                                <div class="form-floating">
                                                    <input type="date" class="form-control" id="pickup_date" placeholder="Date" name="pickup_date">
                                                    <label for="pickup_date">Pickup Date</label>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-floating">
                                                    <select class="form-select" id="pickup_time" name="pickup_time">
                                                        <?php
                                                        for ($minutes = 0; $minutes < 1440; $minutes += 15) {
                                                            $time24 = sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60); // "HH:MM" 24-hour
                                                            $time12 = date('h:i A', strtotime($time24)); // "hh:mm AM/PM" format
                                                            echo "<option value=\"$time12\">$time12</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                    <label for="pickup_time">
                                                        <i class="fa fa-clock me-1 text-danger"></i> Pickup Time
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Adults -->
                                            <div class="col-lg-6">
                                                <div class="form-floating">
                                                    <select class="form-select" id="adults" name="adults">
                                                        <option value="" selected>Select Adults</option>
                                                        <?php for ($i = 1; $i <= 7; $i++): ?>
                                                            <option value="<?= $i ?>"><?= $i ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                    <label for="adults" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-users me-1 text-danger"></i>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Children -->
                                            <div class="col-lg-6">
                                                <div class="form-floating">
                                                    <select class="form-select" id="children" name="children">
                                                        <option value="" selected>Select Children</option>
                                                        <?php for ($i = 1; $i <= 4; $i++): ?>
                                                            <option value="<?= $i ?>"><?= $i ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                    <label for="children" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-child me-1 text-primary"></i>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Luggage -->
                                            <div class="col-lg-6">
                                                <div class="form-floating">
                                                    <select class="form-select" id="luggage" name="luggage">
                                                        <option value="" selected>Select Luggage</option>
                                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                                            <option value="<?= $i ?>"><?= $i ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                    <label for="luggage" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-suitcase me-1 text-danger"></i>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Child Seats -->
                                            <div class="col-lg-6">
                                                <div class="form-floating">
                                                    <select class="form-select" id="child-seats" name="child_seats">
                                                        <option value="" selected>Select Child Seats</option>
                                                        <?php for ($i = 1; $i <= 4; $i++): ?>
                                                            <option value="<?= $i ?>"><?= $i ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                    <label for="child-seats" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-baby-carriage me-1 text-danger"></i>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="mt-3">
                                                    <div class="small text-muted">
                                                        <strong>Adults:</strong> Above 7 years; <strong>Children:</strong> Under 8 years
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Child Seat and Stop Over: Start -->
                                            <h5 class="col-lg-12 text-white mb-3 text-center text-color-warning d-none child-seat-stopover-section">Child Seat and Stop Over</h5>

                                            <!-- Stopovers -->
                                            <div class="col-lg-6 d-none child-seat-stopover-section">
                                                <div class="form-floating">
                                                    <select class="form-select" id="stop-over-charge" name="stop_overs">
                                                        <option value="" selected>Select Stopovers</option>
                                                        <?php for ($i = 1; $i <= 6; $i++): ?>
                                                            <option value="<?= $i ?>"><?= $i ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                    <label for="adults" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-stop-circle me-1 text-primary">
                                                            $<?php echo $commonSettings->stopOverCharge; ?>/stop : <span id="stopover_price">$0</span>
                                                        </i>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Rear Facing Infant Seats -->
                                            <div class="col-lg-6 d-none child-seat-stopover-section">
                                                <div class="form-floating">
                                                    <select class="form-select" id="rear_infant_seats" name="rear_infant_seats">
                                                        <option value="" selected>Select Rear Facing Seats</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                    </select>
                                                    <label for="rear_infant_seats" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-baby-carriage me-1 text-primary">
                                                            $<?php echo $commonSettings->infantRearFacingSeatCharge; ?>/seat : <span id="rear_price">$0</span>
                                                        </i>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Front Facing Infant Seats -->
                                            <div class="col-lg-6 d-none child-seat-stopover-section">
                                                <div class="form-floating">
                                                    <select class="form-select" id="front_infant_seats" name="front_infant_seats">
                                                        <option value="" selected>Select Front Facing Seats</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                    </select>
                                                    <label for="front_infant_seats" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-baby-carriage me-1 text-primary">
                                                            $<?php echo $commonSettings->infantFrontFacingSeatCharge; ?>/seat : <span id="front_price">$0</span>
                                                        </i>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Booster Seats -->
                                            <div class="col-lg-6 d-none child-seat-stopover-section">
                                                <div class="form-floating">
                                                    <select class="form-select" id="booster_seats" name="booster_seats">
                                                        <option value="" selected>Select Booster Seats</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                    </select>
                                                    <label for="booster_seats" class="form-label small mb-0 text-nowrap">
                                                        <i class="fa fa-baby-carriage me-1 text-primary">
                                                            $<?php echo $commonSettings->infantBoosterSeatCharge; ?>/seat : <span id="booster_price">$0</span>
                                                        </i>
                                                    </label>
                                                </div>
                                            </div>
                                            <!-- Child Seat and Stop Over: End -->


                                            <!-- Submit Button -->
                                            <div class="col-12">
                                                <button type="button" class="btn btn-danger w-100 py-2" id="getFareBtn">CONTINUE</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6 d-none d-lg-flex fadeInRight animated" data-animation="fadeInRight" data-delay="1s" style="animation-delay: 1s;">
                                <div class="text-start">
                                    <h1 class="display-5 text-white">Just pay $1 to confirm your joyful reservation</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Carousel End -->

<script>
    window.addEventListener('load', function() {

        if (window.jQuery) {

            $(function() {

                function generateSessionToken() {
                    // A simple, reliable method using a timestamp and a random number.
                    return Date.now().toString(36) + Math.random().toString(36).substring(2, 7);
                }

                // This is a session token to group autocomplete requests. It's a best practice for Google APIs.
                let sessionToken = generateSessionToken();

                // Pass the API base URL to JavaScript
                const API_BASE_URL = "<?php echo $apiBaseUrl; ?>";

                // Function to handle location suggestions for a given input field
                function handleLocationSuggestions(inputElement) {
                    let searchTerm = $(inputElement).val();
                    let $resultsContainer = $(inputElement).closest('.position-relative');

                    // Clear existing suggestions
                    $resultsContainer.find('.autocomplete-results').remove();

                    // Only start searching if the user has typed at least two characters
                    if (searchTerm.length > 1) {
                        // Make an AJAX call to your backend API
                        $.ajax({
                            url: `${API_BASE_URL}/locations/suggest`,
                            method: 'GET',
                            data: {
                                input: searchTerm,
                                session_token: sessionToken
                            },
                            success: function(response) {
                                //console.log("Suggestions received:", response.data);
                                // Check if the response status is "success" and data exists
                                if (response.status === 'success' && response.data && response.data.length > 0) {
                                    // Create a new list for the suggestions
                                    let $ul = $('<ul class="autocomplete-results list-group"></ul>');

                                    // Loop through the suggestions and create list items
                                    $.each(response.data, function(index, suggestion) {
                                        // Use only the 'description' field as requested
                                        let $li = $(`<li class="list-group-item">${suggestion.description}</li>`);
                                        $li.data('place-id', suggestion.place_id);
                                        $li.data('description', suggestion.description);
                                        $ul.append($li);
                                    });

                                    // Append the new list below the input field
                                    $resultsContainer.append($ul);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error fetching location suggestions:", error);
                                // You can add a visual error message here if needed
                            }
                        });
                    }
                }

                // Event listener for keyup on the pickup location input
                $('#pickup_location').on('keyup', function() {
                    handleLocationSuggestions(this);
                });

                // Event listener for keyup on the dropoff location input
                $('#dropoff_location').on('keyup', function() {
                    handleLocationSuggestions(this);
                });

                // Handle a click on a suggestion
                // We use event delegation here because the suggestions are added dynamically
                $(document).on('click', '.autocomplete-results li', function() {
                    let selectedDescription = $(this).data('description');
                    let selectedPlaceId = $(this).data('place-id');

                    // Find the input field associated with this suggestion list
                    let $input = $(this).closest('.position-relative').find('input');

                    // Update the input field with the selected description
                    $input.val(selectedDescription);

                    // You can also store the place_id in a hidden field if needed
                    // For example:
                    // $input.data('place-id', selectedPlaceId);

                    // Clear the suggestions after a selection is made
                    $(this).closest('.autocomplete-results').remove();

                    // Reset the session token for the next new search
                    sessionToken = generateSessionToken();
                });

                // Hide the suggestion list when clicking outside of the inputs
                $(document).on('click', function(e) {
                    // Check if the click is outside both the pickup and dropoff fields
                    if (!$(e.target).closest('#pickupLocationField').length && !$(e.target).closest('#dropoffLocationField').length) {
                        $('.autocomplete-results').remove();
                    }
                });


                // Pull PHP variables using inline JS
                const rearRate = parseFloat("<?php echo $commonSettings->infantRearFacingSeatCharge ?? 0; ?>");
                const frontRate = parseFloat("<?php echo $commonSettings->infantFrontFacingSeatCharge ?? 0; ?>");
                const boosterRate = parseFloat("<?php echo $commonSettings->infantBoosterSeatCharge ?? 0; ?>");
                const stopoverRate = parseFloat("<?php echo $commonSettings->stopOverCharge ?? 0; ?>");

                $('#child-seats').on('change', function() {

                    const childCount = parseInt($('#children').val()) || 0;

                    if (childCount === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Number of Children Missing!',
                            text: "Please select the number of children you'll take before selecting their seats.",
                        });
                    } else {
                        updateChildSeatStopoverVisibility();
                    }
                });

                function updateChildSeatStopoverVisibility() {

                    const childCount = parseInt($('#children').val()) || 0;
                    const childSeatsQty = parseInt($('#child-seats').val()) || 0;
                    const boosterQty = parseInt($('#booster_seats').val()) || 0;
                    const frontQty = parseInt($('#front_infant_seats').val()) || 0;
                    const rearQty = parseInt($('#rear_infant_seats').val()) || 0;
                    const stopovers = parseInt($('#stop-over-charge').val()) || 0;
                    let selectionMade = true;

                    // Prevent selecting seats without selecting children
                    if (childCount === 0) {

                        selectionMade = false;
                    }

                    if (frontQty + rearQty + boosterQty > childSeatsQty) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Seat Selection Issue!',
                            text: "You can't select more seats than the number of children.",
                        });

                        selectionMade = false;
                    }

                    if (frontQty > 0 && rearQty > 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Seat Selection Conflict!',
                            text: "You can't select both front and rear infant seats at the same time.",
                        });

                        selectionMade = false;
                    }

                    if (!selectionMade) {
                        // Reset seat selections
                        $('#booster_seats').val('');
                        $('#front_infant_seats').val('');
                        $('#rear_infant_seats').val('');

                        // Reset seat prices
                        $('#booster_price').text('$0');
                        $('#front_price').text('$0');
                        $('#rear_price').text('$0');

                        return;
                    }

                    // Show the section if any valid input exists
                    if (childCount > 0 && childSeatsQty > 0) {
                        $('.child-seat-stopover-section').removeClass('d-none');
                    } else {
                        $('.child-seat-stopover-section').addClass('d-none');
                    }

                    // Update prices
                    $('#booster_price').text(`$${boosterQty * boosterRate}`);
                    $('#front_price').text(`$${frontQty * frontRate}`);
                    $('#rear_price').text(`$${rearQty * rearRate}`);
                    $('#stopover_price').text(`$${stopovers * stopoverRate}`);
                }

                // Bind all relevant selectors
                $('#children, #booster_seats, #front_infant_seats, #rear_infant_seats, #stop-over-charge').on('change', updateChildSeatStopoverVisibility);

                // Initial check (in case of pre-filled values)
                updateChildSeatStopoverVisibility();


                // Service Type Toggle Logic
                function toggleFields() {
                    const selectedService = $('input[name="service_type"]:checked').val();
                    if (selectedService === 'door_to_door') {
                        $('#pickupLocationField').removeClass('d-none');
                        $('#airportSelectionField').addClass('d-none');
                        $('#dropoffLocationField').removeClass('d-none');
                    } else if (selectedService === 'from_airport') {
                        $('#pickupLocationField').addClass('d-none');
                        $('#airportSelectionField').removeClass('d-none');
                        $('#dropoffLocationField').removeClass('d-none');
                    } else if (selectedService === 'to_airport') {
                        $('#pickupLocationField').removeClass('d-none');
                        $('#airportSelectionField').removeClass('d-none');
                        $('#dropoffLocationField').addClass('d-none');
                    }
                }

                $('input[name="service_type"]').on('change', toggleFields);
                toggleFields();

                // Fare Calculation Logic
                $('#getFareBtn').on('click', async function() {
                    const form = $('#bookingStep1Form')[0];
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());

                    // Convert numbers
                    data.child_seats = parseInt(data.child_seats);
                    data.stop_overs = parseInt(data.stop_overs);

                    // Add calculated extra seat/stopover charges
                    data.booster_price = (parseInt($('#booster_seats').val()) || 0) * boosterRate;
                    data.front_price = (parseInt($('#front_infant_seats').val()) || 0) * frontRate;
                    data.rear_price = (parseInt($('#rear_infant_seats').val()) || 0) * rearRate;
                    data.stopover_price = (parseInt($('#stop-over-charge').val()) || 0) * stopoverRate;

                    // Get selected service type
                    const serviceType = $('input[name="service_type"]:checked').val();
                    data.service_type = serviceType;

                    // Adjust payload based on service type
                    if (serviceType === 'from_airport') {
                        const $selectedAirport = $('#airport_id');
                        const $airportOption = $selectedAirport.find('option:selected');
                        data.airport_id = $airportOption.val();
                        data.from_tax_toll = $airportOption.data('from-tax-toll');
                        delete data.pickup_location;
                    } else if (serviceType === 'to_airport') {
                        const $selectedAirport = $('#airport_id');
                        const $airportOption = $selectedAirport.find('option:selected');
                        data.airport_id = $airportOption.val();
                        data.to_tax_toll = $airportOption.data('to-tax-toll');
                        delete data.dropoff_location;
                    } else { // door_to_door
                        delete data.airport_id;
                    }

                    Object.keys(data).forEach(key => {
                        if (data[key] == '' || data[key] == 0 || data[key] == null) {
                            delete data[key];
                        }
                    });

                    //console.log('Request Payload:');
                    //console.log(JSON.stringify(data, null, 2));

                    try {
                        const response = await fetch(`${API_BASE_URL}/fare-calculation`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });

                        const result = await response.json();

                        if (result.status === 'success') {
                            //console.log('Fare Calculation Result:', result.data);
                            //const message = `Fare Calculated:\nTotal Fare: $${result.data.total_fare}\nExtra Charges: $${result.data.extra_charges_total}\nExtra Tolls: $${result.data.extra_toll_charges_total}\nBase Fare: $${result.data.base_fare}\nChild Seat Cost: $${result.data.child_seat_cost}\nStopover Cost: $${result.data.stopover_cost}\nDistance: ${result.data.distance_km} km\nDuration: ${result.data.duration_minutes} minutes`;
                            const fare = result.data;

                            let message = `<b>Fare Calculated:</b><br>
                                            <b>Total Fare:</b> $${fare.total_fare}<br>`;

                            if (fare.extra_charges && fare.extra_charges > 0) {
                                message += `<b>Extra Charges:</b> ${fare.extra_charges}<br>`;
                            }

                            message += `<b>Base Fare:</b> $${fare.base_fare}<br>`;

                            if (fare.child_seat_cost && fare.child_seat_cost > 0) {
                                message += `<b>Child Seat Cost:</b> ${fare.child_seat_cost}<br>`;
                            }
                            if (fare.stopover_cost && fare.stopover_cost > 0) {
                                message += `<b>Stopover Cost:</b> $${fare.stopover_cost}<br>`;
                            }

                            message += `<b>Distance:</b> ${fare.distance}<br>
                                            <b>Duration:</b> ${fare.duration}`;

                            displayMessage(message, 'success');
                        } else {
                            console.error('Fare Calculation Error:', result.message, result.errors);
                            //const errorMessage = `Error calculating fare: ${result.message} ${result.errors ? '\n' + JSON.stringify(result.errors) : ''}`;
                            //displayMessage(errorMessage, 'error');
                            const errorMessage = `
                                <strong>Error calculating fare:</strong> ${result.message}<br>
                                ${result.errors ? JSON.stringify(result.errors) : ''}
                            `;
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: errorMessage
                            });
                        }
                    } catch (error) {
                        console.error('Network or unexpected error during fare calculation:', error);
                        //displayMessage('An unexpected error occurred during fare calculation. Please try again.', 'error');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            Text: 'An unexpected error occurred during fare calculation. Please try again.'
                        });
                    }
                });

                // Function to display messages (replace alert)
                function displayMessage(message, type) {
                    console.log(`Message (${type}): ${message}`);
                    const $messageDiv = $('<div></div>').text(message).css({
                        padding: '10px',
                        margin: '10px 0',
                        borderRadius: '5px',
                        backgroundColor: type === 'success' ? '#d4edda' : '#f8d7da',
                        color: type === 'success' ? '#155724' : '#721c24'
                    });
                    $('body').prepend($messageDiv);
                    setTimeout(() => $messageDiv.remove(), 10000);
                }

                // Fetch active airports for dropdown
                async function fetchAirports() {
                    try {
                        const response = await fetch(`${API_BASE_URL}/airports/active`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const result = await response.json();
                        if (result.status === 'success') {
                            const $airportSelect = $('#airport_id');
                            $airportSelect.html('<option value="">Select Airport</option>');
                            result.data.forEach(airport => {
                                const $option = $('<option></option>')
                                    .val(airport.id)
                                    .text(airport.name)
                                    .attr('data-from-tax-toll', airport.from_tax_toll)
                                    .attr('data-to-tax-toll', airport.to_tax_toll);
                                $airportSelect.append($option);
                            });
                        } else {
                            console.error('Error fetching airports:', result.message);
                            displayMessage(`Error fetching airports: ${result.message}`, 'error');
                        }
                    } catch (error) {
                        console.error('Network error fetching airports:', error);
                        displayMessage('Network error fetching airports. Please check your connection.', 'error');
                    }
                }

                // Fetch active Google API Key
                async function fetchGoogleApiKey() {
                    try {
                        const response = await fetch(`${API_BASE_URL}/google-api-keys/active/single`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const result = await response.json();
                        if (result.status === 'success') {
                            //console.log('Google API Key:', result.data.api_key);
                            // You can store this key in a global variable or use it directly
                            // For example: window.GOOGLE_MAPS_API_KEY = result.data.api_key;
                        } else {
                            console.error('Error fetching Google API Key:', result.message);
                            displayMessage(`Error fetching Google API Key: ${result.message}`, 'error');
                        }
                    } catch (error) {
                        console.error('Network error fetching Google API Key:', error);
                        displayMessage('Network error fetching Google API Key. Please check your connection.', 'error');
                    }
                }

                // Call fetch functions on page load
                fetchAirports();
                fetchGoogleApiKey();

            });
        } else {
            console.error("jQuery is not loaded.");
        }
    });
</script>