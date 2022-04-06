<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    // this api is for to register the student
    public function register(Request $request)
    {
    	//Validate data
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:students',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $user = Student::create([
        	'name' => $request->name,
        	'email' => $request->email,
        	'password' => bcrypt($request->password)
        ]);

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }

    // this api is for to login the student
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                	'success' => false,
                	'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'Could not create token.',
                ], 500);
        }
 	
 		//Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }
    // this api is for to logout the student
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

		//Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // this api is for get Student data
    public function get_user(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        try {
            if ($user = JWTAuth::authenticate($request->token)) {
                $user->course;
                return response()->json([
                	'data' =>$user,
                    'success' => true,
                    'message' => 'Data Found Successfully',
                    'status'=>200
                ]);
            }
        } catch (JWTException $e) {
    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'User Not found',
                ], 500);
        }
    }
    // this api is for get all course
    public function getcourse()
    { 
        $response = Course::where(['status' => 1])->get();
        if (count($response)>0) {
            return response()->json([
                'data' =>$response,
                'success' => true,
                'message' => 'Data Found Successfully',
                'status'=>200
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'No Data Found'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    // this api is for update Student data
    public function updateUser(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token','name','course_id'), [
            'token' => 'required',
            'name'=>'required',
            'course_id'=>'required',
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        try {
            if ($user = JWTAuth::authenticate($request->token)) {
                $updatedata['name']=$request->name;
                $updatedata['course_id']=$request->course_id;
                $res = Student::where(['id'=>$user->id])->update($updatedata);
                $user = JWTAuth::authenticate($request->token);
                return response()->json([
                	'data' =>$user,
                    'success' => true,
                    'message' => 'User Updated Successfully',
                    'status'=>200
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'User Not Updated',
                ]);
            }
        } catch (JWTException $e) {
    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'User Not found',
                ], 500);
        }
    }
}