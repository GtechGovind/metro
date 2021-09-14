<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'number',
        'operator'
    ];

    protected $table = 'users';

    public function isUserExist($number)
    {
        return DB::table('users')
            ->where('number', '=', $number)
            ->orderBy('timestamp', 'desc')
            ->first();
    }

    public function createUser(Request $request)
    {
        $isUserExist = $this->isUserExist($request->input('number'));

        if (empty($isUserExist)) {

            return DB::table('users')
                ->insert([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'number' => $request->input('number'),
                    'operator' => $request->input('operator')
                ]);

        } else return $isUserExist;

    }

}
