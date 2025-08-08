<?php

namespace App\Services;

use App\Models\Airport; // Assuming Airport model is used
use App\Models\ExtraCharge; // Assuming ExtraCharge model is used
use App\Services\GoogleMapsService;
use App\Services\SlabService; // Assuming you have this for base fare slabs
use App\Services\CarService; // Assuming you have this for car-specific pricing
use App\Services\CommonSettingService; // For general settings like stopover cost
use App\Services\ExtraChargeService; // For extra charges based on zip codes
use App\Exceptions\NotFoundException; // For airport not found
use \DateTime;

class FareCalculatorService
{
    private GoogleMapsService $googleMapsService;
    private SlabService $slabService;
    private CarService $carService; // Used to get airport details if needed
    private CommonSettingService $commonSettingService;
    private ExtraChargeService $extraChargeService;
    private AirportService $airportService; // NEW: Added AirportService to get airport details directly

    public function __construct(
        GoogleMapsService $googleMapsService,
        SlabService $slabService,
        CarService $carService,
        CommonSettingService $commonSettingService,
        ExtraChargeService $extraChargeService,
        AirportService $airportService
    ) {
        $this->googleMapsService = $googleMapsService;
        $this->slabService = $slabService;
        $this->carService = $carService;
        $this->commonSettingService = $commonSettingService;
        $this->extraChargeService = $extraChargeService;
        $this->airportService = $airportService;
    }

    /**
     * Calculates the total fare based on various parameters.
     *
     * @param array $data Contains all necessary booking details
     * @return array The calculated fare breakdown.
     * @throws \Exception If essential data is missing or calculation fails.
     */
    public function calculateFare(array $data): array
    {
        $baseFare = 0.0;
        $gratuityCharge = 0.0;
        $distance = 0.0;
        $duration = 0;
        $extraCharges = 0.0;
        $airportToll = 0.0;
        $tunnelCharge = 0.0;
        $holidayCharge = 0.0;
        $nightCharge = 0.0;
        $hiddenNightCharge = 0.0;
        $childSeatCost = 0.0;
        $stopoverCost = 0.0;
        $discountedForSquarePayment = 0.00;

        $commonSettings = $this->commonSettingService->getCommonSettings();

        $serviceType = $data['service_type'];

        // 1. Identify airport tolls charges
        if ($serviceType === 'from_airport' || $serviceType === 'to_airport') {

            $airportId = $data['airport_id'];
            $airportDetails = $this->airportService->getAirportById($airportId);
            $airportName = $airportDetails->name;

            if ($serviceType === 'from_airport') {
                $airportToll = $airportDetails->fromTaxToll;
            } else if ($serviceType === 'to_airport') {
                $airportToll = $airportDetails->toTaxToll;
            }
        } //end if


        // 2. Get Distance and Duration from Google Maps Service
        $pickupLocation = $data['pickup_location'] ?? '';
        $dropoffLocation = $data['dropoff_location'] ?? '';

        if ($serviceType === 'from_airport') {
            $pickupLocation = $airportName;
        } else if ($serviceType === 'to_airport') {
            $dropoffLocation = $airportName;
        }

        try {
            $distanceMatrix = $this->googleMapsService->getDistanceMatrix($pickupLocation, $dropoffLocation);
            $distance = $distanceMatrix['distance'];
            $duration = $distanceMatrix['duration'];
        } catch (\Exception $e) {
            error_log("Error getting distance matrix: " . $e->getMessage());
            // Fallback or throw a more specific error
            throw new \Exception("Could not calculate distance and duration for fare. " . $e->getMessage());
        }

        // 3. Calculate Extra Charges based on Dropoff Location
        $extraCharges = $this->_calculateExtraCharges($dropoffLocation);


        // 4. Get a suitable car based on passengers, luggages, and child seats
        $passengers = (int)($data['adults'] ?? 0) + (int)($data['children'] ?? 0);
        $luggages = (int)($data['luggage'] ?? 0);
        $isChildSeat = isset($data['children']) ? 1 : 0;

        $cardata = $this->carService->findSuitableCar($passengers, $luggages, $isChildSeat);

        //echo "<pre>";
        //print_r($cardata);
        //echo "</pre>";

        if (!$cardata) {
            throw new \Exception("No suitable car found.");
        }


        // 5. Calculate Base Fare according to the suggested car's slabs
        // Assuming $cardata['slabs'] contains the slab fares for the car
        $slabs = $cardata['slabs'];
        $baseFare = $this->_calculateDistanceFare($distance, $slabs);


        // 6. Calculate Child Seat & Stop Over Costs
        $stopoverCost = (int)($data['stopover_price'] ?? 0);
        $childSeatCost = (int)($data['front_price'] ?? 0) + (int)($data['rear_price'] ?? 0) + (int)($data['booster_price'] ?? 0);


        // 7. Adjust the Gratuity % from the Base Fare
        $gratuityPercentage = (float)($commonSettings->gratuity ?? 0);
        if ($gratuityPercentage > 0) {
            $gratuityCharge = $baseFare * $gratuityPercentage / 100;
        }


        // 8. Get the Tunnel Charge
        $tunnelCharge = (float)($commonSettings->tunnelCharge ?? 0);


        // 9. Calculate Holiday Charges
        if (!empty($commonSettings->holidays)) {
            $pickupDate = $data['pickup_date'] ?? date('Y-m-d');
            $holidays = $commonSettings->holidays;
            $holidaySurcharge = (float)($commonSettings->holidaySurcharge);
            $holidayCharge = $this->_calculateHolidayCharge($pickupDate, $holidays, $holidaySurcharge);
        }

        $pickupTime = $data['pickup_time'] ?? date('H:i A');

        // 10. Calculate Regular Night Charges
        $nightCharge = (float)($commonSettings->nightCharge ?? 0);
        if ($nightCharge > 0) {
            $nightSurcharge = $nightCharge;
            $nightStartTime = $commonSettings->nightChargeStartTime;
            $nightEndTime = $commonSettings->nightChargeEndTime;
            $nightCharge = $this->_calculateNightCharge($pickupTime, $nightSurcharge, $nightStartTime, $nightEndTime);
        }


        // 11. Calculate Hidden Night Charges
        $hiddenNightCharge = (float)($commonSettings->hiddenNightCharge ?? 0);
        if ($hiddenNightCharge > 0) {
            $hiddenNightSurcharge = $hiddenNightCharge;
            $hiddenNightStartTime = $commonSettings->hiddenNightChargeStartTime;
            $hiddenNightEndTime = $commonSettings->hiddenNightChargeEndTime;
            $hiddenNightCharge = $this->_calculateNightCharge($pickupTime, $hiddenNightSurcharge, $hiddenNightStartTime, $hiddenNightEndTime);
        }


        // Total Fare Calculation
        /*echo "<pre>Charge to be added: <br>";
        print_r("Base Fare: " .  $baseFare . "<br>Gratuity: " . $gratuityCharge . "<br>Tunnel Charge: " . $tunnelCharge .
            "<br>Airport Toll: " . $airportToll . "<br>Extra Charges: " . $extraCharges .
            "<br>Holiday: " . $holidayCharge . "<br>Night Charge: " . $nightCharge . "<br>Hidden Night Charge: " . $hiddenNightCharge .
            "<br>Child Seat Cost: " . $childSeatCost . "<br>Stopover Cost: " . $stopoverCost);
        echo "</pre>";*/
        $totalFare = $baseFare + $gratuityCharge + $tunnelCharge +
            $airportToll + $extraCharges +
            $holidayCharge + $nightCharge + $hiddenNightCharge +
            $childSeatCost + $stopoverCost;


        // 12. Discount for Square Payment
        $squareCharge = (float)($commonSettings->squareCharge ?? 0);
        if ($squareCharge > 0) {
            $discountedForSquarePayment = $totalFare - ($totalFare * $squareCharge / 100);
        }


        $fareBreakdown = [];
        $fareBreakdown['service_type'] = $data['service_type'];
        $fareBreakdown['pickup_location'] = $pickupLocation;
        $fareBreakdown['dropoff_location'] = $dropoffLocation;
        $fareBreakdown['pickup_date'] = $pickupDate;
        $fareBreakdown['pickup_time'] = $pickupTime;

        if (isset($data['from_tax_toll']) && (float)$data['from_tax_toll'] > 0) {
            $fareBreakdown['from_tax_toll'] = $data['from_tax_toll'];
        }

        if (isset($data['to_tax_toll']) && (float)$data['to_tax_toll'] > 0) {
            $fareBreakdown['to_tax_toll'] = $data['to_tax_toll'];
        }

        if ($extraCharges > 0) {
            $fareBreakdown['extra_charges'] = '$' . $extraCharges;
        }

        $fareBreakdown['luggage'] = $luggages;
        //$fareBreakdown['extra_luggage_capacity'] = $cardata['car']['extra_luggage_capacity'];
        $fareBreakdown['adults'] = $data['adults'];

        if (isset($data['children']) && (int)$data['children'] > 0) {

            $fareBreakdown['children'] = $data['children'];
            $fareBreakdown['child_seats'] = $data['child_seats'];

            if (isset($data['front_infant_seats']) && (int)$data['front_infant_seats'] > 0) {
                $fareBreakdown['front_infant_seats'] = $data['front_infant_seats'];
                $fareBreakdown['front_price'] = $data['front_price'];
            }

            if (isset($data['rear_infant_seats']) && (int)$data['rear_infant_seats'] > 0) {
                $fareBreakdown['rear_infant_seats'] = $data['rear_infant_seats'];
                $fareBreakdown['rear_price'] = $data['rear_price'];
            }

            if (isset($data['booster_seats']) && (int)$data['booster_seats'] > 0) {
                $fareBreakdown['booster_seats'] = $data['booster_seats'];
                $fareBreakdown['booster_price'] = $data['booster_price'];
            }
        } //end if

        $fareBreakdown['total_fare'] = '$' . round($totalFare, 2);
        $fareBreakdown['base_fare'] = '$' . round($baseFare, 2);
        $fareBreakdown['distance'] = $distance . ' Miles';
        $fareBreakdown['duration'] = $duration . ' (approx.)';
        if ($gratuityCharge > 0) {
            $fareBreakdown['gratuity'] = '$' . round($gratuityCharge, 2) . ' (' . $commonSettings->gratuity . ' % of fare)';
        }
        $fareBreakdown['airport_toll'] = '$' . $airportToll;
        if ($tunnelCharge > 0) {
            $fareBreakdown['tunnel_charge'] = '$' . $tunnelCharge;
        }
        if ($holidayCharge > 0) {
            $fareBreakdown['holiday_charge'] = '$' . $holidayCharge;
        }
        if ($nightCharge > 0) {
            $fareBreakdown['night_charge'] = '$' . $nightCharge;
        }
        if ($hiddenNightCharge > 0) {
            $fareBreakdown['hidden_night_charge'] = '$' . $hiddenNightCharge;
        }
        if ($stopoverCost > 0) {
            $fareBreakdown['stopover_cost'] = '$' . $stopoverCost;
        }
        if ($childSeatCost > 0) {
            $fareBreakdown['child_seat_cost'] = '$' . $childSeatCost;
        }
        if ($cardata) {
            $fareBreakdown['suggested_car'] = array(
                "regular_name" => $cardata['car']['regular_name'],
                "short_name" => $cardata['car']['short_name'],
                "color" => $cardata['car']['color'],
                "car_photo" => $cardata['car']['car_photo'],
                "car_features" => $cardata['car']['car_features'],
                "extra_luggage_capacity" => $cardata['car']['extra_luggage_capacity']
            );
        }
        if ($discountedForSquarePayment > 0) {
            $fareBreakdown['discounted_for_square_payment'] = '$' . round($discountedForSquarePayment, 2);
        }

        return $fareBreakdown;
    }


    private function _calculateExtraCharges(string $areaName): float
    {
        // Fetch extra charge with toll charges based on area name
        $extraCharge = $this->extraChargeService->getExtraChargeByAreaName($areaName);
        return $extraCharge ? (float) $extraCharge->extraCharge + (float) $extraCharge->extraTollCharge : 0.0;
    }

    /**
     * Calculate the fare based on distance slabs.
     *
     * @param float $distance
     * @param array $slabs
     * @return float
     */
    private function _calculateDistanceFare(float $distance, array $slabs): float
    {
        // Step 1: Filter only "Distance" type slabs
        $distanceSlabs = array_filter($slabs, function ($slab) {
            return $slab['slab_type'] === 'Distance';
        });

        // Step 2: Sort slabs by slab_value ASC
        usort($distanceSlabs, function ($a, $b) {
            return $a['slab_value'] <=> $b['slab_value'];
        });

        $totalFare = 0.0;
        $remainingDistance = $distance;
        $previousSlabValue = 0;

        foreach ($distanceSlabs as $slab) {
            $slabValue = (float) $slab['slab_value'];
            $fareAmount = (float) $slab['fare_amount'];

            // Calculate how much distance is covered in this slab range
            if ($remainingDistance > 0) {
                $rangeDistance = $slabValue - $previousSlabValue;
                $chargeableDistance = min($remainingDistance, $rangeDistance);

                $totalFare += $chargeableDistance * $fareAmount;

                $remainingDistance -= $chargeableDistance;
                $previousSlabValue = $slabValue;
            } else {
                break; // Full distance covered, exit loop
            }
        } //end foreach

        // If distance exceeds last slab, use last fare rate
        if ($remainingDistance > 0) {
            $lastFare = (float) end($distanceSlabs)['fare_amount'];
            $totalFare += $remainingDistance * $lastFare;
        }

        return round($totalFare, 2);
    }

    /**
     * Calculate holiday charge based on pickup date and holidays.
     *
     * @param string $pickupDate
     * @param string $holidays
     * @param float $holidaySurcharge
     * 
     * @return float
     */
    private function _calculateHolidayCharge(string $pickupDate, string $holidays, float $holidaySurcharge): float
    {
        $holidayCharge = 0.0;

        if (in_array($pickupDate, explode(',', $holidays))) {
            $holidayCharge += $holidaySurcharge;
        }

        return $holidayCharge;
    }

    private function _calculateNightCharge(string $pickupTime, float $nightSurcharge, string $nightStartTime, string $nightEndTime): float
    {
        $nightCharge = 0.0;

        // Parse pickup in 12-hour format, start/end in 24-hour format
        $pickup = DateTime::createFromFormat('h:i A', $pickupTime);
        $start  = DateTime::createFromFormat('H:i:s', $nightStartTime);
        $end    = DateTime::createFromFormat('H:i:s', $nightEndTime);

        if (!$pickup || !$start || !$end) {
            return 0.0; // Fallback if time parsing fails
        }

        // If end time is less than start time, range crosses midnight
        if ($end <= $start) {
            $end->modify('+1 day');
            if ($pickup < $start) {
                $pickup->modify('+1 day');
            }
        }

        if ($pickup >= $start && $pickup <= $end) {
            $nightCharge = $nightSurcharge;
        }

        return $nightCharge;
    }
}
