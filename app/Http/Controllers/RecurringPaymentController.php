<?php

namespace App\Http\Controllers;

use App\Models\RecurringPayment;
use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\BillPayment;
// use App\Models\Mail\BillPaymentCreate;
// use App\Models\Mail\SendWorkspaceInvication;
use App\Models\ProductServiceCategory;
use App\Models\Transaction;
use App\Models\RecurringTransaction;
use App\Models\Utility;
use App\Models\Tax;
use App\Models\Vender;
use Illuminate\Support\Facades\Mail;


class RecurringPaymentController extends Controller
{
    public function index(Request $request)
    {
         // dd(\Auth::user()->can('manage recurringpayment'));
         //  dd(\Auth::user()->load('roles.permissions'));   
        if(\Auth::user()->can('manage recurringpayment'))
        {
            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');

            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('Select Account', '');

            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 2)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');


            $query = RecurringPayment::where('created_by', '=', \Auth::user()->creatorId());

            // if (str_contains($request->date, ' to ')) { 
            //     $date_range = explode(' to ', $request->date);
            //     $query->whereBetween('date', $date_range);
            // }elseif(!empty($request->date)){
               
            //     $query->where('date', $request->date);
            // }
            
            // if(!empty($request->date))
            // {
            //     $date_range = explode(' to ', $request->date);
            //     $query->whereBetween('date', $date_range);
            // }

            if(!empty($request->vender))
            {
                $query->where('id', '=', $request->vender);
            }
            if(!empty($request->account))
            {
                $query->where('account_id', '=', $request->account);
            }

            if(!empty($request->category))
            {
                $query->where('category_id', '=', $request->category);
            }


            $recurringpayments = $query->get();
            // dd($payments);
            return view('recurringpayment.index', compact('recurringpayments', 'account', 'category', 'vender'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        // dd(\Auth::user()->can('create recurringpayment'));
        // dd(\Auth::user()->load('roles.permissions'));   
        if(\Auth::user()->can('create recurringpayment'))
        {
            $venders = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            // $venders->prepend('--', 0);
            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 2)->get()->pluck('name', 'id');
            $accounts   = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $taxes = Tax::get()->pluck('name', 'id');
            return view('recurringpayment.create', compact('venders', 'categories', 'accounts', 'taxes'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function store(Request $request)
    {
        //  dd(\Auth::user()->can('create recurringpayment'));
        // dd(\Auth::user()->load('roles.permissions'));   
        if(\Auth::user()->can('create recurringpayment'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'recurring_date' => 'required',
                                   'end_date' => 'required',
                                   'period' => 'required',
                                   'policy_number' => 'required',
                                   'amount' => 'required',
                                   'account_id' => 'required',
                                   'category_id' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $recurringpayment = new RecurringPayment();
            $recurringpayment->recurring_date  = $request->recurring_date;
            $recurringpayment->end_date  = $request->end_date;
            $recurringpayment->period    = $request->period;
            $recurringpayment->policy_number   = $request->policy_number;
            $recurringpayment->tax_id     = $request->tax_id;
            $recurringpayment->amount      = $request->amount;
            $recurringpayment->account_id = $request->account_id;
            $recurringpayment->vender_id   = $request->vender_id;
            $recurringpayment->category_id  = $request->category_id;
            $recurringpayment->payment_method = 0;
            $recurringpayment->reference      = $request->reference;
            $recurringpayment->description    = $request->description;
            if(!empty($request->add_receipt))
            {
                $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                // $request->add_receipt->storeAs('uploads/payment', $fileName);
                $recurringpayment->add_receipt = $fileName;
                


                $dir        = 'uploads/payment';
                $path = Utility::upload_file($request,'add_receipt',$fileName,$dir,[]);
                // $request->add_receipt  = $path['url'];
                if($path['flag'] == 1){
                    $url = $path['url'];
                }else{
                    return redirect()->back()->with('error', __($path['msg']));
                }
                $recurringpayment->save();
            }
            $recurringpayment->created_by     = \Auth::user()->creatorId();
            $recurringpayment->save();

            $category            = ProductServiceCategory::where('id', $request->category_id)->first();
            $recurringpayment->recurringpayment_id = $recurringpayment->id;
            $recurringpayment->type       = 'RecurringPayment';
            $recurringpayment->category   = $category->name;
            $recurringpayment->user_id    = $recurringpayment->vender_id;
            $recurringpayment->user_type  = 'Vender';
            $recurringpayment->account    = $request->account_id;

            RecurringTransaction::addRecurringTransaction($recurringpayment);

            $vender          = Vender::where('id', $request->vender_id)->first();
            $recurringpayment         = new BillPayment();
            $recurringpayment->name   = !empty($vender) ? $vender['name'] : '';
            // $recurringpayment->method = '-';
            $recurringpayment->recurring_date   = \Auth::user()->dateFormat($request->recurring_date);
            $recurringpayment->amount = \Auth::user()->priceFormat($request->amount);
            // $recurringpayment->bill   = '';

            if(!empty($vender))
            {
                Utility::userBalance('vendor', $vender->id, $request->amount, 'debit');
            }

            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');

            $uArr = [
                'payment_name' => $recurringpayment->name,
                // 'payment_bill' => $recurringpayment->bill,
                'payment_amount' => $recurringpayment->amount,
                'payment_date' => $recurringpayment->recurring_date,
                // 'payment_method' => $recurringpayment->method,
            ];
            // dd($uArr);
            try
            {
                $resp = Utility::sendEmailTemplate('new_bill_payment', [$vender->id => $vender->email], $uArr);
            }
            
            catch(\Exception $e)
            {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }

            //Twilio Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $vender = Vender::find($request->vender_id);
            if(isset($setting['recurringpayment_notification']) && $setting['recurringpayment_notification'] ==1)
            {
                $msg = __("New recurring payment of").' ' . \Auth::user()->priceFormat($request->amount) . __("created for").' ' . $vender->name . __("by").' '.  $recurringpayment->type . '.';
                Utility::send_twilio_msg($vender->contact,$msg);
            }

            return redirect()->route('recurringpayment.index')->with('success', __('Recurring Payment successfully created.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(RecurringPayment $recurringpayment)
    {

           

        if(\Auth::user()->can('edit recurringpayment'))
        {
            $venders = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $venders->prepend('--', 0);
            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->where('type', '=', 2)->pluck('name', 'id');
            $taxes = Tax::get()->pluck('name', 'id');
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('recurringpayment.edit', compact('venders', 'categories', 'accounts' , 'recurringpayment',  'taxes'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, RecurringPayment $recurringpayment)
    {
        // dd(\Auth::user()->can('edit recurringpayment'));
        // dd(\Auth::user()->load('roles.permissions')); 
        if(\Auth::user()->can('edit recurringpayment'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'recurring_date' => 'required',
                                   'end_date' => 'required',
                                   'period' => 'required',
                                   'policy_number' => 'required',
                                   'amount' => 'required',
                                   'account_id' => 'required',
                                   'vender_id' => 'required',
                                   'category_id' => 'required',
                                   'tax_id' => 'required'
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $vender = Vender::where('id', $request->vender_id)->first();
            if(!empty($vender))
            {
                Utility::userBalance('vendor', $vender->id, $recurringpayment->amount, 'credit');
            }
            Utility::bankAccountBalance($recurringpayment->account_id, $recurringpayment->amount, 'credit');

            $recurringpayment->recurring_date          = $request->recurring_date;
            $recurringpayment->end_date           = $request->end_date;
            $recurringpayment->period           = $request->period;
            $recurringpayment->policy_number           = $request->policy_number;
            $recurringpayment->tax_id           = $request->tax_id;
            $recurringpayment->amount         = $request->amount;
            $recurringpayment->account_id     = $request->account_id;
            $recurringpayment->vender_id      = $request->vender_id;
            $recurringpayment->category_id    = $request->category_id;
            $recurringpayment->payment_method = 0;
            $recurringpayment->reference      = $request->reference;
            $recurringpayment->description    = $request->description;
            if(!empty($request->add_receipt))
            {
                $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                // $request->add_receipt->storeAs('uploads/payment', $fileName);
                 $recurringpayment->add_receipt = $fileName;
                $dir        = 'uploads/payment';
                $path = Utility::upload_file($request,'add_receipt',$fileName,$dir,[]);

                if($path['flag'] == 1){
                    $url = $path['url'];
                }else{
                    return redirect()->back()->with('error', __($path['msg']));
                }

                // $request->add_receipt  = $path['url'];
                 $recurringpayment->save();
            }
             $recurringpayment->save();

            $category            = ProductServiceCategory::where('id', $request->category_id)->first();
             $recurringpayment->category   = $category->name;
             $recurringpayment->recurringpayment_id =  $recurringpayment->id;
             $recurringpayment->type       = 'RecurringPayment';
             $recurringpayment->account    = $request->account_id;

            //  dd('AKUSJDGGAS');
            try{
            RecurringTransaction::editRecurringTransaction( $recurringpayment);

            if(!empty($vender))
            {
                Utility::userBalance('vendor', $vender->id, $request->amount, 'debit');
            }

            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');
        } catch (\Throwable $rex){
            // dd($rex);
        }
            return redirect()->route('recurringpayment.index')->with('success', __(' Recurring Payment successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(RecurringPayment $recurringpayment)
    {

        //   dd(\Auth::user()->can('delete recurringpayment'));
        // dd(\Auth::user()->load('roles.permissions')); 
        if(\Auth::user()->can('delete recurringpayment'))
        {
            if($recurringpayment->created_by == \Auth::user()->creatorId())
            {
                $recurringpayment->delete();
                $type = 'RecurringPayment';
                $user = 'Vender';
                RecurringTransaction::destroyRecurringTransaction($recurringpayment->id, $type, $user);

                if($recurringpayment->vender_id != 0)
                {
                    Utility::userBalance('vendor', $recurringpayment->vender_id, $recurringpayment->amount, 'credit');
                }
                Utility::bankAccountBalance($recurringpayment->account_id , $recurringpayment->amount, 'credit');

                return redirect()->route('recurringpayment.index')->with('success', __('Recurring Payment successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
