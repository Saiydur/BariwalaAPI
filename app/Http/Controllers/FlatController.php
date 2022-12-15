<?php

namespace App\Http\Controllers;

use App\Models\Flat;
use App\Models\FlatAddress;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class FlatController extends Controller
{

    public function showCreate()
    {
        return response()->json([
            "user" => session()->get("user"),
        ], 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "description" => "required|string",
            "monthly_rent" => "required|numeric|gt:0",
            "size" => "required|numeric|gt:0",
            "street" => "required|string",
            "road" => "required|string",
            "city" => "required|string",
            "state" => "required|string",
            "postal_code" => "required|numeric|digits:4",
        ],
        [
            "description.required" => "Please enter a description",
            "monthly_rent.required" => "Please enter a monthly rent",
            "monthly_rent.gt" => "Monthly rent must be greater than 0",
            "size.required" => "Please enter a size",
            "size.gt" => "Size must be greater than 0",
            "street.required" => "Please enter a street",
            "road.required" => "Please enter a road",
            "city.required" => "Please enter a city",
            "state.required" => "Please enter a state",
            "postal_code.required" => "Please enter a postal code",
            "postal_code.digits" => "Postal code must be 4 digits",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => [$validator->errors()],
            ]);
        }

        $user = User::where("api_token", $request->api_token)->first();
        $flat = new Flat();
        $flat->description = $request->description;
        $flat->monthly_rent = $request->monthly_rent;
        $flat->size = $request->size;

        $flatAddress = new FlatAddress();
        $flatAddress->street = $request->street;
        $flatAddress->road = $request->road;
        $flatAddress->city = $request->city;
        $flatAddress->state = $request->state;
        $flatAddress->postal_code = $request->postal_code;

        $user->flats()->save($flat);
        $flat->flatAddress()->save($flatAddress);

        return response()->json([
            "status" => 200,
            "message" => "Flat created successfully",
        ]);
    }

    public function show(Request $request)
    {
        $user = User::where("api_token", $request->api_token)->first();
        if (!$user) {
            return response()->json([
                "status" => 400,
                "message" => "User not found",
            ]);
        }
        $flats = $user->flats()->get();
        if (!$flats) {
            return response()->json([
                "status" => 400,
                "message" => "Flats not found",
            ]);
        }
        $flats = $flats->map(function ($flat) {
            return [
                "id" => $flat->id,
                "description" => $flat->description,
                "monthly_rent" => $flat->monthly_rent,
                "size" => $flat->size,
                "street" => $flat->flatAddress->street,
                "road" => $flat->flatAddress->road,
                "city" => $flat->flatAddress->city,
                "state" => $flat->flatAddress->state,
                "postal_code" => $flat->flatAddress->postal_code,
                "is_rented" => $flat->is_rented
            ];
        });
        return response()->json([
            "status" => 200,
            "message" => $flats,
        ]);
    }

    public function showEdit($id)
    {
        $flat = Flat::where("id", $id)->first();
        if (!$flat) {
            return response()->json([
                "status" => 404,
                "message" => "Flat not found",
            ]);
        }
        return response()->json([
            "status" => 200,
            "message" => [
                "id" => $flat->id,
                "description" => $flat->description,
                "monthly_rent" => $flat->monthly_rent,
                "size" => $flat->size,
                "street" => $flat->flatAddress->street,
                "road" => $flat->flatAddress->road,
                "city" => $flat->flatAddress->city,
                "state" => $flat->flatAddress->state,
                "postal_code" => $flat->flatAddress->postal_code,
            ],
        ]);
    }

    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            "description" => "required|string",
            "monthly_rent" => "required|numeric|gt:0",
            "size" => "required|numeric|gt:0",
            "street" => "required|string",
            "road" => "required|string",
            "city" => "required|string",
            "state" => "required|string",
            "postal_code" => "required|numeric|digits:4",
            "is_rented" => "required|boolean",
        ],
        [
            "description.required" => "Please enter a description",
            "monthly_rent.required" => "Please enter a monthly rent",
            "monthly_rent.gt" => "Monthly rent must be greater than 0",
            "size.required" => "Please enter a size",
            "size.gt" => "Size must be greater than 0",
            "street.required" => "Please enter a street",
            "road.required" => "Please enter a road",
            "city.required" => "Please enter a city",
            "state.required" => "Please enter a state",
            "postal_code.required" => "Please enter a postal code",
            "postal_code.digits" => "Postal code must be 4 digits",
            "is_rented.required" => "Please enter a is_rented",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => [$validator->errors()],
            ]);
        }

        $flat = Flat::find($id);
        $flat->description = $request->description;
        $flat->monthly_rent = $request->monthly_rent;
        $flat->size = $request->size;
        $flat->is_rented = $request->is_rented;

        $flatAddress = FlatAddress::find($flat->flatAddress->id);
        $flatAddress->street = $request->street;
        $flatAddress->road = $request->road;
        $flatAddress->city = $request->city;
        $flatAddress->state = $request->state;
        $flatAddress->postal_code = $request->postal_code;

        $flat->save();
        $flatAddress->save();

        return response()->json([
            "status" => 200,
            "message" => "Flat update successfully",
        ]);
    }

    public function delete($id)
    {
        $flat = Flat::find($id);
        $flat->delete();
        return response()->json([
            "status" => 200,
            "message" => "Flat deleted successfully",
        ]);
    }
}
