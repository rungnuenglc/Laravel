<?php

namespace App\Http\Controllers;

use App\car;
use Illuminate\Http\Request;
use App\Userbase;
use App\userCar;

class MyuserController extends Controller
{
    public function registerpage()
    {
        return view('admin.registerpage');
    }

    public function loginpage()
    {
        return view('admin.loginpage');
    }

    public function saveregisterpage(Request $request)
    {
        $userbase = new Userbase;
        $userbase->Username = $request->input('Username');
        $userbase->Password = $request->input('Password');
        $userbase->Email = $request->input('Email');
        $userbase->Birth = $request->input('Birth');
        $userbase->Gender = $request->input('Gender');
        $userbase->save();
        return redirect('/login');
    }

    public function checklogin(Request $request)
    {
        $Username = $request->input('Username');
        $Password = $request->input('Password');
        //Check Found User
        $userRecord = Userbase::where('Username', $Username)
            ->where('Password', $Password)->first();
        $userRecord['BirthShow'] = substr($userRecord['Birth'],0,10);
//        dd($userRecord);
        if (!empty($userRecord)) {
            $request->session()->put('userinfo', $userRecord);//SESSION
            return redirect('/profile');
        } else {
            return view('admin.loginpage');
        }
    }

    public function profilepage(Request $request)
    {
        $userinfo = $request->session()->get('userinfo');
        $carList = car::all();
        $carUserListData = array();
        foreach ($carList as $key => $val) {
            $carUserListData[$key]['car'] = $val['car'];
            $carUserListData[$key]['carID'] = $val['carID'];
            $carUserRecord = userCar::where('userID', $userinfo['UserID'])->where('carID', $val['carID'])->first();
            $carUserListData[$key]['Checked'] = '';
            if (!empty($carUserRecord)) {
                $carUserListData[$key]['Checked'] = 'checked';
            }
        }
        $data = array(
            'carUserListData' => $carUserListData,
            'userinfo' => $userinfo
        );
        return view('admin.profilepage')->with($data);
    }

    public function saverefcar(Request $request)
    {
        $userinfo = $request->session()->get('userinfo');
        //DELETE
        userCar::where('userID', $userinfo['UserID'])->delete();
        /// SAVE CarRef
        foreach ($request->input('carList') as $key => $val) {
            $userCar = new userCar;
            $userCar->userID = $userinfo['UserID'];
            $userCar->CarID = $val;
            $userCar->save();
        }
        return redirect('/profile');
    }

    public function saveprofile(Request $request)
    {
        if (!empty($request->input('id'))) {
            //EDIT
//            $userinfo = $request->session()->get('userinfo');
//            userCar::where('userID',$userinfo['UserID'])->delete();
//            foreach($request->input('carList') as $key=>$val){
//                $userCar = new userCar;
//                $userCar->userID = $userinfo['UserID'];
//                $userCar->CarID = $val;
//                $userCar->save();
//            }
            $this->saverefcar($request);
            $userbase = new Userbase;

            $dataUpdate = array(
                'Username' => $request->input('Username'),
                'Password' => $request->input('Password'),
                'Email' => $request->input('Email'),
                'Birth' => $request->input('Birth'),
                'Gender' => $request->input('Gender'),
            );
            $userbase->where('UserID', $request->input('id'))->update($dataUpdate);
            //SELECT Last
            $userRecord = Userbase::where('UserID', $request->input('id'))->first();
            $request->session()->put('userinfo', $userRecord);//SESSION
            return redirect('/editprofile');
        }
    }

    public function editprofile(Request $request)
    {
        $userinfo = $request->session()->get('userinfo');
        //SET DATE
//        $userinfo['Birth'] = substr($userinfo['Birth'],0,10);
        $carList = car::all();
        $carUserListData = array();
        foreach ($carList as $key => $val) {
            $carUserListData[$key]['car'] = $val['car'];
            $carUserListData[$key]['carID'] = $val['carID'];
            $carUserRecord = userCar::where('userID', $userinfo['UserID'])->where('carID', $val['carID'])->first();
            $carUserListData[$key]['Checked'] = '';
            if (!empty($carUserRecord)) {
                $carUserListData[$key]['Checked'] = 'checked';
            }
        }
        $data = array(
            'carUserListData' => $carUserListData,
            'userinfo' => $userinfo
        );
//       dd($userinfo);
        return view('admin.editprofilepage')->with($data);
    }

}
