<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    function verifyUser(Request $request): JsonResponse
    {
        $user = new User();
        $number = $request->input('number');

        $oldUser = $user->isUserExist($number);

        if ($oldUser) {
            return response()->json(["status" => true, "code" => env("STATUS_USER_FOUND"), "user" => $oldUser]);
        } else return response()->json(["status" => false, "code" => env("STATUS_USER_NOT_FOUND"), "error" => 'User not found!']);
    }

    function createNewUser(Request $request): JsonResponse
    {
        $user = new User();
        $createUser = $user->createUser($request);

        if ($createUser === true) return response()->json(["status" => true, "code" => env("STATUS_USER_CREATED"), "message" => "User created successfully."]);
        else return response()->json(["status" => false, "code" => env("STATUS_USER_ALREADY_EXISTS"), "error" => 'User already exits!', "user" => $createUser]);
    }
}
