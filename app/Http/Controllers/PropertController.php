<?php

namespace App\Http\Controllers;

use App\Models\AssignParameters;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Modell;
use App\Models\Year;
use App\Models\City;
use App\Models\Area;
use App\Models\Chats;
use App\Models\Customer;
use App\Models\Notifications;
use App\Models\parameter;
use App\Models\Property;
use App\Models\PropertyImages;
use App\Models\PropertysInquiry;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Type;
use App\Models\Usertokens;
use App\Models\UserInterest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use kornrunner\Blurhash\Blurhash;
use Intervention\Image\ImageManagerStatic as Image;
//use Carbon\Carbon;

class PropertController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $category = Category::where('status', '1')->get();
            $manufacturer = Manufacturer::all();
            $model = Modell::all();
            $year = Year::all();
            $city = City::all();
            $area = Area::all();

            return view('property.index', compact('category','manufacturer','model','year','city','area'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!has_permissions('create', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $customer = Customer::where('isActive', '1')->get();
            $category = Category::where('status', '1')->get();
            $manufacturer = Manufacturer::where('status', '1')->get();
            $model = Modell::where('status', '1')->get();
            $year = Year::where('status', '1')->get();
            $city = City::where('status', '1')->get();
            $area = Area::where('status', '1')->get();
            $country = get_countries_from_json();
            $parameters = parameter::all();
            $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

            //dd($category);
            return view('property.create', compact('customer','category','manufacturer','model','year','city','area', 'country', 'parameters', 'currency_symbol'));
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
        $arr = [];

        if (!has_permissions('read', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $request->validate([
                'gallery_images.*' => 'required|image|mimes:jpg,png,jpeg|max:2048',
                'title_image.*' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            ]);

            $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }


            $destinationPathFor3d = public_path('images') . config('global.3D_IMG_PATH');
            if (!is_dir($destinationPathFor3d)) {
                mkdir($destinationPathFor3d, 0777, true);
            }
            $Saveproperty = new Property();

            $Saveproperty->category_id = $request->category;
            $Saveproperty->manufacturer_id = $request->manufacturer;
            $Saveproperty->model_id = $request->model;
            $Saveproperty->year_id = $request->year;
            $Saveproperty->city_id = $request->city;
            $Saveproperty->area_id = $request->area;

            $Saveproperty->title = $request->title;
            $Saveproperty->description = $request->description;
            $Saveproperty->address = $request->address ?? "";
            $Saveproperty->client_address = $request->client_address ?? "";
            $Saveproperty->propery_type = 0;
            $Saveproperty->price = $request->price;
            $Saveproperty->package_id = 0;
            $Saveproperty->status = 1;
            $Saveproperty->city_name = (isset($request->city_name)) ? $request->city_name : '';
            $Saveproperty->country = (isset($request->country)) ? $request->country : '';
            $Saveproperty->state = (isset($request->state)) ? $request->state : '';
            $Saveproperty->latitude = (isset($request->latitude)) ? $request->latitude : '0';
            $Saveproperty->longitude = (isset($request->longitude)) ? $request->longitude : '0';
            $Saveproperty->video_link = (isset($request->video_link)) ? $request->video_link : '';
            $Saveproperty->post_type = 0;
            $Saveproperty->added_by = (isset($request->customer)) ? $request->customer : 0;

            if ($request->hasFile('title_image')) {
                $profile = $request->file('title_image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPath, $imageName);
                $Saveproperty->title_image = $imageName;
                // $Saveproperty->title_image_hash = $image_hash;
            } else {
                $Saveproperty->title_image  = '';
            }
            if ($request->hasFile('3d_image')) {

                $profile = $request->file('3d_image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPathFor3d, $imageName);
                $Saveproperty->threeD_image = $imageName;
            } else {
                $Saveproperty->threeD_image  = '';
            }


            $Saveproperty->save();

            $parameters = parameter::all();
            foreach ($parameters as $par) {

                if ($request->has($par->id)) {
                    // echo "in";
                    $assign_parameter = new AssignParameters();
                    $assign_parameter->parameter_id = $par->id;

                    if (($request->hasFile($par->id))) {


                        $destinationPath = public_path('images') . config('global.PARAMETER_IMG_PATH');
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        $imageName = microtime(true) . "." . ($request->file($par->id))->getClientOriginalExtension();
                        ($request->file($par->id))->move($destinationPath, $imageName);
                        $assign_parameter->value = $imageName;
                    } else {
                        $assign_parameter->value = is_array($request->input($par->id)) ? json_encode($request->input($par->id), JSON_FORCE_OBJECT) : ($request->input($par->id));

                        // $assign_parameter->value = is_array($request->input($par->id)) ? implode(',', ($request->input($par->id))) : ($request->input($par->id));
                    }


                    $assign_parameter->modal()->associate($Saveproperty);

                    $assign_parameter->save();

                    $arr = $arr + [$par->id => $request->input($par->id)];
                }
            }

            /// START :: UPLOAD GALLERY IMAGE

            $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
            if (!is_dir($FolderPath)) {
                mkdir($FolderPath, 0777, true);
            }


            $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $Saveproperty->id;
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            if ($request->hasfile('gallery_images')) {
                // dd("in");
                foreach ($request->file('gallery_images') as $file) {



                    $name = time() . rand(1, 100) . '.' . $file->extension();
                    $file->move($destinationPath, $name);

                    PropertyImages::create([
                        'image' => $name,
                        'propertys_id' => $Saveproperty->id
                    ]);
                }
            }
            /// END :: UPLOAD GALLERY IMAGE


            return back()->with('success', 'Successfully Added');
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
        if (!has_permissions('update', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $category = Category::all()->where('status', '1')->mapWithKeys(function ($item, $key) {
                return [$item['id'] => $item['category']];
            });

            $customer = Customer::where('isActive', '1')->get();
            $category = Category::where('status', '1')->get();
            $manufacturer = Manufacturer::where('status', '1')->get();
            $model = Modell::where('status', '1')->get();
            $year = Year::where('status', '1')->get();
            $city = City::where('status', '1')->get();
            $area = Area::where('status', '1')->get();
            $parameters = parameter::all();


            $list = Property::with('assignParameter.parameter')->where('id', $id)->get()->first();

            $country = get_countries_from_json();
            $state = get_states_from_json($list->country);

            $arr = json_decode($list->carpet_area);
            $par_arr = [];
            $par_id = [];
            $type_arr = [];
            // dd($arr);
            foreach ($list->assignParameter as  $par) {
                $par_arr = $par_arr + [$par->parameter->name => $par->value];
                $par_id = $par_id + [$par->parameter->name => $par->value];
            }
            $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

            return view('property.edit', compact('customer','category','manufacturer','model','year','city','area', 'state', 'country', 'list', 'id', 'par_arr', 'parameters', 'par_id', 'currency_symbol'));
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
        // dd($request->toArray());
        if (!has_permissions('update', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {

            $arr = [];

            $UpdateProperty = Property::with('assignparameter.parameter')->find($id);
            // dd($UpdateProperty->toArray());


            $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }


            $UpdateProperty->category_id = $request->category;
            $UpdateProperty->manufacturer_id = $request->manufacturer;
            $UpdateProperty->model_id = $request->model;
            $UpdateProperty->year_id = $request->year;
            $UpdateProperty->city_id = $request->city;
            $UpdateProperty->area_id = $request->area;

            $UpdateProperty->title = $request->title;
            $UpdateProperty->description = $request->description;
            $UpdateProperty->address = $request->address ?? "";
            $UpdateProperty->client_address = $request->client_address ?? "";
            $UpdateProperty->propery_type = 0;
            $UpdateProperty->price = $request->price;
            $UpdateProperty->added_by = $request->customer ?? 0;
            $UpdateProperty->state = (isset($request->state)) ? $request->state : '';
            $UpdateProperty->country = (isset($request->country)) ? $request->country : '';
            $UpdateProperty->city_name = (isset($request->city_name)) ? $request->city_name : '';
            $UpdateProperty->latitude = (isset($request->latitude)) ? $request->latitude : '0';
            $UpdateProperty->longitude = (isset($request->longitude)) ? $request->longitude : '0';
            $UpdateProperty->video_link = (isset($request->video_link)) ? $request->video_link : '';


            if ($request->hasFile('title_image')) {



                $profile = $request->file('title_image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPath, $imageName);

                if ($UpdateProperty->title_image != '') {
                    if (file_exists(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') .  $UpdateProperty->title_image)) {
                        unlink(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $UpdateProperty->title_image);
                    }
                }
                $UpdateProperty->title_image = $imageName;
            }
            if ($request->title_image_length == 0 && isset($request->title_image_length)) {
                $UpdateProperty->title_image = '';
            }
            $destinationPathFor3d = public_path('images') . config('global.3D_IMG_PATH');
            if (!is_dir($destinationPathFor3d)) {
                mkdir($destinationPathFor3d, 0777, true);
            }
            if ($request->hasFile('3d_image')) {


                $profile = $request->file('3d_image');
                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                $profile->move($destinationPathFor3d, $imageName);


                if ($UpdateProperty->threeD_image != '') {
                    if (file_exists(public_path('images') . config('global.3D_IMG_PATH') .  $UpdateProperty->threeD_image)) {
                        unlink(public_path('images') . config('global.3D_IMG_PATH') . $UpdateProperty->threeD_image);
                    }
                }
                $UpdateProperty->threeD_image = $imageName;
            }
            if ($request->_3d_image_length == 0 && isset($request->_3d_image_length)) {
                $UpdateProperty->threeD_image = '';
            }
            $UpdateProperty->update();




            $parameters = parameter::all();
            AssignParameters::where('modal_id', $id)->delete();

            foreach ($parameters as $par) {

                // dd($request->toArray());

                if ($request->has($par->id)) {


                    $update_parameter = new AssignParameters();

                    $update_parameter->parameter_id = $par->id;


                    if (($request->hasFile($par->id))) {


                        $destinationPath = public_path('images') . config('global.PARAMETER_IMG_PATH');
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        $imageName = microtime(true) . "." . ($request->file($par->id))->getClientOriginalExtension();
                        ($request->file($par->id))->move($destinationPath, $imageName);
                        $update_parameter->value = $imageName;
                    } else {

                        $update_parameter->value = is_array($request->input($par->id)) ? json_encode($request->input($par->id), JSON_FORCE_OBJECT) : ($request->input($par->id));
                    }



                    $update_parameter->modal()->associate($UpdateProperty);

                    $update_parameter->save();
                }
            }

            /// START :: UPLOAD GALLERY IMAGE

            $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
            if (!is_dir($FolderPath)) {
                mkdir($FolderPath, 0777, true);
            }


            $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $UpdateProperty->id;
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            if ($request->hasfile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $name = time() . rand(1, 100) . '.' . $file->extension();
                    $file->move($destinationPath, $name);

                    PropertyImages::create([
                        'image' => $name,
                        'propertys_id' => $UpdateProperty->id
                    ]);
                }
            }

            /// END :: UPLOAD GALLERY IMAGE

            return redirect('property')->with('success', 'Successfully Update');
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
        if (env('DEMO_MODE')) {
            return redirect()->back()->with('error', 'demo');
        }
        if (!has_permissions('delete', 'property')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $property = Property::find($id);

            if ($property->delete()) {

                $chat = Chats::where('property_id', $property->id);
                if ($chat) {
                    $chat->delete();
                }

                $enquiry = PropertysInquiry::where('propertys_id', $property->id);
                if ($enquiry) {
                    $enquiry->delete();
                }

                $slider = Slider::where('propertys_id', $property->id);
                if ($slider) {
                    $slider->delete();
                }


                $notifications = Notifications::where('propertys_id', $property->id);
                if ($notifications) {
                    $notifications->delete();
                }


                if ($property->title_image != '') {
                    if (file_exists(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image)) {
                        unlink(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image);
                    }
                }
                foreach ($property->gallery as $row) {
                    if (PropertyImages::where('id', $row->id)->delete()) {
                        if ($row->image_url != '') {
                            if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image)) {
                                unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image);
                            }
                        }
                    }
                }
                // rmdir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id);
                Notifications::where('propertys_id', $id)->delete();
                return back()->with('success', 'Property Deleted Successfully');
            } else {
                return back()->with('error', 'Something Wrong');
            }
        }
    }

    public function getPropertyList()
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

        $sql = Property::with('category','manufacturer','model','year','city','area')->with('customer')->with('assignParameter.parameter')->with('interested_users')->orderBy($sort, $order);

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql = $sql->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%")->orwhere('address', 'LIKE', "%$search%")->orwhereHas('customer', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%")->orwhere('mobile', 'LIKE', "%$search%");
            });
        }

        if ($_GET['status'] != '' && isset($_GET['status'])) {
            $status = $_GET['status'];
            $sql = $sql->where('status', $status);
        }
        if ($_GET['type'] != '' && isset($_GET['type'])) {
            $type = $_GET['type'];
            $sql = $sql->where('propery_type', $type);
        }

        if ($_GET['customer_id'] != '' && isset($_GET['customer_id'])) {
            $customer_id = $_GET['customer_id'];
            $sql = $sql->where('added_by', $customer_id);
        }

        if ($_GET['category'] != '' && isset($_GET['category'])) {
            $category_id = $_GET['category'];
            $sql = $sql->where('category_id', $category_id);
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

            if (has_permissions('update', 'property')) {
                $operate = '<a  href="' . route('property.edit', $row->id) . '"  class="btn icon btn-primary btn-sm rounded-pill m-1" title="Edit"><i class="fa fa-edit"></i></a>';
            }
            $operate1 = '<a  id="' . $row->id . '"  class="btn icon btn-primary btn-sm rounded-pill" data-status="' . $row->status . '" data-oldimage="' . $row->image . '" data-types="" data-bs-toggle="modal" data-bs-target="#editModal"  onclick="setValue(this.id);" title="Edit"><i class="bi bi-eye-fill"></i></a>';

            $url = urlencode("https://3jlcom.com/properties-deatils/$row->id");

            $operate .= '<a href="#" onclick="copyURL(event,"'. $url .'")" data-code="' . $row->id . '" id="url" class="btn icon btn-secondary btn-sm rounded-pill m-1" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" title="Copy URL"><i class="fa fa-copy"></i></a>';
            // whatsapp anchor with bootstrap icon
            $mobilePlus = substr($row->customer[0]->mobile, 0, 1);
            $plus = ($mobilePlus === '+' ? '' : '+');
            $operate .= '<a target="_blank" href="https://wa.me/'. $plus . ($row->customer[0]?->mobile??'0') .'?text=' . $url . '" class="btn icon btn-success btn-sm rounded-pill m-1" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" title="Share"><i class="bi bi-whatsapp"></i></a>';

            if (has_permissions('delete', 'property')) {
                $operate .= '<a href="' . route('property.destroy', $row->id) . '" onclick="return confirmationDelete(event);" class="btn icon btn-danger btn-sm rounded-pill m-1" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" title="Delete"><i class="bi bi-trash"></i></a>';
            }

            $status = $row->status == '1' ? 'checked' : '';
            $enable_disable =   '<div class="form-check form-switch text-center">
                <input class="form-check-input switch1" id="' . $row->id . '"  onclick="chk(this);" type="checkbox" role="switch"' . $status . '>

            </div>';
            $interested_users = array();
            foreach ($row->interested_users as $interested_user) {

                if ($interested_user->property_id == $row->id) {

                    array_push($interested_users, $interested_user->customer_id);
                }
            }

            $tempRow['total_interested_users'] = count($interested_users);

            $tempRow['enble_disable'] = $enable_disable;

            $tempRow['id'] = $row->id;
            $tempRow['title'] = $row->title;
            $tempRow['category'] = isset($row->category->category) ? $row->category->category_ar : '';
            $tempRow['manufacturer'] = isset($row->manufacturer->manufacturer) ? $row->manufacturer->manufacturer : '';
            $tempRow['model'] = isset($row->model->model) ? $row->model->model : '';
            $tempRow['year'] = isset($row->year->year) ? $row->year->year : '';
            $tempRow['city'] = isset($row->city->city) ? $row->city->city_ar : '';
            $tempRow['area'] = isset($row->area->area) ? $row->area->area_ar : '';
            $tempRow['address'] = $row->address;
            $tempRow['client_address'] = $row->client_address;
            //$tempRow['created_at'] = Carbon::parse($row->created_at)->diffForHumans();
            $tempRow['created_at'] = ($row->created_at)->format('d/m/Y');


            if ($row->propery_type == 0) {
                $type = "Sell";
            } elseif ($row->propery_type == 1) {
                $type = "Rant";
            } elseif ($row->propery_type == 2) {
                $type = "Sold";
            } elseif ($row->propery_type == 3) {
                $type = "Rented";
            }

            $tempRow['total_click'] = $row->total_click;
            $tempRow['propery_type'] = $type;
            $tempRow['price'] = $row->price;
            $tempRow['title_image'] = ($row->title_image != '') ? '<a class="image-popup-no-margins" href="' . $row->title_image . '"><img class="rounded avatar-md shadow img-fluid" alt="" src="' . $row->title_image . '" width="55"></a>' : '';

            $tempRow['3d_image'] = ($row->threeD_image != '') ? '<a class="class="photo360" href="' . $row->threeD_image . '"><img class="rounded avatar-md shadow img-fluid" alt="" src="' . $row->threeD_image . '" width="55"></a>' : '';

            $tempRow['interested_users'] = $operate1;

            $tempRow['status'] = ($row->status == '0') ? '<span class="badge rounded-pill bg-danger">Inactive</span>' : '<span class="badge rounded-pill bg-success">Active</span>';

            //$customer = $row->customer->first();
            // $user = $row->user->first();

            if ($row->added_by != 0) {
                $customerName = $row->customer[0]->name ?? "";
                $tempRow['added_by'] =  "<a target='_blank' href='" . route('customer.index', ['id'=>$row->customer[0]->id]) . "'>" . $customerName . "</a>";
                $tempRow['mobile'] = $row->customer[0]->mobile ?? "";
            }
            if ($row->added_by == 0) {
                $tempRow['added_by'] = __('Admin');
                $tempRow['mobile'] = Setting::where('type', 'company_tel1')->pluck('data');
            }

            foreach ($row->interested_users as $interested_user) {

                if ($interested_user->property_id == $row->id) {

                    $tempRow['interested_users_details'] = Customer::Where('id', $interested_user->customer_id);
                    // array_push($interested_users, $interested_user->customer_id);
                    // $s .= $interested_user->user_id . ',';
                }
            }
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
            $count++;
        }
        // $cities =  json_decode(file_get_contents(public_path('json') . "/cities.json"), true);

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function updateStatus(Request $request)
    {
        if (!has_permissions('update', 'property')) {

            $response['error'] = true;
            $response['message'] = PERMISSION_ERROR_MSG;
            return response()->json($response);

        } else {

            Property::where('id', (string)$request->id)->update(['status' => $request->status]);

            $user_interests = UserInterest::all();

            if ($request->status == 1) {
                foreach ($user_interests as $user_interest) {
                    $property = Property::find($request->id);
                    $property_type = $request->property_type;

                    if ($user_interest->manufacturer_ids != '') {
                        $manufacturer_ids = explode(',', $user_interest->manufacturer_ids);
                        $property = $property->whereIn('manufacturer_id', $manufacturer_ids);
                    }

                    if ($user_interest->model_ids != '') {
                        $model_ids = explode(',', $user_interest->model_ids);
                        $property = $property->whereIn('model_id', $model_ids);
                    }

                    if ($user_interest->city_ids != '') {
                        $city_ids = explode(',', $user_interest->city_ids);
                        $property = $property->whereIn('city_id', $city_ids);
                    }

                    if ($user_interest->area_ids != '') {
                        $area_ids = explode(',', $user_interest->area_ids);
                        $property = $property->whereIn('area_id', $area_ids);
                    }

                    /*if ($user_interest->year_range != '') {

                        $max_year = explode(',', $user_interest->year_range)[1];
                        $min_year = explode(',', $user_interest->year_range)[0];

                        if (isset($max_year) && isset($min_year)) {

                            $property = $property->where(function ($query) use ($min_year, $max_year) {
                                $query->whereRaw("CAST(price AS DECIMAL(10, 2)) >= ?", [$min_year])
                                    ->whereRaw("CAST(price AS DECIMAL(10, 2)) <= ?", [$max_year]);
                            });
                        }
                    }

                    if ($user_interest->price_range != '') {

                        $max_price = explode(',', $user_interest->price_range)[1];
                        $min_price = explode(',', $user_interest->price_range)[0];

                        if (isset($max_price) && isset($min_price)) {
                            $min_price = floatval($min_price);
                            $max_price = floatval($max_price);

                            $property = $property->where(function ($query) use ($min_price, $max_price) {
                                $query->whereRaw("CAST(price AS DECIMAL(10, 2)) >= ?", [$min_price])
                                    ->whereRaw("CAST(price AS DECIMAL(10, 2)) <= ?", [$max_price]);
                            });
                        }
                    }*/

                    $total = $property->get()->count();
                    $fcm_tokens = array();

                    if ($total > 0) {
                        $user_tokens = Usertokens::where('customer_id', (int)$user_interest->user_id)->select('id', 'fcm_id')->get()->pluck('fcm_id')->toArray();

                        $fcm_tokens[] = $user_tokens;
                        if (!empty($fcm_tokens)) {
                            $fcmMsg = array(
                                'title' => __('New Car Listing Find'),
                                'message' => __('Car listing matches Find'),
                                'type' => 'property_listing',
                                'body' => __('Car listing matches Find'),
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                'sound' => 'default',
                                'id' => 2292, //$request->id
                            );
                            send_push_notification($fcm_tokens[0], $fcmMsg);
                        }
                    }
                }
            }

            $Property = Property::with('customer')->find($request->id);
            if (count($Property->customer) > 0) {

                if ($Property->customer[0]->fcm_id != '' && $Property->customer[0]->notification == 1) {

                    //START :: Send Notification To Customer
                    $fcm_ids = array();
                    $customer_id = Customer::where('id', (string)$Property->customer[0]->id)->where('isActive', '1')->where('notification', 1)->get();
                    if (count($customer_id)) {
                        $user_token = Usertokens::where('customer_id', $Property->customer[0]->id)->select('id', 'fcm_id')->get()->pluck('fcm_id')->toArray();
                    }

                    $fcm_ids[] = $user_token;
                    $msg = "";
                    if (!empty($fcm_ids)) {
                        $msg = $Property->status == 1 ? __('Activate now by Adminstrator') : __('Deactive now by Adminstrator');
                        $registrationIDs = $fcm_ids[0];
                        $fcmMsg = array(
                            'title' => __('Car Updated'),
                            'message' => $msg,
                            'type' => 'property_inquiry',
                            'body' => $msg,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default',
                            'id' => $Property->id,
                        );
                        send_push_notification($registrationIDs, $fcmMsg);
                    }
                    //END ::  Send Notification To Customer

                    Notifications::create([
                        'title' => __('Car Updated'),
                        'message' => $msg,
                        'image' => '',
                        'type' => '1',
                        'send_type' => '0',
                        'customers_id' => $Property->customer[0]->id,
                        'propertys_id' => $Property->id
                    ]);
                }
            }
            $response['error'] = false;
            return response()->json($response);
        }
    }


    public function removeGalleryImage(Request $request)
    {
        // dd($request->toArray());
        if (!has_permissions('delete', 'slider')) {
            return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
        } else {
            $id = $request->id;
            // dd($id);
            $getImage = PropertyImages::where('id', $id)->first();
            // dd($getImage);

            $image = $getImage->image;
            $propertys_id =  $getImage->propertys_id;

            if (PropertyImages::where('id', $id)->delete()) {
                if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image)) {
                    unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image);
                }
                $response['error'] = false;
            } else {
                $response['error'] = true;
            }

            $countImage = PropertyImages::where('propertys_id', $propertys_id)->get();
            if ($countImage->count() == 0) {
                rmdir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id);
            }
            return response()->json($response);
        }
    }

    public function getStatesByCountry(Request $request)
    {
        $country = $request->country;
        if ($country != '') {
            $state = get_states_from_json($country);

            if (!empty($state)) {
                $response['error'] = false;
                $response['data'] = $state;
            } else {
                $response['error'] = true;
                $response['message'] = "No data found!";
            }
        } else {
            $response['error'] = true;
            $response['message'] = "No data found!";
        }


        return response()->json($response);
    }
}
