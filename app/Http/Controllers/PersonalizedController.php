<?php

namespace App\Http\Controllers;

use App\Models\Notifications;
use App\Models\UserInterest;
use App\Models\Setting;
use Illuminate\Http\Request;

class PersonalizedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($status = null)
    {

        if (!has_permissions('read', 'property_inquiry')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {

            if ($status) {
                $status = $status;
            } else {
                $status = '';
            }
            $firebase_settings = array();

            $operate = '';

            $firebase_settings['apiKey'] = system_setting('apiKey');
            $firebase_settings['authDomain'] = system_setting('authDomain');
            $firebase_settings['projectId'] = system_setting('projectId');
            $firebase_settings['storageBucket'] = system_setting('storageBucket');
            $firebase_settings['messagingSenderId'] = system_setting('messagingSenderId');
            $firebase_settings['appId'] = system_setting('appId');
            $firebase_settings['measurementId'] = system_setting('measurementId');
            // }

            // return view('home', compact('list', 'firebase_settings'));
            return view('personalized.index', compact('status', 'firebase_settings'));
        }
    }

    public function show($id = null)
    {

        return view('personalized.index');
    }


    public function getPersonalizedList()
    {
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'DESC';

        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }

        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }

        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        $sql = UserInterest::with('customer')->orderBy($sort, $order);
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql =  $sql->where('id', 'LIKE', "%$search%")->orwhereHas('customer', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
            });
        }
        $total = $sql->count();
        if (isset($_GET['limit'])) {
            $sql->skip($offset)->take($limit);
        }
        $res = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;
        foreach ($res as $row) {
            $tempRow['id'] = $row->id;
            $tempRow['property_owner'] = $row->user_id;
            $tempRow['property_mobile'] = $row->category_ids;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
}
