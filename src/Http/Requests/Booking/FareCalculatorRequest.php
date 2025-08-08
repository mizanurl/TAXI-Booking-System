<?php

namespace App\Http\Requests\Booking;

use App\Http\Requests\FormRequest;
use App\Exceptions\ValidationException;

class FareCalculatorRequest extends FormRequest
{
    /**
     * Define the validation rules for booking.
     * @return array
     */
    protected function rules(): array
    {
        return [
            // Basic booking fields
            'service_type'   => ['required', 'string', 'in:from_airport,to_airport,door_to_door'],
            'pickup_date'    => ['required', 'string'],
            'pickup_time'    => ['required', 'string'],
            'adults'         => ['required', 'integer', 'min:1'],
            'luggage'        => ['nullable', 'integer', 'min:0'],

            // Children & Seats
            'children'       => ['nullable', 'integer', 'min:1'],
            // child_seats required when children > 0
            'child_seats'    => ['required_if_gt:children,0', 'integer', 'min:1'],

            // Seat types: allow zero; we'll enforce "at least one > 0" via validate()
            'front_infant_seats' => ['nullable', 'integer', 'min:0'],
            'rear_infant_seats'  => ['nullable', 'integer', 'min:0'],
            'booster_seats'      => ['nullable', 'integer', 'min:0'],

            // Seat cost: allow zero; we'll enforce "at least one > 0" via validate()
            'stopover_price'    => ['nullable'],
            'front_price'       => ['nullable'],
            'rear_price'        => ['nullable'],
            'booster_price'     => ['nullable'],

            // Airport / location fields: airport_id required when service_type in (from_airport,to_airport)
            // pickup_location/dropoff_location required depending on service_type
            'airport_id'       => ['required_if:service_type,from_airport,to_airport', 'integer', 'min:1'],
            'dropoff_location' => ['required_if:service_type,from_airport,door_to_door'],
            'pickup_location'  => ['required_if:service_type,to_airport,door_to_door'],
        ];
    }

    /**
     * Define custom validation messages.
     * @return array
     */
    protected function messages(): array
    {
        return [
            'service_type.required' => 'Please select a valid service.',
            'service_type.string' => 'Service type must be a valid string.',
            'service_type.in' => 'Please select one of the available service types.',

            'pickup_date.required' => 'Pickup date is required.',
            'pickup_date.string' => 'Pickup date must be a valid date.',
            'pickup_time.required' => 'Pickup time is required.',

            'adults.required' => 'Number of adults is required.',
            'adults.integer' => 'Adults must be a number.',
            'adults.min' => 'At least one adult is required.',

            'luggage.integer' => 'Number of luggages must be a number',

            'children.integer' => 'Children must be a number.',
            'children.min' => 'Children must be at least 1 if provided.',

            'child_seats.required_if_gt' => 'Child seats are required when children are selected.',
            'child_seats.integer' => 'Child seats must be a number.',
            'child_seats.min' => 'At least one child seat is required.',

            'front_infant_seats.integer' => 'Front infant seats must be a number.',
            'rear_infant_seats.integer' => 'Rear infant seats must be a number.',
            'booster_seats.integer' => 'Booster seats must be a number.',

            'airport_id.required_if' => 'Airport selection is required for this service.',
            'airport_id.integer' => 'Airport ID must be numeric.',
            'airport_id.min' => 'Airport ID must be valid.',

            'dropoff_location.required_if' => 'Drop-off location is required for this service.',
            'pickup_location.required_if' => 'Pickup location is required for this service.',
        ];
    }

    /**
     * Perform validation and add cross-field checks specific to this request.
     *
     * @return array Validated data
     * @throws ValidationException
     */
    public function validate(): array
    {
        // Run the base validator: this will throw ValidationException if base rule checks fail
        $validated = parent::validate();

        // --- Additional cross-field rule: seat-type requirement.
        // If children > 0 and child_seats > 0, at least one of the seat-type fields must be > 0.
        $childrenCount = isset($validated['children']) ? (int)$validated['children'] : 0;
        $childSeats    = isset($validated['child_seats']) ? (int)$validated['child_seats'] : 0;

        if ($childrenCount > 0 && $childSeats > 0) {
            $frontSeats  = isset($validated['front_infant_seats']) ? (int)$validated['front_infant_seats'] : 0;
            $rearSeats   = isset($validated['rear_infant_seats'])  ? (int)$validated['rear_infant_seats']  : 0;
            $boostSeats  = isset($validated['booster_seats'])      ? (int)$validated['booster_seats']      : 0;

            $stopoverPrice  = isset($validated['stopover_price']) ? (float)$validated['stopover_price'] : 0.00;
            $frontSeatPrice  = isset($validated['front_price']) ? (float)$validated['front_price'] : 0.00;
            $rearSeatPrice  = isset($validated['rear_price']) ? (float)$validated['rear_price'] : 0.00;
            $boosterSeatPrice  = isset($validated['booster_price']) ? (float)$validated['booster_price'] : 0.00;

            if (($frontSeats + $rearSeats + $boostSeats) <= 0) {
                // Build an errors array compatible with your FormRequest/ValidationException
                $errors = [
                    'seat_types' => [
                        'At least one type of child seat (front infant, rear infant, or booster) must be selected when children are traveling.'
                    ]
                ];
                throw new ValidationException('The given data was invalid.', $errors);
            }

            // Optional: ensure sum of seat-type counts equals child_seats if you want exact matching
            // (Uncomment if you want that enforcement)
            if (($frontSeats + $rearSeats + $boostSeats) !== $childSeats) {
                $errors = ['child_seats' => ['Sum of selected seat types must equal the child_seats value.']];
                throw new ValidationException('The given data was invalid.', $errors);
            }
        }

        // All checks passed — return validated data
        return $validated;
    }
}
