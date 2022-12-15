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
use Illuminate\Support\Str;

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
                'status'=>"success",
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

    /**
     * Verify user email
     * @param $email as string where verification link will be sent
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Verify At Database
     * @param $id as int
     * @return \Illuminate\Http\Response
     */
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
            'username.required' => 'Username is required',
            'username.unique' => 'Username is already taken',
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
                    'status' => 400,
                    'message' => [$validator->errors()],
                ],
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
                    'status' => 200,
                    'message' => 'To Active Your Account Please Verify Your Email',
                ],
            );
        } else {
            return response()->json(
                [
                    'status' => 500,
                    'message' => 'User creation failed',
                ],
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
        $user = User::where('id', $id)->first();
        $roles = array();
        foreach ($user->userRoles as $role) {
            array_push($roles, $role->name);
        }
        $user = [
            "id" => $user->id,
            "username" => $user->username,
            "email" => $user->email,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "date_of_birth" => $user->date_of_birth,
            "nid" => $user->NID,
            "phone" => $user->phone,
            "present_address" => $user->userAddress->present_address,
            "permanent_address" => $user->userAddress->permanent_address,
            "is_active" => $user->userActivity->is_active,
            "roles" => $roles,
            "is_verified" => $user->userActivity->is_verified,
        ];
        if ($user) {
            return response()->json(
                [
                    "status" => 200,
                    'message' => $user,
                ],
            );
        } else {
            return response()->json(
                [
                    "status" => 404,
                    "message" => (int) $id . ' not found',
                ],
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
        $user = User::where('id', $id)->first();
        $user = [
            "id" => $user->id,
            "username" => $user->username,
            "email" => $user->email,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "date_of_birth" => $user->date_of_birth,
            "phone" => $user->phone,
            "NID" => $user->NID,
            "present_address" => $user->userAddress->present_address,
            "permanent_address" => $user->userAddress->permanent_address,
            "created_at" => $user->created_at,
            "updated_at" => $user->updated_at,
        ];
        if ($user) {
            return response()->json(
                [
                    "status" => 200,
                    'message' => $user,
                ]
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
            'email' => 'required|max:255',
            'date_of_birth' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'nid' => 'required',
            'phone' => 'required',
            'present_address' => 'required',
            'permanent_address' => 'required',
        ],
        [
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
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "status" => 400,
                    'message' => [$validator->errors()],
                ],
            );
        }
        $user = User::find($id);
        $user->date_of_birth = $request->date_of_birth;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->NID = $request->nid;
        $user->phone = $request->phone;

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
                    "status" => 200,
                    'message' => $user,
                ]
            );
        } else {
            return response()->json(
                [
                    "status" => 404,
                    'message' => 'User not found',
                ],
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

    /**
     * Get the specified resource from storage for login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
                $token = Str::random(60);
                $user->forceFill([
                    'api_token' => hash('sha256', $token),
                ])->save();
                return response()->json(
                    $user->api_token,
                    200
                );
            } else {
                return response()->json(
                    [
                        'err' => 'Invalid Username or Email',
                    ],
                    400
                );
            }
        } else {
            return response()->json(
                ["err"=>'Invalid Username or Email'],
                404
            );
        }
    }

    /**
     * Get the specified resource from session for logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request){
        $user = User::where('api_token', $request->input("token"))->first();
        $user->api_token = null;
        $user->save();
        return response()->json(
            [
                "status" => 200,
                'message' => 'User logged out successfully',
            ]
        );
    }

    /**
     * Get the specified resource from session for profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request){
        $user = User::where("api_token", $request->input("api_token"))->first();
        $user = [
            "id" => $user->id,
            "username" => $user->username,
            "email" => $user->email,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "date_of_birth" => $user->date_of_birth,
            "phone" => $user->phone,
            "NID" => $user->NID,
            "present_address" => $user->userAddress->present_address,
            "permanent_address" => $user->userAddress->permanent_address,
            "created_at" => $user->created_at,
            "updated_at" => $user->updated_at,
        ];
         if($user){
              return response()->json(
                [
                    "status" => 200,
                    'message' => $user,
                ]
              );
        }
    }

    /**
     * Get the specified resource from storage for edit.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'date_of_birth' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'nid' => 'required',
            'phone' => 'required',
            'present_address' => 'required',
            'permanent_address' => 'required',
        ],
        [
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
                    'status' => 400,
                    'message' => [$validator->errors()],
                ],
            );
        }

        $user = User::find($id);
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
                    'status' => 200,
                    'message' => 'User updated successfully',
                ]
            );
        } else {
            return response()->json(
                [
                    'status' => 500,
                    'message' => 'User updating failed',
                ]
            );
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required',
            'confirm_password' => 'required',
        ],
        [
            'new_password.required' => 'New password is required',
            'confirm_password.required' => 'Confirm password is required',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "status" => 400,
                    'message' => [$validator->errors()],
                ],
            );
        }

        $user = User::where("api_token",$request->input("api_token"));
            if ($request->new_password == $request->confirm_password) {
                $user->update(
                    [
                        'password' => bcrypt($request->new_password),
                    ]
                );
                return response()->json(
                    [
                        "status" => 200,
                        'message' => 'Password changed successfully',
                    ]
                );
            } else {
                return response()->json(
                    [
                        "status" => 400,
                        'message' => 'New password and confirm password does not match',
                    ]
                );
            }
    }

    public function check(Request $request)
    {
        $user = User::where("api_token",$request->input("api_token"))->first();
        $roles = array();

        foreach($user->userRoles as $role){
            array_push($roles,$role->name);
        }

        if($user){
            return response()->json(
                [
                    "status" => 200,
                    'message' => $roles,
                ]
            );
        }else{
            return response()->json(
                [
                    "status" => 400,
                    'message' => 'User not logged in',
                ]
            );
        }
    }
}
