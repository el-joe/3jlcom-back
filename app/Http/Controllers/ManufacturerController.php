<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use App\Models\Type;
use Illuminate\Http\Request;


class ManufacturerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'manufacturers')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            return view('manufacturers.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!has_permissions('create', 'manufacturers')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'image' => 'required|image|mimes:jpg,png,jpeg,svg|max:2048',
                'manufacturer' => 'required',
                'manufacturer_ar' => 'required'
            ]);
            $saveManufacturers = new Manufacturer();
            $destinationPath = public_path('images') . config('global.MANUFACTURER_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            // image upload


            if ($request->hasFile('image')) {
                $profile = $request->file('image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPath, $imageName);
                $saveManufacturers->image = $imageName;
            } else {
                $saveManufacturers->image  = '';
            }

            $saveManufacturers->manufacturer = ($request->manufacturer) ? $request->manufacturer : '';
            $saveManufacturers->manufacturer_ar = ($request->manufacturer_ar) ? $request->manufacturer_ar : '';
            $saveManufacturers->save();

            return back()->with('success', 'Manufacturer Successfully Added');
        }
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
        
        if (!has_permissions('update', 'manufacturers')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            
            $id =  $request->edit_id;
            $old_image =  $request->old_image;
            $Manufacturer = Manufacturer::find($id);
            
            $destinationPath = public_path('images') . config('global.MANUFACTURER_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }


            if ($request->hasFile('edit_image')) {
                $profile = $request->file('edit_image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPath, $imageName);
                $Manufacturer->image = $imageName;

                if (is_file(public_path('images') . config('global.MANUFACTURER_IMG_PATH') . $old_image)) {
                    unlink(public_path('images') . config('global.MANUFACTURER_IMG_PATH') . $old_image);
                }
            }
            // else {
            //     $Manufacturer->image  = $old_image;
            // }

            $Manufacturer->manufacturer = ($request->edit_manufacturer) ? $request->edit_manufacturer : '';
            $Manufacturer->manufacturer_ar = ($request->edit_manufacturer_ar) ? $request->edit_manufacturer_ar : '';

            $Manufacturer->sequence = ($request->sequence) ? $request->sequence : 0;

            $Manufacturer->update();

            return back()->with('success', 'Manufacturer Successfully Update');
        }
    }



    public function manufacturerList()
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



        $sql = Manufacturer::orderBy($sort, $order);
        
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('manufacturer', 'LIKE', "%$search%");
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


        $operate = '';
        foreach ($res as $row) {
            $tempRow['id'] = $row->id;
            $tempRow['manufacturer'] = $row->manufacturer;
            $tempRow['manufacturer_ar'] = $row->manufacturer_ar;
            $tempRow['status'] = ($row->status == '0') ? '<span class="badge rounded-pill bg-danger">'.__('InActive').'</span>' : '<span class="badge rounded-pill bg-success">'.__('Active').'</span>';
            $tempRow['image'] = ($row->image != '') ? '<a class="image-popup-no-margins" href="' . $row->image . '"><img class="rounded avatar-md shadow img-fluid" alt="" src="' . $row->image . '" width="55"></a>' : '';

            $operate = '&nbsp;&nbsp;<a  id="' . $row->id . '"  class="btn icon btn-primary btn-sm rounded-pill" data-status="' . $row->status . '" data-oldimage="' . $row->image . '" data-bs-toggle="modal" data-bs-target="#editModal"  onclick="setValue(this.id);" title="Edit"><i class="fa fa-edit"></i></a>';

            $status = $row->status == '1' ? 'checked' : '';
            $enable_disable =   '<div class="form-check form-switch " style="justify-content: center;display: flex;">
         <input class="form-check-input switch1" id="' . $row->id . '"  onclick="chk(this);" type="checkbox" role="switch" ' . $status . '>

            </div>';

            $tempRow['enable_disable'] = $enable_disable;

            $tempRow['operate'] = $operate;

            $rows[] = $tempRow;
            $count++;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }



    public function updateManufacturer(Request $request)
    {
        if (!has_permissions('delete', 'manufacturers')) {
            $response['error'] = true;
            $response['message'] = PERMISSION_ERROR_MSG;
            return response()->json($response);
        } else {

            Manufacturer::where('id', $request->id)->update(['status' => $request->status]);
            $response['error'] = false;
            return response()->json($response);
        }
    }
}
