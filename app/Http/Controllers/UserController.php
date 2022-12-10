<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserAddress;
use App\Models\UserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        if($users->isEmpty()){
            return response()->json(
                [
                    'error' => 'No user found',
                ],
                404
            );
        }
        return response()->json(
            [
                'data' => $users,
            ],
            200
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json(
            [],200
        );
    }

    public function sendVerifyMailLink($email)
    {
        $user = User::where('email', $email)->first();
        if($user){
            $link = 'http://localhost:8000/api/verify/'.$user->id;
            $data = array('name'=>"Bariwala",'url' => $link);
            Mail::send("mail",$data, function($message) use ($email) {
                $message->to($email, 'Bariwala')->subject
                ('Verify your email');
            });
            return response()->json(
                [
                    'message' => 'Verification link sent to your email',
                ],
                200
            );
        }
        return response()->json(
            [
                'error' => 'No user found',
            ],
            404
        );
    }

    public function verify($id)
    {
        $user = User::find($id);
        if($user){
            $user->userActivity->is_active = true;
            $user->userActivity->is_verified = true;
            $user->save();
            $user->userActivity->save();
            return response()->json(
                [
                    'message' => 'Email verified successfully',
                ],
                200
            );
        }
        return response()->json(
            [
                'error' => 'No user found',
            ],
            404
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|max:255',
            'email' => 'required|unique:users|max:255',
            'date_of_birth' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'nid' => 'required',
            'phone' => 'required',
            'password' => 'required',
            'present_address' => 'required',
            'permanent_address' => 'required',
            'role' => 'required',
        ],
        [
            'username.required' => 'username is required',
            'username.unique' => 'username is already taken',
            'email.required' => 'Email is required',
            'email.unique' => 'Email is already taken',
            'date_of_birth.required' => 'Date of birth is required',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'nid.required' => 'NID is required',
            'phone.required' => 'Phone is required',
            'password.required' => 'Password is required',
            'present_address.required' => 'Present address is required',
            'permanent_address.required' => 'Permanent address is required',
            'role.required' => 'Role is required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error' => [$validator->errors()],
                ],
                400
            );
        }
        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->date_of_birth = $request->date_of_birth;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->NID = $request->nid;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);

        $userAddress = new UserAddress();
        $userAddress->present_address = $request->present_address;
        $userAddress->permanent_address = $request->permanent_address;

        $userRole = new UserRole();
        $userRole->name = $request->role;

        $userActivity = new UserActivity();

        $user->save();
        $user->userAddress()->save($userAddress);
        $user->userRoles()->save($userRole);
        $user->userActivity()->save($userActivity);

        $this->sendVerifyMailLink($user->email);

        if ($user) {
            return response()->json(
                [
                    'message' => 'To Active Your Account Please Verify Your Email',
                ],
                201
            );
        } else {
            return response()->json(
                [
                    'message' => 'User creation failed',
                ],
                500
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        json_encode($user);
        if ($user) {
            return response()->json(
                [
                    'data' => $user,
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'User not found',
                ],
                404
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        json_encode($user);
        if ($user) {
            return response()->json(
                [
                    'data' => $user,
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'User not found',
                ],
                400
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|max:255',
            'email' => 'required|unique:users|max:255',
            'date_of_birth' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'nid' => 'required',
            'phone' => 'required',
            'password' => 'required',
            'present_address' => 'required',
            'permanent_address' => 'required',
            'role' => 'required',
        ],
        [
            'username.required' => 'username is required',
            'username.unique' => 'username is already taken',
            'email.required' => 'Email is required',
            'email.unique' => 'Email is already taken',
            'date_of_birth.required' => 'Date of birth is required',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'nid.required' => 'NID is required',
            'phone.required' => 'Phone is required',
            'password.required' => 'Password is required',
            'present_address.required' => 'Present address is required',
            'permanent_address.required' => 'Permanent address is required',
            'role.required' => 'Role is required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error' => [$validator->errors()],
                ],
                400
            );
        }
        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->date_of_birth = $request->date_of_birth;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->NID = $request->nid;
        $user->phone = $request->phone;
        $user->password = $request->password;

        $userAddress = new UserAddress();
        $userAddress->present_address = $request->present_address;
        $userAddress->permanent_address = $request->permanent_address;

        $userRole = new UserRole();
        $userRole->name = $request->role;

        $userActivity = new UserActivity();
        $userActivity->is_verified = true;

        $user->save();
        $user->userAddress()->save($userAddress);
        $user->userRoles()->save($userRole);
        $user->userActivity()->save($userActivity);

        if ($user) {
            return response()->json(
                [
                    'message' => 'User updated successfully',
                ],
                500
            );
        } else {
            return response()->json(
                [
                    'message' => 'User updating failed',
                ],
                500
            );
        }
    }

    /**
     * Get the specified resource from storage for delete.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $user = User::find($id);
        if ($user) {
            return response()->json(
                [
                    'data' => $user,
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'User not found',
                ],
                400
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return response()->json(
                [
                    'message' => 'User deleted successfully',
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'User not found',
                ],
                404,
            );
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usernameOrEmail' => 'required',
            'password' => 'required',
        ],
        [
            'usernameOrEmail.required' => 'username or email is required',
            'password.required' => 'Password is required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error' => [$validator->errors()],
                ],
                400
            );
        }
        $user = User::where('username', $request->usernameOrEmail)->orWhere('email', $request->usernameOrEmail)->first();
        if ($user) {
            if (password_verify($request->password, $user->password)) {
                session()->put('user', $user);
                return response()->json(
                    [
                        'message' => 'User logged in successfully',
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'message' => 'Invalid Username or Email',
                    ],
                    400
                );
            }
        } else {
            return response()->json(
                [
                    'message' => 'Invalid Username or Email',
                ],
                404
            );
        }
    }

    public function logout(){
        session()->forget('user');
        return response()->json(
            [
                'message' => 'User logged out successfully',
            ],
            200
        );
    }

    public function profile(){
       $user = session()->get('user');
         if($user){
              return response()->json(
                [
                    'data' => $user,
                ],
                200
              );
            }
    }

    public function editProfile()
    {
        $user = User::find(session()->get('user')->id);
        if ($user) {
            return response()->json(
                [
                    'userData' => $user,
                    'userAddress' => $user->userAddress,
                    'userRole' => $user->userRoles,
                    'userActivity' => $user->userActivity,
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'User not found',
                ],
                400
            );
        }
    }

    public function updateProfile(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required',
            'date_of_birth' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'nid' => 'required',
            'phone' => 'required',
            'present_address' => 'required',
            'permanent_address' => 'required',
        ],
        [
            'username.required' => 'Username is required',
            'email.required' => 'Email is required',
            'date_of_birth.required' => 'Date of birth is required',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'nid.required' => 'NID is required',
            'phone.required' => 'Phone is required',
            'present_address.required' => 'Present address is required',
            'permanent_address.required' => 'Permanent address is required',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'error' => [$validator->errors()],
                ],
                400
            );
        }

        $user = User::find(session()->get('user')->id);
        $user->username = $request->username;
        $user->email = $request->email;
        $user->date_of_birth = $request->date_of_birth;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->nid = $request->nid;
        $user->phone = $request->phone;
        $user->userAddress->present_address = $request->present_address;
        $user->userAddress->permanent_address = $request->permanent_address;
        $user->save();
        $user->userAddress->save();

        if ($user) {
            return response()->json(
                [
                    'message' => 'User updated successfully',
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'User updating failed',
                ],
                500
            );
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required',
        ],
        [
            'old_password.required' => 'Old password is required',
            'new_password.required' => 'New password is required',
            'confirm_password.required' => 'Confirm password is required',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'error' => [$validator->errors()],
                ],
                400
            );
        }

        $user = User::find(session()->get('user')->id);
        if (password_verify($request->old_password, $user->password)) {
            if ($request->new_password == $request->confirm_password) {
                $user->password = bcrypt($request->new_password);
                $user->save();
                return response()->json(
                    [
                        'message' => 'Password changed successfully',
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'message' => 'New password and confirm password does not match',
                    ],
                    400
                );
            }
        } else {
            return response()->json(
                [
                    'message' => 'Old password does not match',
                ],
                400
            );
        }
    }

    public function check()
    {
        if (session()->has('user')) {
            return response()->json(
                [
                    'message' => 'User logged in',
                    'data' => session()->get('user'),
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'User not logged in',
                ],
                404
            );
        }
    }
}
