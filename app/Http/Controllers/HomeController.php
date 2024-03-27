<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Property;
use App\Models\PropertysInquiry;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (!has_permissions('read', 'dashboard')) {
            return redirect('dashboard')->with('error', PERMISSION_ERROR_MSG);
        } else {

            $properties = Property::select('id', 'category_id', 'title', 'price', 'title_image', 'latitude', 'longitude', 'city_name')->with('category')->orderBy('price', 'DESC')->limit(10)->get();
            $properties_data = Property::select('id', 'title', 'price', 'title_image', 'latitude', 'longitude', 'city_name', 'total_click')->orderBy('total_click', 'DESC')->limit(10)->get();


            // 0:Sell 1:Rent 2:Sold 3:Rented
            $list['total_sell_property'] = Property::whereHas('category', function ($q) {
                $q->where('category', 'Cars For Sale');
            })->get()->count();
            $list['total_rant_property'] = Property::whereHas('category', function ($q) {
                $q->where('category', 'Car Rental');
            })->get()->count();
            $list['total_caysh_property'] = Property::whereHas('category', function ($q) {
                $q->where('caysh', 1);
            })->get()->count();


            $list['total_property_inquiry'] = PropertysInquiry::all()->count();
            $list['total_properties'] = Property::all()->count();
            $list['total_articles'] = Article::all()->count();
            $list['total_categories'] = Category::all()->count();
            $list['total_customer'] = Customer::all()->count();
            $list['total_agents'] = Customer::where('role','1')->get()->count();
            
            // =============== NEW =============== //
            $today = now();
            $startDate = $today->copy()->startOfMonth();
            $endDate = $today->copy()->endOfMonth();

            $sellproperties = Property::whereHas('category', function ($q) {
                $q->where('category', 'Cars For Sale');
                })->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at')->get();
                
            $rentproperties = Property::whereHas('category', function ($q) {
                $q->where('category', 'Car Rental');
            })->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at')->get();
                
            $cayshproperties = Property::whereHas('category', function ($q) {
                $q->where('caysh', 1);
            })->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at')->get();

            $sellmonthSeries = array();
            $rentmonthSeries = array();
            $cayshmonthSeries = array();
            $monthDates = array();
            $sellcountForCurrentDay = array();
            $rentcountForCurrentDay = array();
            $cayshcountForCurrentDay = array();

            $monthSeries = []; // Array to store counts for each month

            // Loop through each day of the current month
            for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
                // data related Month 
                $sellmonthSeries = array_fill(0, 12, 0); // Initialize an array with 12 zeroes for each month

                // Loop through each property and update the counts in $sellmonthSeries
                $sellproperties->each(function ($property) use (&$sellmonthSeries) {
                    $monthIndex = Carbon::parse($property->created_at)->format('n') - 1; // Get the month index (0-11)
                    $sellmonthSeries[$monthIndex]++;
                });


                $rentmonthSeries = array_fill(0, 12, 0); // Initialize an array with 12 zeroes for each month

                // Loop through each property and update the counts in $rentmonthSeries
                $rentproperties->each(function ($property) use (&$rentmonthSeries) {
                    $monthIndex = Carbon::parse($property->created_at)->format('n') - 1; // Get the month index (0-11)
                    $rentmonthSeries[$monthIndex]++;
                });


                $cayshmonthSeries = array_fill(0, 12, 0); // Initialize an array with 12 zeroes for each month

                // Loop through each property and update the counts in $cayshmonthSeries
                $cayshproperties->each(function ($property) use (&$cayshmonthSeries) {
                    $monthIndex = Carbon::parse($property->created_at)->format('n') - 1; // Get the month index (0-11)
                    $cayshmonthSeries[$monthIndex]++;
                });

                $weekDates = array();
                for ($month = 1; $month <= 12; $month++) {
                    $monthName = Carbon::create(null, $month, 1)->format('M');
                    \array_push($monthDates, "'" . $monthName . "'");
                }
                // ----------------------------------------------------------------------------------------------------
                // ----------------------------------------------------------------------------------------------------
                $sellweekpropertyCounts = DB::table('propertys')
                    ->select(
                        DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                        DB::raw('COUNT(*) as count'))
                    ->where('propery_type', 0)
                    ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
                    ->get();

                // Create an array to store the counts for each day of the week
                $sellweekSeries = array_fill(1, 7, 0);
                $rentweekSeries = array_fill(1, 7, 0);

                foreach ($sellweekpropertyCounts as $count) {
                    $sellweekSeries[$count->day_of_week] = $count->count;
                }

                $rentweekpropertyCounts = DB::table('propertys')
                    ->select(
                        DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                        DB::raw('COUNT(*) as count'),
                        DB::raw('created_at')

                    )
                    ->where('propery_type', 1)
                    ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
                    ->get();

                // Create an array to store the counts for each day of the week
                $sellweekSeries = array_fill(1, 7, 0);


                foreach ($rentweekpropertyCounts as $count) {

                    $rentweekSeries[$count->day_of_week] = $count->count;
                }
                $propertiesForDay = $properties->filter(function ($property) use ($day) {
                    return $day->isSameDay(Carbon::parse($property->created_at));
                });

                $countForMonth = $propertiesForDay->count();
                array_push($sellcountForCurrentDay, $countForMonth);
                $currentDates[] = '"' . Carbon::parse($day)->format('Y-m-d') . '"';
            }
                
            // Prepare the chart data
            $chartData = [
                'rentmonthSeries' => $rentmonthSeries,
                'sellmonthSeries' => $sellmonthSeries,
                'cayshmonthSeries' => $cayshmonthSeries,
                'sellcountForCurrentDay' => $sellcountForCurrentDay,
                'rentcountForCurrentDay' => $rentcountForCurrentDay,
                'cayshcountForCurrentDay' => $cayshcountForCurrentDay,
                'sellweekSeries' => $sellweekSeries,
                'rentweekSeries' => $rentweekSeries,
                'cayshweekSeries' => $rentweekSeries,
                'weekDates' =>  [0 => "'Day1'", 1 => "'Day2'", 2 => "'Day3'", 3 => "'Day4'", 4 => "'Day5'", 5 => "'Day6'", 6 => "'Day7'"],
                'monthDates' =>  $monthDates,
                'currentDates' => $currentDates,
                'currentDate' => "[" . Carbon::now()->format('Y-m-d') . "]"
            ];
            
            // Categories Chart
            $get_category = Category::withCount('properties')->where('status','1')->get();
            $category_name = array();
            $category_count = array();

            foreach ($get_category as $key => $value) {
                array_push($category_name, "'" . $value->category_ar . "'");
                array_push($category_count, $value->properties_count);
            }
            
            // ================= END NEW ===================== //
            
            $property = Property::select(DB::raw("COUNT(*) as count"), DB::raw("DATE_FORMAT(created_at, '%M %Y') as month"))
                ->whereYear('created_at', date('Y'))
                ->groupBy(DB::raw("YEAR(created_at)"), DB::raw("MONTH(created_at)"))
                ->pluck('count', 'month');

            $property_labels = $property->keys();
            $property_data = $property->values();

            $rows = array();
            $firebase_settings = array();
            $operate = '';
            $settings['company_name'] = system_setting('company_name');
            $settings['currency_symbol'] = system_setting('currency_symbol');

            $userData = Customer::select(\DB::raw("COUNT(*) as count"))
                ->whereYear('created_at', date('Y'))
                ->groupBy(\DB::raw("Month(created_at)"))
                ->pluck('count');

            return view('home', compact('chartData', 'category_name', 'category_count', 'list', 'settings', 'property_labels', 'property_data', 'properties', 'userData', 'properties_data'));
        }
    }
    
    public function blank_dashboard()
    {
        return view('blank_home');
    }

    public function change_password()
    {
        return view('change_password.index');
    }
    
    public function changeprofile()
    {
        return view('change_profile.index');
    }

    public function check_password(Request $request)
    {
        $id = Auth::id();
        $oldpassword = $request->old_password;
        $user = DB::table('users')->where('id', $id)->first();


        $response['error'] = password_verify($oldpassword, $user->password) ? true : false;
        return response()->json($response);
        // return (password_verify($oldpassword, $user->password)) ? true : false;
    }

    public function store_password(Request $request)
    {

        $confPassword = $request->confPassword;
        $id = Auth::id();
        $role = Auth::user()->type;

        $users = User::find($id);
        // if ($role == 0) {
        //     $users->name  = $request->name;
        //     $users->email  = $request->email;
        // }

        if (isset($confPassword) && $confPassword != '') {
            $users->password = Hash::make($confPassword);
        }

        $users->update();
        return back()->with('success', 'Password Change Successfully');
    }
    
    function update_profile(Request $request)
    {
        $id = Auth::id();
        $role = Auth::user()->type;

        $users = User::find($id);
        if ($role == 0) {
            $users->name  = $request->name;
            $users->email  = $request->email;
        }
        $users->update();
        return back()->with('success', 'Profile Updated Successfully');
    }

    public function privacy_policy()
    {
        echo system_setting('privacy_policy');
    }

    public function firebase_messaging_settings(Request $request)
    {
        $file_path = public_path('firebase-messaging-sw.js');

        // Check if file exists
        if (File::exists($file_path)) {
            // Remove existing file
            File::delete($file_path);
        }

        // Move new file
        $request->file->move(public_path(), 'firebase-messaging-sw.js');
    }
}
