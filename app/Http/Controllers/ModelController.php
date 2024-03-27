<?php

namespace App\Http\Controllers;

use App\Models\Modell;
use App\Models\Manufacturer;
use App\Models\Type;
use Illuminate\Http\Request;


class ModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'models')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $manufacturer = Manufacturer::select('id', 'manufacturer')->where('status', 1)->get();
            return view('models.index', compact('manufacturer'));
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
        if (!has_permissions('create', 'models')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'model' => 'required',
                'manufacturer' => 'required'
            ]);
            $saveModels = new Modell();

            $saveModels->model = ($request->model) ? $request->model : '';
            $saveModels->save();

            return back()->with('success', 'Model Successfully Added');
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
        
        if (!has_permissions('update', 'models')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            
            $id =  $request->edit_id;
            $Model = Modell::find($id);

            $Model->model = ($request->edit_model) ? $request->edit_model : '';

            $Model->manufacturer_id = ($request->edit_manufacturer) ? $request->edit_manufacturer : '';

            $Model->update();

            return back()->with('success', 'Model Successfully Update');
        }
    }



    public function modelList()
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



        $sql = Modell::with('manufacturer')->orderBy($sort, $order);
        
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('model', 'LIKE', "%$search%");
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
        $tempRow['type'] = '';
        foreach ($res as $row) {
            $tempRow['id'] = $row->id;
            $tempRow['model'] = $row->model;
            $tempRow['manufacturer'] =  (!empty($row->manufacturer)) ? $row->manufacturer->manufacturer : '';
            $tempRow['status'] = ($row->status == '0') ? '<span class="badge rounded-pill bg-danger">'.__('InActive').'</span>' : '<span class="badge rounded-pill bg-success">'.__('Active').'</span>';
            
            $operate = '&nbsp;&nbsp;<a  id="' . $row->id . '"  class="btn icon btn-primary btn-sm rounded-pill" data-status="' . $row->status . '" data-bs-toggle="modal" data-bs-target="#editModal"  onclick="setValue(this.id);" title="Edit"><i class="fa fa-edit"></i></a>';

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



    public function updateModel(Request $request)
    {
        if (!has_permissions('delete', 'models')) {
            $response['error'] = true;
            $response['message'] = PERMISSION_ERROR_MSG;
            return response()->json($response);
        } else {

            Modell::where('id', $request->id)->update(['status' => $request->status]);
            $response['error'] = false;
            return response()->json($response);
        }
    }
}
