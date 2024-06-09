<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\UserPurchasedPackage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $package = Package::select('id', 'name')->where('status', 1)->get();
        return view('customer.index', compact('package'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (!has_permissions('delete', 'customer')) {
            $response['error'] = true;
            $response['message'] = PERMISSION_ERROR_MSG;
            return response()->json($response);
        } elseif ($request->agent == 1){
            Customer::where('id', $request->id)->update(['role' => $request->status]);
            $response['error'] = false;
            return response()->json($response);
        } elseif ($request->verified == 1){
            Customer::where('id', $request->id)->update(['isVerified' => $request->status]);
            $response['error'] = false;
            return response()->json($response);
        } else {
            Customer::where('id', $request->id)->update(['isActive' => $request->status]);
            $response['error'] = false;
            return response()->json($response);
        }
    }

    public function customerList(Request $request)
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



        $sql = Customer::orderBy($sort, $order)->when($request->id,fn($q)=>$q->whereId($request->id));


        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
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

        $status = '';
        $operate = '';
        $enable_disable = '';
        foreach ($res as $row) {
            $tempRow['id'] = $row->id;
            $tempRow['name'] = $row->name;
            $tempRow['email'] = $row->email;
            $tempRow['mobile'] = $row->mobile;
            $tempRow['address'] = $row->address;
            $tempRow['firebase_id'] = $row->firebase_id;
            $tempRow['isActive'] = ($row->isActive == '0') ? '<span class="badge rounded-pill bg-danger">'.__('InActive').'</span>' : '<span class="badge rounded-pill bg-success">'.__('Active').'</span>';
            $tempRow['profile'] = ($row->profile != '') ? '<a class="image-popup-no-margins" href="' . $row->profile . '" width="55" height="55"><img class="rounded avatar-md shadow img-fluid" alt="" src="' . $row->profile . '" width="55" height="55"></a>' : '';

            $tempRow['fcm_id'] = $row->fcm_id;

            $isActive = $row->isActive == '1' ? 'checked' : '';

            $enable_disable =   '<div class="form-check form-switch " style="justify-content: center;display: flex;">
                <input class="form-check-input switch1" name="' . $row->id . '"  onclick="chk(this);" type="checkbox" role="switch" ' . $isActive . '>
            </div>';

            $operate = '<a  id="' . $row->id . '"  class="btn icon btn-primary btn-sm rounded-pill" data-subscription="' . $row->subscription . '" data-oldimage="' . $row->image . '" data-types="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#editModal"  onclick="setValue(this.id);" title="Edit Package"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';

            $isAgent = $row->role == '1' ? 'checked' : '';

            $role =   '<div class="form-check form-switch " style="justify-content: center;display: flex;">
                <input class="form-check-input switch2" name="' . $row->id . '"  onclick="chk1(this);" type="checkbox" role="switch" ' . $isAgent . '>
            </div>';

            $isVerified = $row->isVerified == '1' ? 'checked' : '';

            $verified =   '<div class="form-check form-switch " style="justify-content: center;display: flex;">
                <input class="form-check-input switch3" name="' . $row->id . '"  onclick="chk2(this);" type="checkbox" role="switch" ' . $isVerified . '>
            </div>';

            $tempRow['customertotalpost'] =  '<a href="' . url('property') . '?customer=' . $row->id . '">' . $row->customertotalpost . '</a>';
            $tempRow['role'] = $role;
            $tempRow['verified'] = $verified;

            $userPurchasedPackage = UserPurchasedPackage::where('modal_id', (string)$row->id)->latest()->first();

            if($userPurchasedPackage){
                $package = Package::where('id', $userPurchasedPackage->package_id)->first();
                $tempRow['subscription_startdate'] = $userPurchasedPackage->start_date;
                $tempRow['subscription_enddate'] = $userPurchasedPackage->end_date;
                $subscriptionAnchor = "<a href='javascript:' data-bs-toggle='modal' data-subscription='" . $row->subscription . "' data-oldimage='" . $row->image . "' data-types='" . $row->id . "'  data-bs-target='#editModal'  onclick='setValue(this.id);' title='Edit Package'>" . $package->name . "</a>";
                $tempRow['subscription'] = $subscriptionAnchor; // . ' (' . $package->property_limit . ')';
            }else{
                $tempRow['subscription_startdate'] = '';
                $tempRow['subscription_enddate'] = '';
                $tempRow['subscription'] = '';
            }


            $tempRow['enble_disable'] = $enable_disable;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function updatePackage(Request $request)
    {
        $package = Package::find($request->edit_user_package);
        $userPurchasedPackage = UserPurchasedPackage::where('modal_id', $request->id)->first();
        $startDate = $userPurchasedPackage?->start_date ?? Carbon::now()->format('Y-m-d');
        $purchaseExpired = $userPurchasedPackage?->end_date > Carbon::now() ? false : true;

        if (!has_permissions('delete', 'customer')) {
            return back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            dd($request->id);
            Customer::where('id', $request->id)->update(['subscription' => 1]);
            UserPurchasedPackage::where('modal_id', $request->id)->delete();
            UserPurchasedPackage::create([
                'modal_id' => $request->id,
                'package_id' => $request->edit_user_package,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($package->duration),
            ]);

            switch ($request->input('action')) {
                case 'renew':
                    return back()->with('success', 'Customer Package Updated Successfully');
                    break;

                case 'change':
                    return back()->with('success', 'Customer Package Changed Successfully');
                    break;
            }
        }
    }
}
