<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    protected $fillable = [
        'account',
        'type',
        'amount',
        'description',
        'recurring_date',
        'created_by',
        'customer_id',
        'recurringpayment_id',
    ];


    public function bankAccount()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'account')->first();
    }


    public static function addRecurringTransaction($request)
    {
        $transaction  = new RecurringTransaction();
        $transaction->account = $request->account;
        $transaction->user_id = $request->user_id;
        $transaction->user_type = $request->user_type;
        $transaction->type = $request->type;
        $transaction->amount = $request->amount;
        $transaction->description = $request->description;
        $transaction->recurring_date  = $request->recurring_date;
        $transaction->created_by = $request->created_by;
        $transaction->recurringpayment_id  = $request->recurringpayment_id;
        $transaction->category    = $request->category;
        $transaction->save();
    }

    public static function editRecurringTransaction($request)
    {
        $transaction              = RecurringTransaction::where('recurringpayment_id', $request->recurringpayment_id)->where('type', $request->type)->first();

        $transaction->account     = $request->account;
        $transaction->amount      = $request->amount;
        $transaction->description = $request->description;
        $transaction->recurring_date  = $request->recurring_date;
        $transaction->category    = $request->category;
        $transaction->save();
    }



    public static function destroyRecurringTransaction($id, $type, $user)
    {

       RecurringTransaction::where('recurringpayment_id', $id)->where('type', $type)->where('user_type', $user)->delete();
    }

    public function recurringpayment()
    {
        return $this->hasOne('App\Models\InvoicePayment', 'id', 'recurringpayment_id');
    }

    public function billPayment()
    {
        return $this->hasOne('App\Models\BillPayment', 'id', 'recurringpayment_id');
    }

 }