<?php

namespace App\Http\Controllers;

use App\Models\CryptoCurrency;
use App\Models\TransactionCurrency;
use App\Models\UserHolding;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserHolding(Request $request)
    {
        $user = $request->user();
        $UserHolding= UserHolding::with(['cryptoCurrency'])->whereUserId($user->id)->get()->toArray();
        if($UserHolding){
            return response()->json([
                'code'=>200,
                'status' => 'true',
                'message' => 'User Holding',
                'data' => $UserHolding,
            ], 200);
        } else{
            return response()->json([
                'code'=>400,
                'status' => 'false',
                'message' => 'User Holding not found',
                'data' => [],
            ], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserTransaction(Request $request)
    {
        $user = $request->user();
        $UserHolding= TransactionCurrency::with(['cryptoCurrency'])->whereUserId($user->id)->get()->toArray();
        if($UserHolding){
            return response()->json([
                'code'=>200,
                'status' => 'true',
                'message' => 'User Transaction History',
                'data' => $UserHolding,
            ], 200);
        } else{
            return response()->json([
                'code'=>400,
                'status' => 'false',
                'message' => 'User Transactions not found',
                'data' => [],
            ], 400);
        }
    }

    public function addBuySell(Request $request)
    {
        $user=$request->user();
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'currency_id' => 'required',
            'action' => 'required|in:0,1',
            'quantity' => 'required',
            'average' => 'required',
            'amount'=>'required',
            'invested' => 'required|numeric|min:0',
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => 'false',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Find the UserHolding record based on user_id and currency_id
        $userHolding = UserHolding::where('user_id', $user->id)
            ->where('currency_id', $request->currency_id)
            ->first();

        if ($userHolding) {
            // Update the existing UserHolding record
            if ($request->action == 0) {
                $userHolding->quantity -= $request->quantity;
            } else if ($request->action == 1) {
                $userHolding->quantity += $request->quantity;
            }

            $userHolding->update([
                'quantity'=>$request->quantity,
                'average' => $request->average,
                'invested' => $request->invested,
            ]);
        } else {
            // Create a new UserHolding record
            $userHolding = UserHolding::create([
                'user_id' => $request->user_id,
                'currency_id' => $request->currency_id,
                'quantity' => $request->quantity,
                'average' => $request->average,
                'invested' => $request->invested,
            ]);
        }
        TransactionCurrency::create([
            'user_id' => $user->id,
            'currency_id' => $request->currency_id,
            'transaction_type' => $request->action,
            'current_price' => $request->current_price,
            'quantity' => $request->quantity,
            'amount' => $request->amount,
            'transaction_date' => now(),
        ]);

        // Return the success response
        return response()->json([
            'code' => 200,
            'status' => 'true',
            'message' => 'User Holding created or updated successfully',
            'data' => [],
        ], 200);
    }
    public function getCurrency(Request $request)
    {
        $currency = CryptoCurrency::all();
        if($currency){
            return response()->json([
            'code'=>200,
            'status' => 'true',
            'message' => 'Total currency',
            'data' => $currency,
        ], 200);
        } else{
            return response()->json([
                'code'=>400,
                'status' => 'false',
                'message' => 'Currency not found',
                'data' => [],
            ], 400);
        }

    }

}
