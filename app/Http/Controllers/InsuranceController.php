<?php

namespace App\Http\Controllers;

use Mpdf\Mpdf;
use Barryvdh\DomPDF\Facade\Pdf; // <--- ADD THIS LINE HERE
// use App\Http\Controllers\Carbon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User; // Assuming your logic is in models now
use App\Models\Client;
use App\Models\PotentialClient;
use App\Models\Insurer;
use App\Models\Policy;
use App\Models\PlacingSlip;
use App\Models\Vehicle;
use App\Models\SlipCancellation;
use App\Models\CreditNote;
use App\Models\Claim;
use App\Models\Invoice;
use App\Models\Receipt;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentVoucher;
use Illuminate\Support\Facades\Log;
use App\Models\Quotation;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;


class InsuranceController extends Controller
{

    // INSURANCE BROKING DASHBOARD

    // INSURANCE BROKING DASHBOARD
public function dashboard(Request $request, $action = 'view_slip_list')
{
    $user = auth()->user();

    // 1. Get Placing Slips (Expiring in 3 months)
    $expiryLimit = \Carbon\Carbon::now()->addMonths(3);
    $placements = PlacingSlip::where('status', 'Active')
        ->where('policy_expiry_date', '>', \Carbon\Carbon::now())
        ->where('policy_expiry_date', '<=', $expiryLimit)
        ->get();

    // 2. Get Active Claims (Not Settled or Closed)
    $activeClaims = Claim::whereNotIn('claim_status', ['Settled', 'Closed'])
        ->get();
    
    // REMOVED: $request->query('action') lines entirely.
    // $action is already assigned 'insurance_broking' via the URL route parameter!

    return view('insurance_broking.dashboard', compact(
        'placements', 
        'activeClaims', 
        'action', 
        'user'
    ));
}



   public function index(Request $request, $action = 'register_slip')
{
    $actions = [
        "register_slip"            => "Register Slip",
        "register_client"          => "Register Client",
        "register_policy"          => "Register Policy",
        "register_insurer"         => "Register Insurer",
        "register_vehicle"         => "Register Vehicle",
        "register_claim"           => "Register Claim",
        "register_quote"           => "Register Quote",
        
        // New View Actions
        "view_slip_list"           => "VIEW LIST",
        "view_claim_list"          => "Claims Registry",
        "view_vehicle_list"        => "Vehicle Registry",
        "view_cancelled_slip_list" => "Cancellation Advices"
    ];

    // 1. Guard clause: Ensure the action is valid
    if (!array_key_exists($action, $actions)) {
        abort(404);
    }

    // 2. Globally share authenticated user
    $data = [
        'action'  => $action,
        'section' => $actions[$action],
        'user'    => auth()->user(), 
    ];

   

    // --- LOGIC FOR VIEW LISTS ---
if (str_starts_with($action, 'view_')) {

    // 1. INTERCEPT FOR LIVE SEARCH (AJAX requests)
    if (request()->ajax() || request()->wantsJson()) {
        $query = request()->get('query');
        
        $results = \App\Models\SlipCancellation::where('slip_id', 'LIKE', "%{$query}%")
            ->orWhere('insured_name', 'LIKE', "%{$query}%")
            ->orWhere('insurance_policy', 'LIKE', "%{$query}%")
            ->take(8)
            ->get(['slip_id', 'insured_name', 'insurance_policy']);

        return response()->json($results);
    }

    // 2. STANDARD HTML PAGE LOADS
    switch ($action) {
        case 'view_slip_list':
            $data['pageTitle'] = 'SLIP LIST';
            $data['placements'] = PlacingSlip::whereIn('status', ['Active', 'Renewed', 'Expired'])->get();
            break;
            
        case 'view_claim_list':
            $data['pageTitle'] = 'CLAIM LIST';
            $data['claims'] = Claim::all();
            break;
            
        case 'view_vehicle_list':
            $data['pageTitle'] = 'VEHICLE LIST';
            $data['vehicles'] = Vehicle::all();
            break;
            
        case 'view_cancelled_slip_list':
            $data['pageTitle'] = 'CANCELLED SLIP LIST';
            $data['cancellations'] = SlipCancellation::all(); 
            break;
    }
    
    return view('insurance_broking.view_list.index', $data);
}



    // --- LOGIC FOR REGISTRATION ---
    // Optimizing data payload: fetch only what is strictly necessary if tables are huge
    $data['clients']  = Client::all();
    $data['insurers'] = Insurer::all();
    $data['policies'] = Policy::all();

    // Clone / Template Logic
    $data['cloneData'] = $request->filled('clone_id') 
        ? PlacingSlip::find($request->query('clone_id'))
        : null;

    // Lookups for JS
    $data['policy_lookup'] = $data['policies']->pluck('scope_of_cover_policy', 'policy_name');
    $data['client_lookup'] = $data['clients']->keyBy('client_name')->map(function($item) {
        return [
            'nature'  => $item->nature_of_business,
            'address' => $item->physical_address
        ];
    });

    return view('insurance_broking.register', $data);
}

    
    // REGISTRATION FORM


 public function registerClient(Request $request)
{

    // 1. Validate the incoming data
    $validatedData = $request->validate([
        'client_name'        => 'required|string|unique:client_register,client_name',
        'physical_address'   => 'required|string',
        'postal_address'     => 'nullable|string',
        'contact_number'     => 'required|string',
        'email_address'      => 'required|email',
        'nature_of_business' => 'required|string',
        'client_type'        => 'required|string',
    ], [
        'client_name.unique' => 'Client already registered.',
    ]);

    // Check if a user is actually logged in
    if (!Auth::check()) {
        return back()->with('error', 'Your session has expired. Please log in again.');
    }

    // 2. Format the name (lowercase then Capitalize Each Word)
    $formattedName = Str::title(Str::lower(trim($request->client_name)));

    try {
        // 3. Create the record using Eloquent
        Client::create([
            'user'               => Auth::user()->name ?? 'System Process', // Added a fallback string just in case
            'client_name'        => $formattedName,
            'physical_address'   => $request->physical_address,
            'postal_address'     => $request->postal_address,
            'contact_number'     => $request->contact_number,
            'email_address'      => $request->email_address,
            'nature_of_business' => $request->nature_of_business,
            'client_type'        => $request->client_type,
        ]);

        return back()->with('success', 'Client successfully registered');

    } catch (\Exception $e) {
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}

// REGISTER_VEHICLE

    public function registerVehicle(Request $request)
{
    // --- SCENARIO 1: SINGLE MANUAL ENTRY ---
    if ($request->input('action') === 'register_vehicle') {
        $validated = $request->validate([
            'chassis_number'     => 'required|string|unique:vehicle_register,chassis_number|max:255',
            'reg_number'         => 'required|string|max:50',
            'slip_number'        => 'required|string|max:100',
            'policy_start_date'  => 'required|date',
            'policy_expiry_date' => 'required|date|after:policy_start_date',
            'sum_insured'        => 'required|numeric|min:0',
            'total_premium'      => 'required|numeric|min:0',
            // Capturing optional form fields safely
            'insurer_name'       => 'nullable|string|max:255',
            'client_name'        => 'nullable|string|max:255',
            'vehicle_make'       => 'nullable|string|max:255',
            'engine_number'      => 'nullable|string|max:255',
            'policy_type'        => 'nullable|string|max:255',
            'policy_currency'    => 'nullable|string|max:10',
        ], [
            'chassis_number.unique' => 'This vehicle (Chassis) is already registered in the system.'
        ]);

        // Save safely using explicitly validated fields
        Vehicle::create(array_merge($validated, [
            'user' => Auth::user()->name ?? auth()->user()->name ?? 'System'
        ]));

        return redirect()->route('insurance_broking.register', ['action' => 'register_vehicle'])
                         ->with('success', 'Vehicle ' . $validated['reg_number'] . ' successfully registered!');
    }

    // --- SCENARIO 2: BULK CSV IMPORT ---
    if ($request->input('action') === 'bulk_import_vehicle' && $request->hasFile('vehicle_csv')) {
        
        $request->validate([
            'vehicle_csv' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('vehicle_csv');
        $path = $file->getRealPath();
        $currentUser = Auth::user()->name ?? auth()->user()->name ?? 'System';
        $importCount = 0;

        if (($handle = fopen($path, "r")) !== FALSE) {
            fgetcsv($handle); // Skip header row

            DB::beginTransaction();
            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) < 6 || empty($data[3]) || empty($data[5])) {
                        continue;
                    }

                    // Skips duplicate chassis numbers silently to prevent a full batch crash
                    $chassisExists = Vehicle::where('chassis_number', $data[5])->exists();
                    if ($chassisExists) {
                        continue; 
                    }

                    Vehicle::create([
                        'user'               => $currentUser,
                        'slip_number'        => $data[0] ?? null,
                        'insurer_name'       => $data[1] ?? null,
                        'client_name'        => $data[2] ?? null,
                        'reg_number'         => $data[3] ?? null,
                        'vehicle_make'       => $data[4] ?? null,
                        'chassis_number'     => $data[5] ?? null,
                        'engine_number'      => $data[6] ?? null,
                        'policy_start_date'  => $this->parseCsvDate($data[7] ?? null),
                        'policy_expiry_date' => $this->parseCsvDate($data[8] ?? null),
                        'policy_type'        => $data[9] ?? null,
                        'policy_currency'    => $data[10] ?? 'ZMW',
                        'sum_insured'        => (double)($data[11] ?? 0),
                        'total_premium'      => (double)($data[12] ?? 0),
                    ]);
                    $importCount++;
                }

                DB::commit();
                fclose($handle);
                
                return redirect()->route('insurance_broking.register', ['action' => 'register_vehicle'])
                                 ->with('success', "Successfully imported $importCount vehicles!");

            } catch (\Exception $e) {
                DB::rollBack();
                fclose($handle);
                return back()->withInput()->withErrors('Import Error: ' . $e->getMessage());
            }
        }
    }

    // Fallback if neither condition matches perfectly
    return back()->withInput()->withErrors('No valid registration action detected.');
}


    // 4. REGISTER CLAIM
    /**
     * Handle Claim Registration
     */
    protected function registerClaim(Request $request)
{
    // 1. Advanced Validation
    // This checks 'unique' across four columns (date_of_loss, client_name, date_of_notification, and type_of_claim)
    $validated = $request->validate([
        'client_name'           => 'required|string',
        'date_of_notification'  => 'required|date', 
        'type_of_claim'         => 'required|string',
        'date_of_loss'          => [
            'required',
            'date',
            Rule::unique('claim_register', 'date_of_loss')->where(function ($query) use ($request) {
                return $query->where('client_name', $request->client_name)
                             ->where('date_of_notification', $request->date_of_notification)
                             ->where('type_of_claim', $request->type_of_claim);
            }),
        ],
        'claim_amount'          => 'required|numeric|min:0',
        'insurer_name'          => 'required|string',
        'claim_intimation_date' => 'nullable|date',
        'details_of_loss'       => 'nullable|string',
        'documents_received'    => 'nullable|string',
        'claim_status'          => 'required|string',
    ], [
        'date_of_loss.unique' => 'A claim for this client with this specific type and dates has already been registered.',
    ]);

    // 2. Create the record using Eloquent
    Claim::create(array_merge($validated, [
        'user' => Auth::user()->name ?? 'System', 
    ]));

    // 3. Success Response
    return redirect()->route('insurance_broking.register', ['action' => 'register_claim'])
                     ->with('success', 'Claim for ' . $request->client_name . ' successfully registered');
}




// 6. REGISTER SLIP


protected function registerSlip(Request $request)
{
    // 1. Comprehensive Validation
    $validated = $request->validate([
        // Core Policy Details
        'insurance_policy'    => 'required|string|max:255', // e.g., Policy Number or Type
        'insured'             => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) use ($request) {
                // Multi-point duplicate check including your specific fields (minus policy_name)
                $exists = \App\Models\PlacingSlip::where('insured', $value)
                    ->where('insurer', $request->insurer)
                    ->where('policy_start_date', $request->policy_start_date)
                    ->where('policy_expiry_date', $request->policy_expiry_date)
                    ->where('insurance_policy', $request->insurance_policy)
                    ->where('total_sum_insured', $request->total_sum_insured)
                    ->exists();

                if ($exists) {
                    $fail("An identical placing slip with this Insured, Insurance Policy, and Sum Insured already exists for this period.");
                }
            },
        ],
        'insurer'             => 'required|string|max:255',
        'policy_start_date'   => 'required|date',
        'policy_expiry_date'  => 'required|date|after:policy_start_date',
        
        // Financial Values
        'total_sum_insured'   => 'required|numeric|min:0',
        'basic_premium'       => 'required|numeric|min:0',
        'policy_currency'     => 'required|string|max:10',

        // Rates used in financial calculations
        'discount_rate'       => 'nullable|numeric|min:0|max:100',
        'commission_rate'     => 'nullable|numeric|min:0|max:100',
        'basic_rate'          => 'nullable|numeric|min:0',

        // Optional / Descriptive Slip Fields
        'nature_of_business'  => 'nullable|string',
        'principal_address'   => 'nullable|string',
        'cancellation_clause' => 'nullable|string',
        'placing_slip_clause' => 'nullable|string',
        'scope_of_cover'      => 'nullable|string',
        'extensions'          => 'nullable|string',
        'excess_deductible'   => 'nullable|string',
        'property_insured'    => 'nullable|string',
        'location_of_risk'    => 'nullable|string',
        'specific_warranties' => 'nullable|string',
        'specific_conditions' => 'nullable|string',

        // Payment Information
        'payment_made'        => 'nullable|numeric|min:0',
        'payment_method'      => 'nullable|string|max:100',
    ]);

    // 2. Financial Logic
    $basicPremium = (float)$validated['basic_premium'];
    $discRate     = (float)($validated['discount_rate'] ?? 0);
    $discount     = ($discRate / 100) * $basicPremium;
    
    $netPremium   = $basicPremium - $discount;
    $levy         = $netPremium * 0.05; // 5% Levy
    $grossPremium = $netPremium + $levy;

    $commRate       = (float)($validated['commission_rate'] ?? 0);
    $commAmount     = ($commRate / 100) * $netPremium;
    $insurerPremium = $grossPremium - $commAmount;

    // 3. Save via Eloquent
    PlacingSlip::create([
        'user'                => Auth::user()->name ?? auth()->user()->name ?? 'System Process',
        'insurance_policy'    => $validated['insurance_policy'],
        'insured'             => $validated['insured'],
        'nature_of_business'  => $validated['nature_of_business'] ?? null,
        'principal_address'   => $validated['principal_address'] ?? null,
        'policy_start_date'   => $validated['policy_start_date'],
        'policy_expiry_date'  => $validated['policy_expiry_date'],
        'insurer'             => $validated['insurer'],
        'cancellation_clause' => $validated['cancellation_clause'] ?? null,
        'placing_slip_clause' => $validated['placing_slip_clause'] ?? null,
        'scope_of_cover'      => $validated['scope_of_cover'] ?? null,
        'extensions'          => $validated['extensions'] ?? null,
        'excess_deductible'   => $validated['excess_deductible'] ?? null,
        'property_insured'    => $validated['property_insured'] ?? null,
        'location_of_risk'    => $validated['location_of_risk'] ?? null,
        'specific_warranties' => $validated['specific_warranties'] ?? null,
        'specific_conditions' => $validated['specific_conditions'] ?? null,
        'policy_currency'     => $validated['policy_currency'],
        'total_sum_insured'   => $validated['total_sum_insured'],
        'basic_rate'          => $validated['basic_rate'] ?? null,
        'basic_premium'       => $basicPremium,
        'discount_rate'       => $discRate,
        'discount'            => $discount,
        'premium_levy_rate'   => 5.00,
        'premium_levy'        => $levy,
        'gross_premium'       => $grossPremium,
        'commission_rate'     => $commRate,
        'commission_amount'   => $commAmount,
        'insurer_premium'     => $insurerPremium,
        'payment_made'        => $validated['payment_made'] ?? null,
        'payment_method'      => $validated['payment_method'] ?? null,
    ]);

    // 4. Clean Redirect
    return redirect()->route('insurance_broking.register', ['action' => 'register_slip'])
                     ->with('success', 'The slip (' . $validated['insurance_policy'] . ') for ' . $validated['insured'] . ' has been successfully registered');
}


// 7. REGISTER INSURER
/**
 * Handle Insurer Registration
 */

protected function registerInsurer(Request $request)
{

    $request->validate([
        'insurer_name'   => 'required|string|unique:insurer_register,insurer_name',
        'email_address'  => 'required|email',
        'contact_number' => 'required',
        'physical_address' => 'required|string',
        'insurer_type'     => 'required|string',
        'postal_address'   => 'nullable|string',
        
    ]);

    Insurer::create([
        'user'             => Auth::user()->name ?? 'System',
        'insurer_name'     => $request->insurer_name,
        'physical_address' => $request->physical_address,
        'postal_address'   => $request->postal_address,
        'insurer_type'     => $request->insurer_type,
        'contact_number'   => $request->contact_number, // Pass to model
        'email_address'    => $request->email_address,  // Pass to model
    ]);

    return redirect()->route('insurance_broking.register', ['action' => 'register_insurer'])
                     ->with('success', 'Insurer registered successfully!');
}


// 8. REGISTER POLICY

/**
 * Handle Policy Registration
 */
protected function registerPolicy(Request $request)
{
    // 1. Validation Logic
    // 'unique:policies,policy_name' automatically checks for duplicates in the DB
    $request->validate([
        'policy_name'            => 'required|string|max:255|unique:policy_register,policy_name',
        'scope_of_cover_policy'  => 'required|string',
        'class_of_policy'        => 'required|string',
        'remarks_policy'         => 'nullable|string',
    ], [
        'policy_name.unique' => 'This policy name is already registered.'
    ]);

    // 2. Save via Policy Model
    Policy::create([
        'user'                  => Auth::user()->name ?? 'System',
        'policy_name'           => $request->policy_name,
        'scope_of_cover_policy' => $request->scope_of_cover_policy,
        'remarks_policy'        => $request->remarks_policy,
        'class_of_policy'       => $request->class_of_policy,
    ]);

    // 3. Redirect with success message
    return redirect()->route('insurance_broking.register', ['action' => 'register_policy'])
                     ->with('success', 'Policy "' . $request->policy_name . '" successfully registered!');
}










//STORE FUNCTION

public function store(Request $request)
{
    // 1. GATEKEEPER: Check authentication once for ALL sub-methods
    if (!Auth::check()) {
        return redirect()->route('login')->with('error', 'Your session expired. Please log in again.');
    }

    // Redirect based on the action field
    switch ($request->input('action')) {
        case 'register_quote':   return $this->registerQuote($request);
        case 'register_slip':    return $this->registerSlip($request);
        case 'register_client':  return $this->registerClient($request);
        case 'register_policy':  return $this->registerPolicy($request);
        case 'register_insurer': return $this->registerInsurer($request);
        case 'register_claim':   return $this->registerClaim($request);
        case 'register_vehicle':
        case 'bulk_import_vehicle': 
            return $this->registerVehicle($request);
        default:
            return back()->withErrors('Error: Invalid action requested.');
    }
}



    /**
     * Helper to handle the d-m-y or d/m/y format from CSV
     */
    private function parseCsvDate($dateString)
    {
        try {
            $cleaned = str_replace('/', '-', trim($dateString));
            return Carbon::createFromFormat('d-m-y', $cleaned)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d'); // Default to today if format fails
        }
    }

//FETCH CLIENT RECORDS
    public function client_name() 
{
    // Fetches all records from the 'client_register' table as a Collection
    // and converts it to an associative array to match your previous output.
    return Client::all()->toArray();
}

//FETCH PONTENTIAL CLIENT RECORDS
public function potential_client_names() {
    // This replaces the mysqli query and fetch_all logic
    return PotentialClient::all()->toArray();
}




//GET ALL CLIENTS INCLUDING POTENTIAL CLIENTS

public function get_every_client() 
{
    // 1. Fetch both sets of data
    $clients = Client::all();
    $potential = PotentialClient::all();

    // 2. Merge them using Laravel Collections
    // This maintains them as objects, which is much easier to work with in Blade
    return $clients->merge($potential);
}


/**
     * Retrieve all insurance policies.
     */
    public function policy_name() 
    {
        // Replaces mysqli_query and fetch_all
        // orderBy is optional but usually helpful for dropdowns/lists
        return Policy::orderBy('policy_name', 'asc')->get()->toArray();
    }

    public function insurer_name() 
    {
        // Fetches all records from insurer_register and returns as an array
        return Insurer::all()->toArray();
    }

    //PLACING SLIP INFORMATION
    public function placement_infor() 
    {
        // Fetches every row from the slip_register table
        return PlacingSlip::all()->toArray();
    }

    // GET SLIP NUMBERS

    // public function getPlacementById($id) 
    // {
    //     // find() looks for the primary key (id) and returns the model instance
    //     // It returns null if no record is found.
    //     return PlacingSlip::find($id);
    // }

    public function getPlacementById($id) 
    {
        // If ID is not found, Laravel throws a 404 page automatically
        return Slip::findOrFail($id);
    }

    // CLONE SLIP FOR RENEWAL
    
   /**
 * Use an existing placing slip as a data template for a new registration.
 */
    public function cloneSlip(PlacingSlip $placingSlip)
    {
        // Replicate the model properties into a clean unsaved object instance
        $clonedSlip = $placingSlip->replicate();
        
        // Clear out status and unique fields so it acts like a pristine new draft
        $clonedSlip->status = 'Pending'; 
        $clonedSlip->policy_start_date = null;
        $clonedSlip->policy_expiry_date = null;

        // Redirect straight to your creation form view, passing the template values along
        return view('insurance_broking.register', [
            'template' => $clonedSlip
        ]);
    }

    public function processRenewal(Request $request)
    {
        // Your logic to clone the slip for renewal
        // Return JSON for the AJAX request
        return response()->json(['success' => true, 'new_id' => 123]);
    }


    public function policy_expiry()
    {
        $expiryLimit = Carbon::now()->addMonths(3);
        
        $placements = PlacingSlip::where('status', 'Active')
            ->where('policy_expiry_date', '<=', $expiryLimit)
            ->where('policy_expiry_date', '>', Carbon::now())
            ->get();

        return view('dashboard', compact('placements'));
    }


    /**
     * Retrieve all endorsement/cancellation information.
     */
    public function slip_cancellation_infor() 
    {
        // Eloquent handles the SELECT * and connection automatically
        return SlipCancellation::all()->toArray();
    }

    /**
 * Retrieve only the IDs from the slip register table.
 */
public function slip_register_id_infor() 
{
    // select('id') ensures we only pull the ID column, saving memory
    return PlacingSlip::select('id')->get()->toArray();
}

public function slip_id_list() 
{
    return PlacingSlip::pluck('id')->toArray();
}


/**
 * Retrieve only the slip_id column from the slip_cancellation table.
 */
public function slip_cancellation_id_infor() 
{
    // select('slip_id') ensures we only pull that specific column.
    // Eloquent will throw an exception automatically if the table/column doesn't exist.
    return SlipCancellation::select('slip_id')->get()->toArray();
}

public function slip_cancellation_id_list() 
{
    return SlipCancellation::pluck('slip_id')->toArray();
}

/**
     * Retrieve slip IDs from the credit notes table.
     */
    public function credit_note_id_infor() 
    {
        // We select only the 'slip_id' to mirror your previous SQL query
        return CreditNote::select('slip_id')->get()->toArray();
    }


    /**
     * Retrieve all claim information.
     */
    public function claim_infor() 
    {
        // Eloquent's all() method handles the query and result fetching
        return Claim::all()->toArray();
    }

    
    /**
     * Retrieve all invoice information.
     */
    public function invoice_infor() 
    {
        // Eloquent handles the SELECT * and database connection
        return Invoice::all()->toArray();
    }


    /**
     * Retrieve all credit note information.
     */

    public function credit_note_infor() 
    {
        // Eloquent handles the SELECT * from the 'credit_notes' table
        return CreditNote::all()->toArray();
    }

    /**
     * Retrieve only slip numbers from the invoice table.
     */
    public function invoice_slip_id_infor() 
    {
        // select('slip_number') ensures we only pull the necessary column
        // We call toArray() to match the MYSQLI_ASSOC output format
        return Invoice::select('slip_number')->get()->toArray();
    }


    /**
     * Retrieve all cash book records, newest first.
     */
    public function cash_book_infor() 
    {
        // latest('receipt_date') is a shorthand for orderBy('receipt_date', 'desc')
        // get() executes the query, and toArray() matches your assoc array format
        return CashBook::latest('receipt_date')->get()->toArray();
    }


// EDIT CLAIM
    public function editClaim($id)
    {
        $claim = Claim::findOrFail($id);
        return view('insurance_broking.claims.edit', compact('claim'));
    }


/**
 * Update the specified claim in storage.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id
 * @return \Illuminate\Http\RedirectResponse
 */

// public function updateClaim(Request $request, $id)
// {
//     // dd($request->all()); // This will show you exactly what values are being passed
//     // 1. Find the claim
//     $claim = \App\Models\Claim::findOrFail($id);

//     // 2. Validate the incoming data
//     $validatedData = $request->validate([
//         'claim_intimation_date' => 'nullable|date',
//         'insurer_name'          => 'nullable|string|max:255',
//         'client_name'           => 'required|string|max:255',
//         'type_of_claim'         => 'nullable|string',
//         'date_of_loss'          => 'required|date',
//         'date_of_notification'  => 'nullable|date',
//         'details_of_loss'       => 'nullable|string',
//         'claim_amount'          => 'required|numeric',
//         'documents_received'    => 'nullable|string',
//         'claim_status'          => 'nullable|string',
//         'remarks'               => 'nullable|string',
//         'date_settled'          => 'nullable|date',
//         'amount_settled'        => 'nullable|numeric',
//         'policy_currency'       => 'nullable|string|max:10',
//     ]);

//     // Loop through the data and convert empty strings to null
//     foreach ($validatedData as $key => $value) {
//         if ($value === '') {
//             $data[$key] = null;
//         }
//     }

//     // 3. Prepare data for update
//     $updatePayload = array_merge($validatedData, [
//         'user' => \Illuminate\Support\Facades\Auth::user()->name ?? 'Unknown'
//     ]);

//     // 4. Perform the update
//     try {
//         $claim->update($updatePayload);
        
//         // Redirect to the "Show" page instead of "back()" to confirm the update
//         return redirect()->route('insurance_broking.claims.show', $id)
//                          ->with('success', 'Claim details saved successfully.');

//     } catch (\Exception $e) {
//         \Illuminate\Support\Facades\Log::error("Claim Update Failed: " . $e->getMessage());
        
//         // Use withErrors to pass the message back to your Blade error block
//         return back()->withInput()->with('error', 'Update Failed: ' . $e->getMessage());
//     }
// }

public function updateClaim(Request $request, $id)
{
    // 1. Find the claim
    $claim = \App\Models\Claim::findOrFail($id);

    // 2. Validate the incoming data (Handles multiple files array)
    $validatedData = $request->validate([
        'claim_intimation_date' => 'nullable|date',
        'insurer_name'          => 'nullable|string|max:255',
        'client_name'           => 'required|string|max:255',
        'type_of_claim'         => 'nullable|string',
        'date_of_loss'          => 'required|date',
        'date_of_notification'  => 'nullable|date',
        'details_of_loss'       => 'nullable|string',
        'claim_amount'          => 'required|numeric',
        'documents_received'    => 'nullable|string',
        'claim_status'          => 'nullable|string',
        'remarks'               => 'nullable|string',
        'date_settled'          => 'nullable|date',
        'amount_settled'        => 'nullable|numeric',
        'policy_currency'       => 'nullable|string|max:10',
        
        // Handle array of files validation
        'claim_documents'       => 'nullable|array',
        'claim_documents.*'     => 'file|mimes:pdf,jpg,png,docx|max:5120', 
    ]);

    // Extract files early to isolate them from string sanitization mapping
    $files = $request->file('claim_documents');

    // 3. Data Sanitization (Excluding file objects so they aren't corrupted)
    $updatePayload = collect($validatedData)
        ->except('claim_documents')
        ->map(function ($value) {
            return $value === '' ? null : $value;
        })->toArray();

    // 4. Handle Multiple Files Upload
    if ($request->hasFile('claim_documents')) {
        // Resolve tenant ID dynamically (fallback to 'rib' based on your paths)
        $tenantId = tenant('id') ?? 'rib';
        
        // Ensure client_id has a strict fallback so it never defaults to an empty slash
        $clientId = $claim->client_id ?? 'unknown_client';
        
        // Clean definition without trailing or accidental empty segments
        $directory = "tenants/{$tenantId}/clients/{$clientId}/claims/{$id}";
        
        // Fetch existing documents from the database if you want to merge them, 
        // or start a fresh array to overwrite them:
        $uploadedPaths = []; 
        if (!empty($claim->claim_documents)) {
            $existing = json_decode($claim->claim_documents, true);
            if (is_array($existing)) {
                $uploadedPaths = $existing;
            }
        }

        // Loop through each uploaded file
        foreach ($files as $file) {
            $filename = $file->hashName();
            
            // Explicitly store to the 'public' disk configuration
            $path = $file->storeAs($directory, $filename, 'public');
            
            // Clean up any double slashes that might sneak into the path string
            $cleanPath = preg_replace('#/+#', '/', $path);
            
            $uploadedPaths[] = $cleanPath;
        }

        // Save cleanly as a JSON string array column 
        $updatePayload['claim_documents'] = json_encode($uploadedPaths);
    }

    // 5. Add Audit Trail
    $updatePayload['user'] = \Illuminate\Support\Facades\Auth::user()->name ?? 'Unknown';

    // 6. Perform the update
    try {
        $claim->update($updatePayload);
        
        return redirect()->route('insurance_broking.claims.show', $id)
                         ->with('success', 'Claim details saved successfully.');

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Claim Update Failed: " . $e->getMessage());
        return back()->withInput()->with('error', 'Update Failed: ' . $e->getMessage());
    }
}

// DELETE FILES

public function deleteFile(Request $request, $id)
{
    try {
        // 1. Manually fetch the claim to ensure Route Model Binding isn't failing
        $claim = \App\Models\Claim::findOrFail($id); 
        
        $filePath = $request->input('file_path');
        if (!$filePath) {
            return response()->json(['success' => false, 'message' => 'No file path provided.'], 400);
        }

        // 2. Safely parse out the documents array depending on how it's stored
        $documents = $claim->claim_documents;
        if (is_string($documents)) {
            $documents = json_decode($documents, true) ?? [];
        } elseif (!is_array($documents)) {
            $documents = [];
        }

        // 3. Verify the file actually belongs to this record
        if (!in_array($filePath, $documents)) {
            return response()->json(['success' => false, 'message' => 'File not found on this record.'], 404);
        }

        // 4. Delete the physical file from the 'public' disk
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        // 5. Filter out the deleted file and reset array keys
        $updatedDocs = array_values(array_filter($documents, fn($doc) => $doc !== $filePath));
        
        // 6. Save it back to the database safely
        // If your model automatically casts to array, pass the array. Otherwise, json_encode it.
        $claim->claim_documents = empty($updatedDocs) ? null : $updatedDocs;
        $claim->save();

        return response()->json(['success' => true, 'message' => 'File deleted successfully.']);

    } catch (Exception $e) {
        // This will log the actual error message to storage/logs/laravel.log so you can inspect it
        \Log::error('File deletion failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false, 
            'message' => 'Server Error: ' . $e->getMessage()
        ], 500);
    }
}



// 1. DISPLAY THE TABLE OF UNINVOICED SLIPS
    // public function generateInvoices()
    // {
    //     // Performance Upgrade: Subquery prevents loading thousands of IDs into memory
    //     $placements = PlacingSlip::where('status', 'Active')
    //         ->whereNotIn('id', function ($query) {
    //             $query->select('slip_number')->from('invoices');
    //         })
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return view('insurance_broking.accounts.invoices.generate_invoice', [
    //         'placements'  => $placements,
    //         'levyRate'    => 0.05,
    //         'levyDivisor' => 1.05
    //     ]);
    // }

    public function generateInvoices()
{
    // 1. Declare the page title used by your master layout tracking wrapper
    $pageTitle = 'Generate Invoice';

    // 2. Fetch active slips that have not yet been copied to your invoices registry
    $placements = PlacingSlip::where('status', 'Active')
        ->whereNotIn('id', function ($query) {
            $query->select('slip_number')->from('invoices');
        })
        ->orderBy('id', 'desc')
        ->get();

    // 3. Return the view and pass all required variables cleanly inside the payload array
    return view('insurance_broking.accounts.invoices.generate_invoice', [
        'pageTitle'   => $pageTitle, // <-- FIXED: Passed down so your blade layout can render it
        'placements'  => $placements,
        'levyRate'    => 0.05,
        'levyDivisor' => 1.05
    ]);
}


public function viewInvoice_list()
    {
        // 1. Fetch invoices ordered by the newest records
        // If invoices are tied to a specific company/user, you can filter them here:
        // $invoice_in
        // for = Invoice::where('company_id', Auth::user()->company_id)->get();
        $invoice_infor = Invoice::orderBy('created_at', 'desc')->get();

        $pageTitle = 'Invoice List';

        // 2. Return the view and pass the variable down cleanly
        return view('insurance_broking.accounts.invoices.view_list', compact('invoice_infor', 'pageTitle'));
    }

//CREATE INVOICE
public function createInvoice(Request $request)
{
    // 1. Validation
    $request->validate([
        'slip_number' => 'required'
    ]);

    // 2. Fetch the source data from the Slip model
    $slip = PlacingSlip::find($request->slip_number);

    if (!$slip) {
        return back()->with('msg', 'Error: Source record not found.');
    }

    // 3. Check if an invoice already exists for this slip_number
    $exists = Invoice::where('slip_number', $slip->id)->exists();

    if ($exists) {
        return back()->with('msg', 'Error: Invoice already exists for Slip #' . $slip->id);
    }

    // 4. Create the Invoice
    // We map the fields from the Slip ($slip) to the Invoice
    Invoice::create([
        'user'                 => Auth::user()->name ?? 'System',
        'slip_number'          => $slip->id,
        'client_name'          => $slip->insured, // Matches your $data["insured"]
        'principal_address'    => $slip->principal_address,
        'policy_start_date'    => $slip->policy_start_date,
        'policy_expiry_date'   => $slip->policy_expiry_date,
        'insurer'              => $slip->insurer,
        'policy_name'          => $slip->insurance_policy,
        'policy_currency'      => $slip->policy_currency,
        'total_sum_insured'    => $slip->total_sum_insured,
        'basic_rate'           => $slip->basic_rate,
        'basic_premium'        => $slip->basic_premium,
        'premium_levy'         => $slip->premium_levy,
        'discount_rate'        => $slip->discount_rate,
        'premium_levy_rate'    => $slip->premium_levy_rate,
        'gross_premium'        => $slip->gross_premium,
        'commission_rate'      => $slip->commission_rate,
        'commission_amount'    => $slip->commission_amount,
        'insurer_premium'      => $slip->insurer_premium,
    ]);

    return back()->with('msg', 'Invoice successfully created.');
}

// DOWNLOAD PDF INVOICE
    public function downloadInvoice($id)
    {
        // 1. Fetch matching record or throw 404 exit
        $invoice = Invoice::where('invoice_number', $id)->firstOrFail();

        // 2. Resolve Banking configurations based on Currency profile signatures
        $bankDetails = [
            'bank' => 'First Capital Bank Zambia Ltd',
            'acc_no' => ($invoice->policy_currency === 'USD') ? '0003205014188 (USD)' : '0003202005675 (ZMW)'
        ];

        // 3. Construct Structural QR Generator Vector Payload String
        $qrRawText = "RIB INV:" . $invoice->invoice_number . " | " . $invoice->client_name . " | Amt:" . $invoice->policy_currency . $invoice->gross_premium;

        // =================================================================
        // NATIVE SECURE QR CODE GENERATION (100% OFFLINE VIA BACONQRCODE)
        // =================================================================
        try {
            // Instantiate the native renderer to compile high-quality vector paths
            $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
            $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150, 1);
            $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $renderer));
            
            // Generate a real, 100% scannable vector SVG string natively offline
            $realSvgData = $writer->writeString($qrRawText);
            $qrString = 'data:image/svg+xml;base64,' . base64_encode($realSvgData);
        } catch (\Exception $e) {
            // Safe network fallback ONLY if local rendering engine hits an unexpected execution barrier
            $apiBackupUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrRawText);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiBackupUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            $qrBytes = curl_exec($ch);
            curl_close($ch);

            if (!empty($qrBytes)) {
                $qrString = 'data:image/png;base64,' . base64_encode($qrBytes);
            } else {
                $qrString = ''; // Clear out cleanly if completely blocked by firewalls
            }
        }

        // 4. MULTI-TENANT DYNAMIC LOGO RESOLUTION
        $tenantId = tenant('id');
        $logoPath = storage_path("app/public/tenants/{$tenantId}/logo.jpg");
        
        if (!file_exists($logoPath)) {
            $logoPath = public_path('img/profstand.jpg');
        }

        // Convert branding asset stream into an inline base64 data URL string
        $logoUrl = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $mimeType = @mime_content_type($logoPath) ?: 'image/jpeg';
            $logoUrl  = 'data:' . $mimeType . ';base64,' . $logoData;
        }

        // 5. MULTI-TENANT HEADER RESOLUTION (via Admin User Context)
        $adminUser = \App\Models\User::where('role', 'admin')->first();

        $companyName = $adminUser->company ?? 'Profstand';
        $address     = $adminUser->physical_address ?? 'System Error';
        $phone       = $adminUser->tel_number ?? 'System Error';
        $email       = $adminUser->email ?? 'admin@profstand.com';

        // 6. Bind parameters and map execution to view layer
        $pdf = Pdf::loadView('insurance_broking.accounts.invoices.generate_pdf', [
            'invoice'       => $invoice,
            'bankDetails'   => $bankDetails,
            'qrString'      => $qrString, // Now contains your unified, highly flexible dataURI base64 payload string
            'logoUrl'       => $logoUrl,      
            'companyName'   => $companyName,
            'address'       => $address,
            'phone'         => $phone,
            'email'         => $email,
            'dateFormatted' => now()->format('d M Y')
        ]);

        // 7. Apply standard options and print configurations
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->setPaper('a4', 'portrait');

        // 8. Stream transmission download payload directly to client window
        $filename = "INV" . str_pad($invoice->invoice_number, 4, "0", STR_PAD_LEFT) . ".pdf";
        return $pdf->download($filename);
    }
    

// MONTHLY BUSINESS REPORT
public function monthlyBusiness_report(Request $request)
{
    $pageTitle = 'Monthly Report';
    
    // 1. Extract Filter Request State Contexts
    $period = $request->input('period', 'current');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');

    // 2. Initialize Base Query with Strict 'Active' constraint rule profile
    $query = Invoice::where('invoice_status', 'Active');

    // 3. Apply Multi-Range Time Horizon Constraints
    if ($period === 'current') {
        $query->whereMonth('policy_start_date', Carbon::now()->month)
              ->whereYear('policy_start_date', Carbon::now()->year);
              
    } elseif ($period === 'last') {
        $lastMonth = Carbon::now()->subMonth();
        $query->whereMonth('policy_start_date', $lastMonth->month)
              ->whereYear('policy_start_date', $lastMonth->year);
              
    } elseif ($period === 'custom' && !empty($fromDate) && !empty($toDate)) {
        // Safe inclusive check against standard range arrays
        $query->whereBetween('policy_start_date', [$fromDate, $toDate]);
    }
    // Note: 'all' skips time horizon filters entirely

    // 4. Fetch the execution dataset collection ordered by latest records
    $invoices = $query->orderBy('policy_start_date', 'desc')->get();

    // 5. Compute structural summaries split by currency
    // Assuming the database contains structural variants like 'USD' and 'ZMW'
    $totalUSD = $invoices->where('policy_currency', 'USD')->sum('gross_premium');
    $totalZMW = $invoices->where('policy_currency', 'ZMW')->sum('gross_premium');

    return view('insurance_broking.accounts.invoices.monthly_business_done', compact(
        'invoices', 
        'pageTitle', 
        'period', 
        'fromDate', 
        'toDate', 
        'totalUSD',
        'totalZMW'
    ));
}


// SLIP CANCELLATION
public function slip_cancellation(Request $request)
{
    // 1. Validate incoming form inputs securely
    $request->validate([
        'id'                     => 'required|integer',
        'manual_refund'          => 'nullable|numeric|min:0',
        'remaining_days'         => 'nullable|integer|min:0',
        'cancellation_date_from' => 'nullable|date',
        'cancellation_date_to'   => 'nullable|date',
        'remarks'                => 'required|string|max:1000',
        'is_reversal'            => 'nullable'
    ]);

    $slipId        = (int) $request->input('id');
    $isReversal    = $request->has('is_reversal');
    $premiumRefund = (float) $request->input('manual_refund', 0.00);
    $dateFrom      = $request->input('cancellation_date_from');
    $dateTo        = $request->input('cancellation_date_to');
    
    $userRemarks   = trim($request->input('remarks', 'Manual Cancellation'));
    $remarks       = $isReversal ? "[FULL REVERSAL] " . $userRemarks : $userRemarks;

    $cancelledBy      = auth()->user()->name ?? 'System';
    $cancellationDate = now()->toDateString(); 

    try {
        // 2. Execute everything within a robust transactional block
        DB::transaction(function () use ($slipId, $isReversal, $premiumRefund, $dateFrom, $dateTo, $remarks, $cancelledBy, $cancellationDate) {
            
            // Find the parent slip and lock it
            $slip = PlacingSlip::where('id', $slipId)->lockForUpdate()->first();

            if (!$slip) {
                throw new \Exception("Slip record #{$slipId} not found.");
            }

            // Prevent Duplicate Cancellation States
            if ($slip->status === 'Cancelled') {
                throw new \Exception("Notice: Slip #{$slipId} was already cancelled.");
            }

            // Relationship Action 1: Create the log (slip_id is automatically assigned by Eloquent!)
            $slip->cancellations()->create([
                'insurance_policy'       => $slip->insurance_policy,
                'insured_name'           => $slip->insured, 
                'basic_premium'          => $slip->basic_premium,
                'premium_refund'         => $premiumRefund,
                'policy_currency'        => $slip->policy_currency,
                'cancelled_by'           => $cancelledBy,
                'cancellation_date'      => $cancellationDate,
                'cancellation_date_from' => $dateFrom,
                'cancellation_date_to'   => $dateTo,
                'remarks'                => $remarks,
            ]);

            // Action 2: Update the parent slip itself
            $targetStatus = $isReversal ? 'Cancelled' : $slip->status;
            $slip->update([
                'status'         => $targetStatus,
                'date_cancelled' => $cancellationDate,
                'remarks'        => $remarks,
                'cancelled_by'   => $cancelledBy
            ]);

            // Relationship Action 3: Update child invoices safely
            $invoiceStatus = $isReversal ? 'Cancelled' : 'Active';
            $slip->invoices()->update([
                'invoice_status' => $invoiceStatus,
                'date_cancelled' => $cancellationDate,
                'remarks'        => $remarks,
                'cancelled_by'   => $cancelledBy
            ]);
        });

        // 3. Success Message Handling
        $msg = $isReversal 
            ? "Slip #{$slipId} cancelled successfully." 
            : "Slip #{$slipId} logs updated (Partial Cancellation processed).";

        return redirect()->back()->with(['msg' => $msg, 'msg_type' => 'success']);

    } catch (\Exception $e) {
        // 4. Error/Notice Catch Block
        logger()->error("Error handling Slip Cancellation: " . $e->getMessage());

        $msgType = str_contains($e->getMessage(), 'Notice:') ? 'info' : 'danger';
        return redirect()->back()->with(['msg' => $e->getMessage(), 'msg_type' => $msgType]);
    }
}


//CREATE CREDIT NOTE
public function createCreditNote(Request $request)
{
    // 1. Validation
    $request->validate([
        'slip_id' => 'required'
    ]);

    $slip_id = $request->slip_id;
    Log::info("Credit Note Process Started for Slip ID: " . $slip_id);

    // Then in the controller you can do:
    $cancellationData = SlipCancellation::where('slip_id', $slip_id)->first();

    // 2. Fetch Data (Using Query Builder for 'slip_cancellation')
    $cancellationData = DB::table('slip_cancellation')
        ->where('slip_id', $slip_id)
        ->first();

    if ($cancellationData) {
        try {
            // 3. Create the Credit Note
            CreditNote::create([
                'slip_id'           => $slip_id,
                'insurance_policy'  => $cancellationData->insurance_policy,
                'insured_name'      => $cancellationData->insured_name,
                'basic_premium'     => $cancellationData->basic_premium,
                'premium_refund'    => $cancellationData->premium_refund,
                'policy_currency'   => $cancellationData->policy_currency,
                'cancelled_by'      => $cancellationData->cancelled_by,
                'cancellation_date' => $cancellationData->cancellation_date,
                'remarks'           => $cancellationData->remarks,
                'processed_by'      => Auth::user()->name ?? 'System User',
            ]);

            Log::info("Credit Note Insert Success for Slip ID: " . $slip_id);

            // In Laravel, back() with a session message replaces the JS redirect
            return back()->with('msg', 'Credit Note successfully created.');

        } catch (\Exception $e) {
            Log::error("SQL Execution Error: " . $e->getMessage());
            return back()->with('msg', 'Insert Error: ' . $e->getMessage());
        }
    }

    // 4. Handle Missing Record
    Log::warning("Source Record Missing for ID: " . $slip_id);
    return back()->with('msg', "Error: Cancellation details not found for ID: " . $slip_id);
}


//CREATE RECEIPT
public function createReceipt(Request $request)
{
    // 1. Validation
    $request->validate([
        'invoice_number' => 'required',
        'gross_amount_received' => 'required|numeric|min:0.01',
        'receipt_date' => 'required|date',
    ]);

    $invoice_no = $request->invoice_number;
    $amt_received = (float)$request->gross_amount_received;

    // 2. Fetch original invoice data
    $inv = Invoice::where('invoice_number', $invoice_no)->first();

    if (!$inv) {
        return back()->with('msg', "Error: Invoice number $invoice_no not found.");
    }

    if ($inv->status === 'Fully Paid') {
        return back()->with('msg', "Error: This invoice is already Fully Paid.");
    }

    // 3. Calculate previous payments and new status
    $previous_paid = Receipt::where('invoice_number', $invoice_no)->sum('gross_amount_received');
    $gross_premium = (float)$inv->gross_premium;
    $total_so_far = $previous_paid + $amt_received;

    // Determine Status
    $new_status = ($total_so_far >= ($gross_premium - 0.01)) ? 'Fully Paid' : 'Partial Payment';

    // 4. Execute Transaction
    return DB::transaction(function () use ($inv, $request, $amt_received, $new_status) {
        
        // --- CALCULATIONS ---
        $comm_rate = (float)($inv->commission_rate ?? 0);
        if ($comm_rate >= 1) { $comm_rate /= 100; }

        $basic_received = round(($amt_received / 1.05), 2);
        $levy_received  = round(($amt_received - $basic_received), 2);
        $commission_received = round(($basic_received * $comm_rate), 2);
        $insurer_premium_received = round(($basic_received - $commission_received), 2);

        // 5. Create CashBook Entry
        Receipt::create([
            'invoice_number'          => $inv->invoice_number,
            'client_name'             => $inv->client_name,
            'insurer'                 => $inv->insurer,
            'policy_name'             => $inv->policy_name,
            'policy_start_date'       => $inv->policy_start_date,
            'policy_expiry_date'      => $inv->policy_expiry_date,
            'policy_currency'         => $inv->policy_currency,
            'total_sum_insured'       => $inv->total_sum_insured,
            'basic_rate'              => $inv->basic_rate,
            'basic_premium'           => $inv->basic_premium,
            'premium_levy_rate'       => $inv->premium_levy_rate,
            'premium_levy'            => $inv->premium_levy,
            'gross_premium'           => $inv->gross_premium,
            'commission_rate'         => $inv->commission_rate,
            'commission_amount'       => $inv->commission_amount,
            'insurer_premium'         => $inv->insurer_premium,
            'user'                    => Auth::user()->name ?? 'System',
            'description'             => $request->description,
            'payment_method'          => $request->payment_method,
            'payment_ref'             => $request->payment_ref,
            'reference_no'            => $request->reference_no,
            'gross_amount_received'   => $amt_received,
            'basic_premium_received'  => $basic_received,
            'premium_levy_received'   => $levy_received,
            'rib_commission_received' => $commission_received,
            'insurer_premium_received'=> $insurer_premium_received,
            'receipt_date'            => $request->receipt_date,
        ]);

        // 6. Update Invoice Status
        $inv->update(['status' => $new_status]);

        return redirect()->route('your.index.route')->with('msg', 'Receipt successfully created.');
    });
}


//REMITTANCE REPORT
public function getRemittanceReport(Request $request)
{
    // 1. Get parameters from the request (URL query or Form)
    $insurer_name = $request->insurer_name;
    $from_date = $request->from_date;
    
    // 2. Handle the default date (Laravel/Carbon version of date('Y-m-d'))
    $to_date = $request->to_date ?: Carbon::today()->toDateString();

    // 3. The Query
    $reportData = InsurerRemittance::whereBetween('remittance_date', [$from_date, $to_date])
        // Optional: If you want to filter by insurer name like the function name suggests
        ->when($insurer_name, function ($query, $insurer_name) {
            return $query->where('insurer_name', $insurer_name);
        })
        ->orderBy('remittance_date', 'asc')
        ->get(); // Returns a Collection

    return $reportData;
}

//PIA REPORT
public function getRemittanceReport_pia($selected_insurer, $from_date, $to_date = '') 
{


    // 1. Default to-date to today if empty
    $to_date = $to_date ?: Carbon::today()->toDateString();

    // 2. Build the query
    return InsurerRemittance::whereBetween('remittance_date', [$from_date, $to_date])
        // The 'when' method replaces your if($hasInsurer) logic
        ->when($selected_insurer, function ($query, $selected_insurer) {
            return $query->where('insurer_name', $selected_insurer);
        })
        ->orderBy('remittance_date', 'asc')
        ->get()
        ->toArray(); // Converts the Collection to an associative array
}

//SHOW PIA REPORT

public function showPiaReport(Request $request) 
{
    // --- PUT IT HERE ---
    $request->validate([
        'from_date' => 'required|date',
        'to_date'   => 'nullable|date|after_or_equal:from_date',
    ]);

    // Now that we know the data is safe, run the query
    $insurer = $request->insurer;
    $from = $request->from_date;
    $to = $request->to_date;

    $report = $this->getRemittanceReport_pia($insurer, $from, $to);

    return view('reports.pia', compact('report'));
}


//REMITTANCE INFO
public function remittance_infor() 
{
    // Fetches all records from the 'insurer_remittances' table
    // Returns a Laravel Collection
    $remittance_infor = InsurerRemittance::all();

    // If your existing code strictly requires a plain PHP array:
    // return $remittance_infor->toArray();

    return $remittance_infor;
    
}

// public function remittance_infor() 
// {
//     // Fetches 15 records per page
//     return InsurerRemittance::paginate(15);
// }

//BANK ALLOCATIONS
public function allocate_receipt($receipt_id, $bank_id) 
{
    try {
        return DB::transaction(function () use ($receipt_id, $bank_id) {
            
            // 1. Fetch the bank transaction first
            // findOrFail handles the "transaction not found" exception automatically
            $bankData = BankTransaction::findOrFail($bank_id);
            $bank_ref = $bankData->reference_number;

            // 2. Update Receipt (Cash Book)
            // Using where() on receipt_number as per your original logic
            Receipt::where('receipt_number', $receipt_id)->update([
                'allocation_status'   => 'allocated',
                'bank_transaction_id' => $bank_id,
                'bank_reference'      => $bank_ref,
                'allocated_at'        => now(), // Laravel helper for current time
            ]);

            // 3. Update Bank Transaction status and link
            $bankData->update([
                'status'            => 'allocated',
                'linked_receipt_id' => $receipt_id
            ]);

            return true;
        });

    } catch (\Exception $e) {
        // Log the specific error to storage/logs/laravel.log
        Log::error("Allocation Error: " . $e->getMessage());
        return false;
    }
}

//VOUCHER REGISTRATION
public function storeVoucher(Request $request)
{
    // 1. Validation: Ensure all required data is present
    $validatedData = $request->validate([
        'payee_name'       => 'required|string|max:255',
        'amount'           => 'required|numeric',
        'currency'         => 'required|string|max:10',
        'payment_method'   => 'required|string',
        'description'      => 'nullable|string',
        'expense_category' => 'required|string',
    ]);

    // 2. Create the record
    // We map your form input names (payee_name) to DB columns (client_name)
    try {
        PaymentVoucher::create([
            'client_name'      => $validatedData['payee_name'],
            'amount'           => $validatedData['amount'],
            'currency'         => $validatedData['currency'],
            'payment_method'   => $validatedData['payment_method'],
            'description'      => $validatedData['description'],
            'expense_category' => $validatedData['expense_category'],
            'created_by'       => Auth::user()->name ?? 'System',
        ]);

        // 3. Success Redirect (Replaces the <script> alert)
        return redirect()->route('vouchers.index')
            ->with('msg', 'Voucher submitted and is now PENDING approval.');

    } catch (\Exception $e) {
        // Return back with the error message if the insert fails
        return back()->withInput()->with('msg', 'Error: ' . $e->getMessage());
    }
}


//PAYMENT VOUCHER INFOR
public function paymentVoucherInfo()
{
    try {
        // Use orderByRaw to handle the specific CASE logic for statuses
        return PaymentVoucher::orderByRaw("CASE WHEN status = 'Pending' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

    } catch (\Exception $e) {
        // Laravel's built-in logging replaces error_log()
        Log::error("Payment Voucher Retrieval Error: " . $e->getMessage());
        return collect([]); // Return an empty collection to avoid view errors
    }
}

// APPROVED-PAYMENT VOUCHER INFFORMATION
public function paymentVoucherInforApproved()
{
    try {
        // We use orderByRaw to prioritize 'Approved' status, 
        // then sort everything by the most recent creation date.
        return PaymentVoucher::orderByRaw("CASE WHEN status = 'Approved' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

    } catch (\Exception $e) {
        // Laravel's built-in logging replaces error_log()
        Log::error("Approved Payment Voucher Retrieval Error: " . $e->getMessage());
        
        // Returning an empty collection ensures your @foreach loop in Blade doesn't crash
        return collect([]); 
    }
}

// APPROVE OR REJECT VOUCHER REQUEST
public function approveOrRejectVoucher($id, $action)
{
    // 1. Find the voucher or throw a 404
    $voucher = PaymentVoucher::findOrFail($id);

    // 2. Determine the status based on the action parameter
    $newStatus = ($action === 'approve') ? 'Approved' : 'Rejected';

    // 3. Update the record
    $voucher->update([
        'status'      => $newStatus,
        'approved_at' => now(), // Laravel helper for current date/time
        'approved_by' => Auth::user()->name ?? 'Unknown System User',
    ]);

    // 4. Redirect back with a flash message
    return back()->with('msg', "Voucher #{$id} has been successfully {$newStatus}.");
}

// UPDATE VOURCHERS
/**
 * Updates a payment voucher record.
 * 
 * @param Request $request
 * @param int $id
 * @return \Illuminate\Http\RedirectResponse
 */
public function editVoucher(Request $request, $id)
{
    // 1. Validation
    $validatedData = $request->validate([
        'client_name'    => 'required|string|trim',
        'amount'         => 'required|numeric',
        'currency'       => 'required|string',
        'payment_method' => 'required|string',
        'description'    => 'nullable|string|trim',
        'created_at'     => 'required|date',
    ]);

    try {
        // 2. Find the voucher
        $voucher = PaymentVoucher::findOrFail($id);

        // 3. Perform the update
        // Laravel automatically updates the 'updated_at' column for you.
        $voucher->update([
            'client_name'    => $validatedData['client_name'],
            'amount'         => $validatedData['amount'],
            'currency'       => $validatedData['currency'],
            'payment_method' => $validatedData['payment_method'],
            'description'    => $validatedData['description'],
            'created_at'     => $validatedData['created_at'],
            'updated_by'     => Auth::user()->name ?? 'Unknown User',
        ]);

        return back()->with('msg', 'Voucher updated successfully.');

    } catch (\Exception $e) {
        Log::error("Voucher Update Error: " . $e->getMessage());
        return back()->with('msg', 'Error updating voucher: ' . $e->getMessage());
    }
}


/**
     * Display the specific Placing Slip.
     * Uses Route Model Binding to fetch the Placement automatically.
     */
    // public function showSlip(PlacingSlip $placingSlip)
    // {
    //     return view('insurance_broking.placement_slips.show', compact('placement'));
    // }

    // public function showSlip(PlacingSlip $placingSlip)
    // {
    //     return view('insurance_broking.placement_slips.show', [
    //         'placement' => $placingSlip
    //     ]);
    // }

   public function showSlip($id)
    {
        $placingSlip = PlacingSlip::findOrFail($id);

        return view('insurance_broking.placement_slips.show', [
            'placement' => $placingSlip,
            'pageTitle' => 'Placing Slip Details' // Adds page title contextual string here
        ]);
    }

    public function showCancelledSlip($id)
    {
        // Find the distinct cancellation entry or default back to null
        $cancellations = SlipCancellation::where('slip_id', $id)->first();

        return view('insurance_broking.cancelled_slips.show', [
            'cancellations' => $cancellations,
            'idFromUrl'    => $id,
            'pageTitle'    => 'View Cancellation',
            'isEmpty'      => !SlipCancellation::exists()
        ]);
    }

    // EDIT CANCELLED SLIPS
    public function editCancelledSlip($id)
    {
        return view('insurance_broking.cancelled_slips.edit', [
            'cancellations' => SlipCancellation::where('slip_id', $id)->first(),
            'idFromUrl'     => $id,
            'pageTitle'    => 'EDIT CANCELLED SLIP',
            'isEmpty'       => !SlipCancellation::exists()
        ]);
    }

    public function updateCancelledSlip(Request $request, $id)
{
    // 1. Validate inputs (including the two new period fields)
    $validated = $request->validate([
        'cancellation_date'      => 'required|date',
        'cancellation_date_from' => 'nullable|date',
        'cancellation_date_to'   => 'nullable|date|after_or_equal:cancellation_date_from',
        'cancelled_by'           => 'required|string|max:255',
        'remarks'                => 'nullable|string',
        'policy_currency'        => 'required|string|max:10',
        'basic_premium'          => 'required|numeric',
        'premium_refund'         => 'required|numeric',
    ]);

    // 2. Find and update the record smoothly via Eloquent
    $cancellation = SlipCancellation::where('slip_id', $id)->firstOrFail();
    $cancellation->update($validated);

    // 3. Redirect back to the form wrapper layout with flash session data
    // Tip: If your GET route is named differently (e.g. view_list), redirect there!
    return redirect()->back()
                     ->with('success', 'Cancellation advice records successfully updated.');
}

    // EDIT/UPDATE SLIPS
    /**
     * Show the edit form.
     */
    public function editSlip(PlacingSlip $PlacingSlip, $id)
    {
        // Fetch placement info or fail with a 404 error if not found
        $placement = PlacingSlip::findOrFail($id);

        return view('insurance_broking.placement_slips.edit', compact('PlacingSlip', 'placement'));
    }

    /**
     * Update the placement slip resource.
     */
  
public function updateSlip(Request $request, $id)
{
    // 1. Find the parent record
    $placement = PlacingSlip::findOrFail($id);

    // 2. Validate all fields matching your native code
    $validated = $request->validate([
        'insured'               => 'required|string|max:255',
        'nature_of_business'    => 'nullable|string',
        'principal_address'     => 'required|string',
        'policy_start_date'     => 'required|date',
        'policy_expiry_date'    => 'nullable|date',
        'insurer'               => 'nullable|string',
        'cancellation_clause'   => 'required|string',
        'placing_slip_clause'   => 'nullable|string',
        'insurance_policy'      => 'nullable|string',
        'scope_of_cover'        => 'nullable|string',
        'extensions'            => 'nullable|string',
        'excess_deductible'     => 'nullable|string',
        'property_insured'      => 'nullable|string',
        'location_of_risk'      => 'nullable|string',
        'specific_warranties'   => 'nullable|string',
        'specific_conditions'   => 'nullable|string',
        'policy_currency'       => 'nullable|string',
        'total_sum_insured'     => 'nullable|numeric',
        'basic_rate'            => 'nullable|numeric',
        'basic_premium'         => 'nullable|numeric',
        'discount_rate'         => 'nullable|numeric',
        'discount'              => 'nullable|numeric',
        'premium_levy_rate'     => 'nullable|numeric',
        'premium_levy'          => 'nullable|numeric',
        'gross_premium'         => 'nullable|numeric',
        'commission_rate'       => 'nullable|numeric',
        'commission_amount'     => 'nullable|numeric',
        'payment_made'          => 'nullable|numeric|min:0',
        'insurer_premium'       => 'nullable|numeric', 
        'payment_method'        => 'nullable|string',
        'premium_status'        => 'nullable|string',
        'status'                => 'nullable|string',
    ]);

    // ======================================================================
    // BACKEND SAFETY VALVE: Normalize rates to database decimals (e.g. 15 -> 0.15)
    // ======================================================================
    if (($validated['commission_rate'] ?? 0) >= 1)   $validated['commission_rate'] /= 100;
    if (($validated['discount_rate'] ?? 0) >= 1)     $validated['discount_rate'] /= 100;
    if (($validated['premium_levy_rate'] ?? 0) >= 1) $validated['premium_levy_rate'] /= 100;
    if (($validated['basic_rate'] ?? 0) >= 1)        $validated['basic_rate'] /= 100;
    // ======================================================================

    // 3. Fallback handlers & Type Sanitization (Reading from $validated, NOT raw $request)
    $validated['insurer_premium']   = (float)($validated['insurer_premium'] ?? 0);
    $validated['commission_rate']   = (float)($validated['commission_rate'] ?? 0); 
    $validated['commission_amount'] = (float)($validated['commission_amount'] ?? 0);
    $validated['premium_levy']      = (float)($validated['premium_levy'] ?? 0);
    $validated['gross_premium']     = (float)($validated['gross_premium'] ?? 0);
    $validated['basic_premium']     = (float)($validated['basic_premium'] ?? 0);
    $validated['basic_rate']        = (float)($validated['basic_rate'] ?? 0);
    $validated['discount_rate']     = (float)($validated['discount_rate'] ?? 0);
    $validated['premium_levy_rate'] = (float)($validated['premium_levy_rate'] ?? 0);
    $validated['total_sum_insured'] = (float)($validated['total_sum_insured'] ?? 0);
    
    $validated['payment_method']    = $validated['payment_method'] ?? 'Standard';

    // 4. Track user sessions
    $currentUserName = Auth::user()->name ?? 'System';
    $validated['user'] = $currentUserName;
    $validated['updated_by'] = $currentUserName;

    // 5. Wrap updates in a transaction to match dual-query integrity
    DB::beginTransaction();

    try {
        // Update placing slip
        $placement->update($validated);

        // Update corresponding invoice automatically using cleaned/normalized data
        Invoice::where('slip_number', $id)->update([
            'updated_by'          => $currentUserName,
            'client_name'         => $request->input('insured'),
            'principal_address'   => $request->input('principal_address'),
            'policy_start_date'   => $request->input('policy_start_date'),
            'policy_expiry_date'  => $request->input('policy_expiry_date'),
            'insurer'             => $request->input('insurer'),
            'policy_name'         => $request->input('insurance_policy'),
            'policy_currency'     => $request->input('policy_currency'),
            'total_sum_insured'   => $validated['total_sum_insured'],
            'basic_rate'          => $validated['basic_rate'],
            'basic_premium'       => $validated['basic_premium'],
            'premium_levy'        => $validated['premium_levy'],
            'discount_rate'       => $validated['discount_rate'],
            'premium_levy_rate'   => $validated['premium_levy_rate'],
            'gross_premium'       => $validated['gross_premium'],
            'commission_rate'     => $validated['commission_rate'], // Saves the clean decimal (0.15)
            'commission_amount'   => $validated['commission_amount'],
            'insurer_premium'     => $validated['insurer_premium'],
        ]);

        DB::commit();

        return redirect()->route('insurance_broking.placement_slips.edit', $id)
            ->with('success', 'Slip details saved successfully. Corresponding invoice updated automatically.');

    } catch (\Exception $e) {
        DB::rollBack();

        return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
    }
}

    /**
     * Generate and download the Placing Slip PDF.
     */
  public function generateSlipPdf($id)
    {
        // 1. Fetch the data or fail with a 404 cleanly
        $placement = PlacingSlip::findOrFail($id);

        // 2. Format & Clean Extensions Text into an Array
        $extensionsList = [];
        if (!empty($placement->extensions)) {
            $extArray = preg_split('/\r\n|\r|\n/', trim($placement->extensions));
            foreach ($extArray as $item) {
                $cleanItem = trim($item);
                if ($cleanItem != "") {
                    // Strip out bullet points, dashes, and number formatting prefixes
                    $cleanItem = preg_replace('/^([•\-\*]|\d+\.)\s*/u', '', $cleanItem);
                    
                    // Identify if it's a structural header or a standard bullet item
                    $isHeader = (preg_replace('/^Section\s+[A-Z]/i', '', $cleanItem) !== $cleanItem);
                    
                    $extensionsList[] = [
                        'text' => $cleanItem,
                        'is_header' => $isHeader
                    ];
                }
            }
        }

        // 3. Build the QR Text Source Payload
        $qrRawText = "REF: " . $placement->id . 
                    " | Insured: " . $placement->insured . 
                    " | Insurer: " . $placement->insurer . 
                    " | Premium: " . $placement->policy_currency . " " . $placement->gross_premium;

        // =================================================================
        // NATIVE SECURE QR CODE GENERATION (100% OFFLINE VIA BACONQRCODE)
        // =================================================================
        try {
            // Instantiate the native renderer to compile high-quality vector paths
            $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
            $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150, 1);
            $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $renderer));
            
            // Generate a real, 100% scannable vector SVG string natively offline
            $realSvgData = $writer->writeString($qrRawText);
            $qrString = 'data:image/svg+xml;base64,' . base64_encode($realSvgData);
        } catch (\Exception $e) {
            // Safe network fallback ONLY if local rendering engine hits an unexpected environment issue
            $apiBackupUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrRawText);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiBackupUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            $qrBytes = curl_exec($ch);
            curl_close($ch);

            if (!empty($qrBytes)) {
                $qrString = 'data:image/png;base64,' . base64_encode($qrBytes);
            } else {
                $qrString = ''; // Clear out cleanly if completely blocked by firewalls
            }
        }

        // 4. Formatted Number Computations 
        $numbers = [
            'sumInsured'     => number_format((float)$placement->total_sum_insured, 2),
            'basicRate'      => number_format((float)$placement->basic_rate, 4),
            'basicPremium'   => number_format((float)$placement->basic_premium, 2),
            'discountRate'   => number_format((float)$placement->discount_rate, 2),
            'discount'       => number_format((float)$placement->discount, 2),
            'levyRate'       => number_format((float)$placement->premium_levy_rate, 2),
            'levyAmount'     => number_format((float)$placement->premium_levy, 2),
            'grossPremium'   => number_format((float)$placement->gross_premium, 2),
            'insurerPremium' => number_format((float)$placement->insurer_premium, 2),
        ];

        // 5. Multi-Tenant Dynamic Logo & Header Resolution
        $tenantId = tenant('id'); 
        $adminUser = \App\Models\User::where('role', 'admin')->first();

        $companyName = $adminUser->company ?? 'Profstand';
        $address     = $adminUser->physical_address ?? 'Plot Number 14 Njoka Road, Lusaka';
        $phone       = $adminUser->tel_number ?? '+260 572313599';
        $email       = $adminUser->email ?? 'services@profstand.com';

        $logoPath = storage_path("app/public/tenants/{$tenantId}/logo.jpg");
        if (!file_exists($logoPath)) {
            $logoPath = public_path('img/profstand_logo.jpg');
        }

        // Convert branding asset stream into an inline base64 data URL string
        $logoUrl = '';
        if (file_exists($logoPath) && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $mimeType = @mime_content_type($logoPath) ?: 'image/jpeg'; 
            $logoUrl  = 'data:' . $mimeType . ';base64,' . trim($logoData);
        }

        // 6. Direct execution execution via Barryvdh wrapper
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('insurance_broking.placement_slips.pdf_slip', compact(
                'placement',
                'extensionsList',
                'qrString', // Now safely parsed locally as an image data string
                'numbers',
                'companyName',
                'address',
                'phone',
                'email',
                'logoUrl'
            ));

            // Configure options for canvas rendering and cross-site asset resolution
            $pdf->setPaper('A4', 'portrait')
                ->setWarnings(false);
                
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

            // Output as inline stream ('I') directly back to browser view context
            return $pdf->stream("SLPN{$placement->id}.pdf");

        } catch (\Exception $e) {
            return back()->with('error', 'Could not generate PDF document via Dompdf: ' . $e->getMessage());
        }
    }



    /**
     * Generate and download the Key Fact Statement (KFS) PDF document.
     */
  public function generateKfsPdf($id)
    {
        // 1. Safely fetch record or abort with a clean 404 error if missing
        $placement = PlacingSlip::findOrFail($id);

        // 2. Logic to clean, strip markers, and format Extensions into standard Array elements
        $extensionsList = [];
        if (!empty($placement->extensions)) {
            $extArray = preg_split('/\r\n|\r|\n/', trim($placement->extensions));
            foreach ($extArray as $item) {
                $cleanItem = trim($item);
                if ($cleanItem !== "") {
                    // Strip existing bullets, hyphens, or numerical prefixes
                    $cleanItem = preg_replace('/^([•\-\*]|\d+\.)\s*/u', '', $cleanItem);
                    
                    // Match structurally isolated text partitions (e.g., "Section A")
                    $isHeader = preg_match('/^Section\s+[A-Z]/i', $cleanItem);
                    
                    $extensionsList[] = [
                        'text' => $cleanItem,
                        'is_header' => $isHeader
                    ];
                }
            }
        }

        // 3. Prepare QR Raw Data Payload for the Key Fact Statement
        $qrRawText = "KFS REF: " . $placement->id . 
                    " | Client: " . $placement->insured . 
                    " | Total Due: " . $placement->policy_currency . " " . $placement->gross_premium;

        // =================================================================
        // NATIVE SECURE QR CODE GENERATION (100% OFFLINE VIA BACONQRCODE)
        // =================================================================
        try {
            // Instantiate the native renderer to compile high-quality vector paths
            $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
            $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150, 1);
            $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $renderer));
            
            // Generate a real, 100% scannable vector SVG string natively offline
            $realSvgData = $writer->writeString($qrRawText);
            $qrString = 'data:image/svg+xml;base64,' . base64_encode($realSvgData);
        } catch (\Exception $e) {
            // Safe network fallback ONLY if local rendering engine hits an unexpected environment issue
            $apiBackupUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrRawText);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiBackupUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            $qrBytes = curl_exec($ch);
            curl_close($ch);

            if (!empty($qrBytes)) {
                $qrString = 'data:image/png;base64,' . base64_encode($qrBytes);
            } else {
                $qrString = ''; // Clear out cleanly if completely blocked by firewalls
            }
        }

        // 4. Compute Formatted Currency Variables
        $sumInsured = number_format((float)$placement->total_sum_insured, 2);
        $grossPremium = number_format((float)$placement->gross_premium, 2);

        // 5. Multi-Tenant Dynamic Logo & Header Resolution
        $tenantId = tenant('id'); 
        $adminUser = \App\Models\User::where('role', 'admin')->first();

        $companyName = $adminUser->company ?? '';
        $address     = $adminUser->physical_address ?? '';
        $phone       = $adminUser->tel_number ?? '';
        $email       = $adminUser->email ?? '';

        $logoPath = storage_path("app/public/tenants/{$tenantId}/logo.jpg");
        if (!file_exists($logoPath)) {
            $logoPath = public_path('img/rib_logo.jpg');
        }

        // Convert branding asset stream into an inline base64 data URL string
        $logoUrl = '';
        if (file_exists($logoPath) && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $mimeType = @mime_content_type($logoPath) ?: 'image/jpeg'; 
            $logoUrl  = 'data:' . $mimeType . ';base64,' . trim($logoData);
        }

        // 6. Execute View Generation via Dompdf
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('insurance_broking.placement_slips.pdf_kfs', compact(
                'placement',
                'extensionsList',
                'qrString', // Injected safely as an inline Base64 dataURI
                'sumInsured',
                'grossPremium',
                'companyName',
                'address',
                'phone',
                'email',
                'logoUrl'
            ));

            $pdf->setPaper('a4', 'portrait')
                ->setWarnings(false);

            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

            return $pdf->stream("KFS_{$placement->id}.pdf");

        } catch (\Exception $e) {
            return back()->with('error', 'Could not generate Key Fact Statement document: ' . $e->getMessage());
        }
    }


                    
    /**
     * Handle the cancellation logic for a Placing Slip.
     */
    // public function cancelSlip(Request $request)
    // {
    //     $validated = $request->validate([
    //         'id'            => 'required|exists:slip_cancellation,id',
    //         'remarks'       => 'required|string|max:2000',
    //         'manual_refund' => 'required|numeric|min:0'
    //     ]);

    //     // Using a transaction to ensure database integrity
    //     DB::transaction(function () use ($validated) {
    //         $placement = PlacingSlip::lockForUpdate()->find($validated['id']);
            
    //         $placement->update([
    //             'status'               => 'Cancelled',
    //             'cancellation_remarks' => $validated['remarks'],
    //             'refund_amount'        => $validated['manual_refund'],
    //             'cancelled_at'         => now(),
    //         ]);
    //     });

    //     return back()->with([
    //         'msg'      => "Placing Slip #{$validated['id']} has been successfully cancelled.",
    //         'msg_type' => 'success'
    //     ]);
    // }

   public function cancelSlip(Request $request)
{
    $validated = $request->validate([
        'id'            => 'required|exists:slip_register,id',
        'remarks'       => 'required|string|max:2000',
        'manual_refund' => 'required|numeric|min:0',
        'is_reversal'   => 'nullable',
        'date_from'     => 'nullable|date',
        'date_to'       => 'nullable|date',
    ]);

    try {
        DB::transaction(function () use ($validated) {
            // 1. Fetch and Lock the row
            $placement = PlacingSlip::lockForUpdate()->findOrFail($validated['id']);

            if ($placement->status === 'Cancelled') {
                throw new \Exception("Slip #{$validated['id']} was already cancelled.");
            }

            $isReversal = isset($validated['is_reversal']);
            $remarks = $isReversal ? "[FULL REVERSAL] " . $validated['remarks'] : $validated['remarks'];

            // 2. Create the log entry using your model
            SlipCancellation::create([
                'slip_id'                => $placement->id,
                'insurance_policy'       => $placement->insurance_policy,
                'insured_name'           => $placement->insured,
                'basic_premium'          => $placement->basic_premium,
                'premium_refund'         => $validated['manual_refund'],
                'policy_currency'        => $placement->policy_currency,
                'cancelled_by'           => auth()->user()->name ?? 'System',
                'cancellation_date'      => now(),
                'cancellation_date_from' => $validated['date_from'] ?? null,
                'cancellation_date_to'   => $validated['date_to'] ?? null,
                'remarks'                => $remarks,
            ]);

            // 3. Update the main placement record
            $placement->update([
                'status'               => $isReversal ? 'Cancelled' : $placement->status,
                'cancellation_remarks' => $remarks,
                'refund_amount'        => $validated['manual_refund'],
                'cancelled_at'         => now(),
            ]);

            // 4. Update the related invoice
            DB::table('invoices')->where('slip_number', $placement->id)->update([
                'invoice_status' => $isReversal ? 'Cancelled' : 'Active',
                'remarks'        => $remarks,
                'cancelled_by'   => auth()->user()->name ?? 'System',
            ]);
        });

        return back()->with([
            'msg'      => "Placing Slip #{$validated['id']} has been successfully cancelled.",
            'msg_type' => 'success'
        ]);

    } catch (\Exception $e) {
        \Log::error("Cancellation failed: " . $e->getMessage());

        return back()->with([
            'msg'      => $e->getMessage(),
            'msg_type' => 'danger'
        ]);
    }
}

    // SHOW/VIEW CLAIM DETAILS
    public function showClaim($id)
    {
        $claim = Claim::findOrFail($id); // Or your specific model name
        return view('insurance_broking.claims.show', compact('claim'));
    }


    // RENEW POLICY
    public function renew(Request $request)
    {
        try {
            $id = $request->input('id');
            
            if (!$id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Policy ID received.'
                ], 400);
            }

            // --- RENEWAL LOGIC ---
            // Instead of the Renewal class, we use a database transaction
            $newId = DB::transaction(function () use ($id) {
                $oldSlip = PlacingSlip::findOrFail($id);
                
                // Replicate the slip (creates a clone of the model)
                $newSlip = $oldSlip->replicate();
                
                // Update fields for the new term
                $newSlip->status = 'active'; // or 'draft'
                $newSlip->parent_id = $id;   // Link to previous policy
                $newSlip->date_registered = now();
                
                // Logic for dates (e.g., adding 1 year)
                if ($oldSlip->expiry_date) {
                    $newSlip->inception_date = $oldSlip->expiry_date;
                    $newSlip->expiry_date = \Carbon\Carbon::parse($oldSlip->expiry_date)->addYear();
                }

                $newSlip->save();
                return $newSlip->id;
            });

            return response()->json([
                'success' => true,
                'new_id' => $newId
            ]);

        } catch (\Exception $e) {
            Log::error("Renewal Failed for ID $id: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'The renewal process failed: ' . $e->getMessage()
            ], 500);
        }
    }


    // VIEW LIST OF VARIOUS ITEMS

    public function view_list(Request $request)
    {
        $action = $request->query('action', 'view_slip_list');
        
        // Data needed for all views
        $data = [
            'action' => $action,
            'pageTitle' => 'VIEW LIST',
        ];

        // Conditional data loading based on action
        if ($action == 'view_slip_list') {
            $data['placements'] = PlacingSlip::whereIn('status', ['Active', 'Renewed', 'Expired'])->get();
        } elseif ($action == 'view_claim_list') {
            $data['claims'] = Claim::all();
        } elseif ($action == 'view_vehicle_list') {
            $data['vehicles'] = Vehicle::all();
        }

        return view('insurance_broking.view_list.index', $data);
    }

    // VIEW LIST OF SLIPS

    public function viewSlip_list(Request $request)
    {
        
        
        // Data needed for all views
        $data = [
            'pageTitle' => 'VIEW SLIP LIST',
        ];

        // Conditional data loading based on action
        
            $data['placements'] = PlacingSlip::where('status', 'Active')->get();

        return view('insurance_broking.partials.slip_list', $data);
    }

    // VIEW CANCELLED LIST OF SLIPS

   // VIEW CANCELLED SLIP LIST
public function viewCancelledSlip_list(Request $request)
{
    // 1. Data needed for layout contexts and tab states
    $data = [
        'pageTitle' => 'VIEW SLIPS CANCELLED',
    ];

    // 2. Fixed syntax error and assigned clean model payload retrieval
    $data['cancellations'] = SlipCancellation::get();

    // 3. Return view with data payload mapping
    return view('insurance_broking.partials.cancelled_slips', $data);
}

    // CLAIM LIST
    // VIEW CLAIM LIST
    public function viewClaim_list(Request $request)
    {
        // Data needed for the layout views
        $data = [
            'pageTitle' => 'VIEW CLAIM LIST',
        ];

        // Fetches all rows from the claims database table cleanly
        $data['claims'] = Claim::all();

        return view('insurance_broking.partials.claim_list', $data);
    }




    // VEHICLE UPLOAD TEMPLATE
    public function downloadTemplate(): StreamedResponse
    {
        $filename = "vehicle_import_template.csv";

        $headers = [
            'slip_number', 'insurer_name', 'client_name', 'reg_number', 
            'vehicle_make', 'chassis_number', 'engine_number', 'policy_start_date', 
            'policy_expiry_date', 'policy_type', 'policy_currency', 'sum_insured', 'total_premium'
        ];

        $exampleRow = [
            'SLPN001', 'General Insurance', 'John Smith', 'ABC-123', 
            'Toyota Hilux', 'VIN123456789', 'ENG987654', '2024-01-01', 
            '2025-01-01', 'Comprehensive', 'USD', '20000.00', '500.00'
        ];

        return new StreamedResponse(function () use ($headers, $exampleRow) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, $headers);
            fputcsv($handle, $exampleRow);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }


    // QUOTATIONS CLONE SLIP DATA
    // public function create_quotation(Request $request)
    // {
    
    //     // Directly look for the slip ID passed from the button click
    //     $cloneId = $request->query('clone_id'); 
        
    //     // Find the record if it exists, otherwise keep it null for a blank form
    //     $templateSlip = $cloneId ? PlacingSlip::find($cloneId) : null;

    //     return view('insurance_broking.register', compact('templateSlip'));
    // }

    public function create_quotation(Request $request)
    {
        $cloneId = $request->query('clone_id'); 
        $templateSlip = $cloneId ? PlacingSlip::find($cloneId) : null;

        // Define the section variable that the view layout is begging for
        $section = "Quotations"; 

        return view('insurance_broking.register', compact('templateSlip', 'section'));
    }

   //BULK VEHICLE UPLOAD
//    public function bulkStore(Request $request)
//     {
//         $request->validate([
//             'vehicle_file' => 'required|mimes:csv,txt|max:2048',
//         ]);

//         $file = $request->file('vehicle_file');
//         $path = $file->getRealPath();
//         $data = array_map('str_getcsv', file($path));

//         // Remove the header row
//         $headers = array_shift($data);

//         try {
//             DB::beginTransaction();

//             foreach ($data as $row) {
//                 // Map CSV columns to Database columns
//                 // Ensure the order matches your CSV template exactly
//                 Vehicle::create([
//                     'slip_number'        => $row[0],
//                     'insurer_name'       => $row[1],
//                     'client_name'        => $row[2],
//                     'reg_number'         => $row[3],
//                     'vehicle_make'       => $row[4],
//                     'chassis_number'     => $row[5],
//                     'engine_number'      => $row[6],
//                     'policy_start_date'  => $row[7],
//                     'policy_expiry_date' => $row[8],
//                     'policy_type'        => $row[9],
//                     'policy_currency'    => $row[10],
//                     'sum_insured'        => $row[11],
//                     'total_premium'      => $row[12],
//                 ]);
//             }

//             DB::commit();
//             return back()->with('success', 'Vehicles imported successfully!');

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return back()->with('error', 'Error during import: ' . $e->getMessage());
//         }
//     }







    
}
