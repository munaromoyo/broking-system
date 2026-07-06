<?php
namespace App\Http\Controllers;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class BankController extends Controller
{

    public function importBankTransactions(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip the header row
        fgetcsv($handle);

        $count = 0;

        DB::beginTransaction();

        try {
            while (($column = fgetcsv($handle, 10000, ",")) !== FALSE) {
                // Check if row is empty
                if (empty($column[0])) continue;

                BankTransaction::create([
                    'transaction_date' => Carbon::parse($column[0])->format('Y-m-d'),
                    'value_date'       => Carbon::parse($column[1])->format('Y-m-d'),
                    'description'      => $column[2],
                    'reference_number' => $column[3],
                    'currency'         => $column[4] ?? 'ZMW',
                    'debits'           => (float)($column[5] ?? 0),
                    'credits'          => (float)($column[6] ?? 0),
                    'balance'          => (float)($column[7] ?? 0),
                    'status'           => 'unallocated',
                ]);

                $count++;
            }

            DB::commit();
            fclose($handle);

            return redirect()->back()->with('status', "Successfully imported $count transactions!");

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->back()->with('error', "Error during import: " . $e->getMessage());
        }
    }


    //BANK TRANSACTIONS
    public function bank_transactions() 
    {
        // Fetches all records as a Collection of objects
        return BankTransaction::all();
    }





}
