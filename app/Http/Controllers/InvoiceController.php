<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\Mail\CustomerInvoiceSend;
use App\Models\Mail\InvoicePaymentCreate;
use App\Models\Mail\InvoiceSend;
use App\Models\Mail\PaymentReminder;
use App\Models\Milestone;
use App\Models\Products;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\StockReport;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\Utility;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoiceExport;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['invoice', 'payinvoice','export']]);
    }

    public function index(Request $request)
    {

        if (\Auth::user()->can('manage invoice')) {

            $customer = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $customer->prepend('Select Customer', '');

            $status = Invoice::$statues;

            $query = Invoice::where('created_by', '=', \Auth::user()->creatorId());

            if (!empty($request->customer)) {
                $query->where('customer_id', '=', $request->customer);
            }

            if (str_contains($request->issue_date, ' to ')) {
                $date_range = explode(' to ', $request->issue_date);
                $query->whereBetween('issue_date', $date_range);
            }elseif(!empty($request->issue_date)){

                $query->where('issue_date', $request->issue_date);
            }

            // if (!empty($request->issue_date)) {
            //     $date_range = explode(' to ', $request->issue_date);
            //     $query->whereBetween('issue_date', $date_range);
            // }

            if (!empty($request->status)) {
                $query->where('status', '=', $request->status);
            }
            $invoices = $query->get();

            return view('invoice.index', compact('invoices', 'customer', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create($customerId)
    {

        if (\Auth::user()->can('create invoice')) {
            $customFields   = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
            $invoice_number = \Auth::user()->invoiceNumberFormat($this->invoiceNumber());
            $customers      = Customer::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $customers->prepend('Select Customer', '');
            $category = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 1)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $product_services = ProductService::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $product_services->prepend('--', '');

            return view('invoice.create', compact('customers', 'invoice_number', 'product_services', 'category', 'customFields', 'customerId'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function customer(Request $request)
    {
        $customer = Customer::where('id', '=', $request->id)->first();

        return view('invoice.customer_detail', compact('customer'));
    }

    public function product(Request $request)
    {

        $data['product']     = $product = ProductService::find($request->product_id);
        $data['unit']        = (!empty($product->unit())) ? $product->unit()->name : '';
        $data['taxRate']     = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        $data['taxes']       = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $salePrice           = $product->sale_price;
        $quantity            = 1;
        $taxPrice            = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity);

        return json_encode($data);
    }

    public function store(Request $request)
    {

        if (\Auth::user()->can('create invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'customer_id' => 'required',
                    'issue_date' => 'required',
                    'due_date' => 'required',
                    'category_id' => 'required',
                    'items' => 'required',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $status = Invoice::$statues;

            $invoice                 = new Invoice();
            $invoice->invoice_id     = $this->invoiceNumber();
            $invoice->customer_id    = $request->customer_id;
            $invoice->status         = 0;
            $invoice->issue_date     = $request->issue_date;
            $invoice->due_date       = $request->due_date;
            $invoice->category_id    = $request->category_id;
            $invoice->ref_number     = $request->ref_number;
            $invoice->discount_apply = isset($request->discount_apply) ? 1 : 0;
            $invoice->created_by     = \Auth::user()->creatorId();
            $invoice->save();

            Utility::starting_number( $invoice->invoice_id + 1, 'invoice');
            CustomField::saveData($invoice, $request->customField);
            $products = $request->items;

            for ($i = 0; $i < count($products); $i++) {
                $invoiceProduct              = new InvoiceProduct();
                $invoiceProduct->invoice_id  = $invoice->id;
                $invoiceProduct->product_id  = $products[$i]['item'];
                $invoiceProduct->quantity    = $products[$i]['quantity'];
                $invoiceProduct->tax         = $products[$i]['tax'];
                // $invoiceProduct->discount    = (isset($request->discount_apply)) ? isset($products[$i]['discount']) ? $products[$i]['discount'] : 0: 0;
                $invoiceProduct->discount    = $products[$i]['discount'];
                $invoiceProduct->price       = $products[$i]['price'];
                $invoiceProduct->description = $products[$i]['description'];
                $invoiceProduct->save();

                Utility::total_quantity('minus',$invoiceProduct->quantity,$invoiceProduct->product_id);

                //Product Stock Report
                $type='invoice';
                $type_id = $invoice->id;
                $description=$invoiceProduct->quantity.'  '.__(' quantity sold in invoice').' '. \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
                Utility::addProductStock( $products[$i]['item'],$invoiceProduct->quantity,$type,$description,$type_id);

            }
            //Twilio Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $customer = Customer::find($request->customer_id);
            if(isset($setting['invoice_notification']) && $setting['invoice_notification'] ==1)
            {
                $msg = __("New Invoice").' '. \Auth::user()->invoiceNumberFormat($invoice->invoice_id).' '. __("created by").' ' .\Auth::user()->name.'.';
                Utility::send_twilio_msg($customer->contact,$msg);
            }
            return redirect()->route('invoice.index', $invoice->id)->with('success', __('Invoice successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($ids)
    {
        if (\Auth::user()->can('edit invoice')) {
            $id      = Crypt::decrypt($ids);
            $invoice = Invoice::find($id);

            $invoice_number = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            $customers      = Customer::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $category       = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 1)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $product_services = ProductService::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $invoice->customField = CustomField::getData($invoice, 'invoice');
            $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();

            return view('invoice.edit', compact('customers', 'product_services', 'invoice', 'invoice_number', 'category', 'customFields'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Invoice $invoice)
    {
        if (\Auth::user()->can('edit bill')) {
            if ($invoice->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'customer_id' => 'required',
                        'issue_date' => 'required',
                        'due_date' => 'required',
                        'category_id' => 'required',
                        'items' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('invoice.index')->with('error', $messages->first());
                }
                $invoice->customer_id    = $request->customer_id;
                $invoice->issue_date     = $request->issue_date;
                $invoice->due_date       = $request->due_date;
                $invoice->ref_number     = $request->ref_number;
                $invoice->discount_apply = isset($request->discount_apply) ? 1 : 0;
                $invoice->category_id    = $request->category_id;
                $invoice->save();
                CustomField::saveData($invoice, $request->customField);
                $products = $request->items;

                for ($i = 0; $i < count($products); $i++) {
                    $invoiceProduct = InvoiceProduct::find($products[$i]['id']);
                    // Utility::total_quantity('minus',$invoiceProduct->quantity,$invoiceProduct->product_id);


                    if ($invoiceProduct == null) {
                        $invoiceProduct             = new InvoiceProduct();
                        $invoiceProduct->invoice_id = $invoice->id;
                        Utility::total_quantity('minus',$products[$i]['quantity'],$products[$i]['item']);
                    }
                    else{

                        Utility::total_quantity('minus',$invoiceProduct->quantity,$invoiceProduct->product_id);
                    }

                    if (isset($products[$i]['item'])) {
                        $invoiceProduct->product_id = $products[$i]['item'];
                    }

                    $invoiceProduct->quantity    = $products[$i]['quantity'];
                    $invoiceProduct->tax         = $products[$i]['tax'];
                    $invoiceProduct->discount    = $products[$i]['discount'];
                    $invoiceProduct->price       = $products[$i]['price'];
                    $invoiceProduct->description = $products[$i]['description'];
                    $invoiceProduct->save();
                    // Utility::total_quantity('plus',$products[$i]['quantity'],$invoiceProduct->product_id);

                    if($products[$i]['id'] > 0){
                        Utility::total_quantity('plus',$products[$i]['quantity'],$invoiceProduct->product_id);
                    }else{
                        Utility::total_quantity('plus',$products[$i]['quantity'],$products[$i]['item']);
                    }

                    //Product Stock Report
                    $type='invoice';
                    $type_id = $invoice->id;
                    StockReport::where('type','=','invoice')->where('type_id' ,'=', $invoice->id)->delete();
                    $description=$products[$i]['quantity'].'  '.__(' quantity sold in invoice').' '. \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
                    if(empty($products[$i]['id'])){
                        Utility::addProductStock( $products[$i]['item'],$products[$i]['quantity'],$type,$description,$type_id);
                    }

                    // Utility::addProductStock( $products[$i]['item'],$products[$i]['quantity'],$type,$description,$type_id);

                }

                return redirect()->route('invoice.index')->with('success', __('Invoice successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function invoiceNumber()
    {
        $latest = Utility::getValByName('invoice_starting_number');
        return $latest ;
    }

     public function show($ids)
    {

        if (\Auth::user()->can('show invoice')) {
            $id      = Crypt::decrypt($ids);
            $invoice = Invoice::find($id);
            if ($invoice->created_by == \Auth::user()->creatorId()) {
                $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->first();
                $sum_of_tax = 0;

                $customer = $invoice->customer;
                $iteams   = $invoice->items;
                $total_sum_of_taxes = 0;
                $taxes_arr = [];
                foreach($iteams as $item){
                    $taxes = Utility::tax($item->tax);
                    $sum_of_taxes = 0;
                    foreach($taxes as $tax){
                        $taxPrice=Utility::taxRate($tax->rate ,$item->price,$item->quantity,$item->discount) ;
                        $sum_of_taxes = $sum_of_taxes + $taxPrice;
                    }
                    $obj = [
                        'id' => (int)$tax->id,
                        'name' => $tax->name,
                        'sum' => $sum_of_taxes
                    ];
                    array_push($taxes_arr, $obj);
                    $total_sum_of_taxes = $sum_of_taxes+ $total_sum_of_taxes;

                }
                $temp = [];
                foreach($taxes_arr as $value) {
                    if(!array_key_exists($value['id'], $temp)) {
                        $temp[$value['id']] = 0;
                    }
                    $temp[$value['id']] += $value['sum'];
                }
                $temp2 = [];
                foreach($temp as $key => $t1){
                    $tax = Utility::tax($key);
                    if($tax == null){
                        continue;
                    }
                    // dd($tax[0]['name']);
                    try{
                        $obj = [
                            'id' => $key,
                            'name' => $tax[0]['name'],
                            'sum' => $t1
                        ];
                    } catch (\Throwable $rex){
                        dd($tax, $key, $t1);
                    }
                    array_push($temp2, $obj);
                }
                // dd($temp2);
                $invoice->customField = CustomField::getData($invoice, 'invoice');
                $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
                // dd($invoice);
                return view('invoice.view', compact('temp2', 'total_sum_of_taxes', 'invoice', 'customer', 'iteams', 'invoicePayment', 'customFields'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function destroy(Invoice $invoice)
    {

        if (\Auth::user()->can('delete invoice'))
        {
            if ($invoice->created_by == \Auth::user()->creatorId())
            {
                foreach($invoice->payments as $invoices)
                {
                    Utility::bankAccountBalance($invoices->account_id, $invoices->amount, 'debit');
                    $invoices->delete();
                }
                $invoice->delete();
                if ($invoice->customer_id != 0) {
                    Utility::userBalance('customer', $invoice->customer_id, $invoice->getTotal(), 'debit');
                }
                InvoiceProduct::where('invoice_id', '=', $invoice->id)->delete();

                return redirect()->route('invoice.index')->with('success', __('Invoice successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function productDestroy(Request $request)
    {

        if (\Auth::user()->can('delete invoice product')) {
            InvoiceProduct::where('id', '=', $request->id)->delete();

            return redirect()->back()->with('success', __('Bill product successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customerInvoice(Request $request)
    {
        if (\Auth::user()->can('manage customer invoice')) {

            $status = Invoice::$statues;

            $query = Invoice::where('customer_id', '=', \Auth::user()->id)->where('status', '!=', '0')->where('created_by', \Auth::user()->creatorId());

            if (str_contains($request->issue_date, ' to ')) {
                $date_range = explode(' to ', $request->issue_date);
                $query->whereBetween('issue_date', $date_range);
            }elseif(!empty($request->issue_date)){

                $query->where('issue_date', $request->issue_date);
            }

            // if (!empty($request->issue_date)) {
            //     $date_range = explode(' to ', $request->issue_date);
            //     $query->whereBetween('issue_date', $date_range);
            // }

            if (!empty($request->status)) {
                $query->where('status', '=', $request->status);
            }
            $invoices = $query->get();

            return view('invoice.index', compact('invoices', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function customerInvoiceShow($id)
    {

        if (\Auth::user()->can('show invoice')) {
            $invoice_id = Crypt::decrypt($id);
            $invoice    = Invoice::where('id', $invoice_id)->first();
            if ($invoice->created_by == \Auth::user()->creatorId()) {
                $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->first();
                $sum_of_tax = 0;
                $customer = $invoice->customer;
                $iteams   = $invoice->items;
                $company_payment_setting = Utility::getCompanyPaymentSetting();
                $total_sum_of_taxes = 0;
                $taxes_arr = [];
                foreach($iteams as $item){
                    $taxes = Utility::tax($item->tax);
                    $sum_of_taxes = 0;
                    foreach($taxes as $tax){
                        $taxPrice=Utility::taxRate($tax->rate ,$item->price,$item->quantity,$item->discount) ;
                        $sum_of_taxes = $sum_of_taxes + $taxPrice;
                    }
                    $obj = [
                        'id' => (int)$tax->id,
                        'name' => $tax->name,
                        'sum' => $sum_of_taxes
                    ];
                    array_push($taxes_arr, $obj);
                    $total_sum_of_taxes = $sum_of_taxes+ $total_sum_of_taxes;

                }
                $temp = [];
                foreach($taxes_arr as $value) {
                    if(!array_key_exists($value['id'], $temp)) {
                        $temp[$value['id']] = 0;
                    }
                    $temp[$value['id']] += $value['sum'];
                }
                $temp2 = [];
                foreach($temp as $key => $t1){
                    $tax = Utility::tax($key);
                    if($tax == null){
                        continue;
                    }
                    // dd($tax[0]['name']);
                    try{
                        $obj = [
                            'id' => $key,
                            'name' => $tax[0]['name'],
                            'sum' => $t1
                        ];
                    } catch (\Throwable $rex){
                        dd($tax, $key, $t1);
                    }
                    array_push($temp2, $obj);
                }
                // dd($temp2);
                $invoice->customField = CustomField::getData($invoice, 'invoice');
                $customFields         = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
                // dd($invoice);
                return view('invoice.view', compact('temp2', 'total_sum_of_taxes', 'invoice', 'customer', 'iteams', 'invoicePayment', 'customFields', 'company_payment_setting'));
                return view('invoice.view', compact('invoice', 'customer', 'iteams', 'company_payment_setting'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function sent($id)
    {
        if (\Auth::user()->can('send invoice')) {
            $invoice            = Invoice::where('id', $id)->first();
            $invoice->send_date = date('Y-m-d');
            $invoice->status    = 1;
            $invoice->save();

            $customer         = Customer::where('id', $invoice->customer_id)->first();
            $invoice->name    = !empty($customer) ? $customer->name : '';
            $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

            $invoiceId    = Crypt::encrypt($invoice->id);
            $invoice->url = route('invoice.pdf', $invoiceId);

            Utility::userBalance('customer', $customer->id, $invoice->getTotal(), 'credit');

            $uArr = [
                'invoice_name' => $invoice->name,
                'invoice_number' => $invoice->invoice,
                'invoice_url' => $invoice->url,
            ];
            try
            {
                $resp = Utility::sendEmailTemplate('customer_invoice_sent', [$customer->id => $customer->email], $uArr);
            }
             catch (\Exception $e) {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }

            return redirect()->back()->with('success', __('Invoice successfully sent.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function resent($id)
    {
        if (\Auth::user()->can('send invoice')) {
            $invoice = Invoice::where('id', $id)->first();

            $customer         = Customer::where('id', $invoice->customer_id)->first();
            $invoice->name    = !empty($customer) ? $customer->name : '';
            $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

            $invoiceId    = Crypt::encrypt($invoice->id);
            $invoice->url = route('invoice.pdf', $invoiceId);

            $uArr = [
                'invoice_name' => $invoice->name,
                'invoice_number' => $invoice->invoice,
                'invoice_url' => $invoice->url,
            ];
            try
            {
            // dd($customer);
                $resp = Utility::sendEmailTemplate('customer_invoice_sent', [$customer->id => $customer->email], $uArr);
            }
             catch (\Exception $e) {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }

            return redirect()->back()->with('success', __('Invoice successfully sent.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function payment($invoice_id)
    {
        if (\Auth::user()->can('create payment invoice')) {
            $invoice = Invoice::where('id', $invoice_id)->first();

            $customers  = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $accounts   = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('invoice.payment', compact('customers', 'categories', 'accounts', 'invoice'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function createPayment(Request $request, $invoice_id)
    {


        if (\Auth::user()->can('create payment invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'amount' => 'required',
                    'account_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoicePayment                 = new InvoicePayment();
            $invoicePayment->invoice_id     = $invoice_id;
            $invoicePayment->date           = $request->date;
            $invoicePayment->amount         = $request->amount;
            $invoicePayment->account_id     = $request->account_id;
            $invoicePayment->payment_method = 0;
            $invoicePayment->reference      = $request->reference;
            $invoicePayment->description    = $request->description;
            if(!empty($request->add_receipt))
            {
                $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                // $request->add_receipt->storeAs('uploads/payment', $fileName);
                $invoicePayment->add_receipt = $fileName;


                $dir        = 'uploads/payment';
                $path = Utility::upload_file($request,'add_receipt',$fileName,$dir,[]);
                // $request->add_receipt  = $path;
                if($path['flag'] == 1){
                    $url = $path['url'];
                }else{
                    return redirect()->back()->with('error', __($path['msg']));
                }
                $invoicePayment->save();
            }
            $invoicePayment->save();

            $invoice = Invoice::where('id', $invoice_id)->first();
            $due     = $invoice->getDue();
            $total   = $invoice->getTotal();
            if ($invoice->status == 0) {
                $invoice->send_date = date('Y-m-d');
                $invoice->save();
            }

            if ($due <= 0) {
                $invoice->status = 4;
                $invoice->save();
            } else {
                $invoice->status = 3;
                $invoice->save();
            }
            $invoicePayment->user_id    = $invoice->customer_id;
            $invoicePayment->user_type  = 'Customer';
            $invoicePayment->type       = 'Partial';
            $invoicePayment->created_by = \Auth::user()->id;
            $invoicePayment->payment_id = $invoicePayment->id;
            $invoicePayment->category   = 'Invoice';
            $invoicePayment->account    = $request->account_id;

            Transaction::addTransaction($invoicePayment);

            $customer = Customer::where('id', $invoice->customer_id)->first();

            $payment            = new InvoicePayment();
            $payment->name      = $customer['name'];
            $payment->date      = \Auth::user()->dateFormat($request->date);
            $payment->amount    = \Auth::user()->priceFormat($request->amount);
            $payment->invoice   = 'invoice ' . \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            $payment->dueAmount = \Auth::user()->priceFormat($invoice->getDue());

            Utility::userBalance('customer', $invoice->customer_id, $request->amount, 'debit');

            Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');

            $uArr = [
                'payment_name' => $payment->name,
                'payment_amount' => $payment->amount,
                'invoice_number' => $payment->invoice,
                'payment_date' => $payment->date,
                'payment_dueAmount' => $payment->dueAmount
            ];
            try
            {
                $resp = Utility::sendEmailTemplate('new_invoice_payment', [$customer->id => $customer->email], $uArr);
            }
             catch (\Exception $e) {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }

            return redirect()->back()->with('success', __('Payment successfully added.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        }
    }

    public function paymentDestroy(Request $request, $invoice_id, $payment_id)
    {

        if (\Auth::user()->can('delete payment invoice')) {
            $payment = InvoicePayment::find($payment_id);

            InvoicePayment::where('id', '=', $payment_id)->delete();

            $invoice = Invoice::where('id', $invoice_id)->first();
            $due     = $invoice->getDue();
            $total   = $invoice->getTotal();

            if ($due > 0 && $total != $due) {
                $invoice->status = 3;
            } else {
                $invoice->status = 2;
            }

            $invoice->save();
            $type = 'Partial';
            $user = 'Customer';
            Transaction::destroyTransaction($payment_id, $type, $user);

            Utility::userBalance('customer', $invoice->customer_id, $payment->amount, 'credit');

            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'debit');

            return redirect()->back()->with('success', __('Payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function paymentReminder($invoice_id)
    {
        $invoice            = Invoice::find($invoice_id);
        $customer           = Customer::where('id', $invoice->customer_id)->first();
        $invoice->dueAmount = \Auth::user()->priceFormat($invoice->getDue());
        $invoice->name      = $customer['name'];
        $invoice->date      = \Auth::user()->dateFormat($invoice->send_date);
        $invoice->invoice   = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

        $uArr = [
            'payment_name' => $invoice->name,
            'invoice_number' => $invoice->invoice,
            'payment_dueAmount'=> $invoice->dueAmount,
            'payment_date'=> $invoice->date,

        ];
        try
        {
            $resp = Utility::sendEmailTemplate('payment_reminder', [$customer->id => $customer->email], $uArr);
        }
         catch (\Exception $e) {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        }

        //Twilio Notification
        $setting  = Utility::settings(\Auth::user()->creatorId());
        $customer = Customer::find($invoice->customer_id);
        if(isset($setting['payment_notification']) && $setting['payment_notification'] ==1)
        {
            $msg = __("New Payment Reminder of ").' '. \Auth::user()->invoiceNumberFormat($invoice->invoice_id).' '. __("created by").' ' .\Auth::user()->name.'.';

            Utility::send_twilio_msg($customer->contact,$msg);
        }

        return redirect()->back()->with('success', __('Payment reminder successfully send.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    public function customerInvoiceSend($invoice_id)
    {
        return view('customer.invoice_send', compact('invoice_id'));
    }

    public function customerInvoiceSendMail(Request $request, $invoice_id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $email   = $request->email;
        $invoice = Invoice::where('id', $invoice_id)->first();

        $customer         = Customer::where('id', $invoice->customer_id)->first();
        $invoice->name    = !empty($customer) ? $customer->name : '';
        $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

        $invoiceId    = Crypt::encrypt($invoice->id);
        $invoice->url = route('invoice.pdf', $invoiceId);

        $uArr = [
            'invoice_name' => $invoice->name,
            'invoice_number' =>$invoice->invoice,
            'invoice_url' => $invoice->url,
        ];

        try
        {
            $resp = Utility::sendEmailTemplate('customer_invoice_sent', [$customer->id => $customer->email], $uArr);
        }
         catch (\Exception $e) {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        }

        return redirect()->back()->with('success', __('Invoice successfully sent.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    public function shippingDisplay(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if ($request->is_display == 'true') {
            $invoice->shipping_display = 1;
        } else {
            $invoice->shipping_display = 0;
        }
        $invoice->save();

        return redirect()->back()->with('success', __('Shipping address status successfully changed.'));
    }

    public function duplicate($invoice_id)
    {
        if (\Auth::user()->can('duplicate invoice')) {
            $invoice                            = Invoice::where('id', $invoice_id)->first();
            $duplicateInvoice                   = new Invoice();
            $duplicateInvoice->invoice_id       = $this->invoiceNumber();
            $duplicateInvoice->customer_id      = $invoice['customer_id'];
            $duplicateInvoice->issue_date       = date('Y-m-d');
            $duplicateInvoice->due_date         = $invoice['due_date'];
            $duplicateInvoice->send_date        = null;
            $duplicateInvoice->category_id      = $invoice['category_id'];
            $duplicateInvoice->ref_number       = $invoice['ref_number'];
            $duplicateInvoice->status           = 0;
            $duplicateInvoice->shipping_display = $invoice['shipping_display'];
            $duplicateInvoice->created_by       = $invoice['created_by'];
            $duplicateInvoice->save();

            if ($duplicateInvoice) {
                $invoiceProduct = InvoiceProduct::where('invoice_id', $invoice_id)->get();
                foreach ($invoiceProduct as $product) {
                    $duplicateProduct             = new InvoiceProduct();
                    $duplicateProduct->invoice_id = $duplicateInvoice->id;
                    $duplicateProduct->product_id = $product->product_id;
                    $duplicateProduct->quantity   = $product->quantity;
                    $duplicateProduct->tax        = $product->tax;
                    $duplicateProduct->discount   = $product->discount;
                    $duplicateProduct->price      = $product->price;
                    $duplicateProduct->save();
                }
            }

            return redirect()->back()->with('success', __('Invoice duplicate successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function previewInvoice($template, $color)
    {
        $objUser  = \Auth::user();
        $settings = Utility::settings();
        $invoice  = new Invoice();

        $customer                   = new \stdClass();
        $customer->email            = '<Email>';
        $customer->shipping_name    = '<Customer Name>';
        $customer->shipping_country = '<Country>';
        $customer->shipping_state   = '<State>';
        $customer->shipping_city    = '<City>';
        $customer->shipping_phone   = '<Customer Phone Number>';
        $customer->shipping_zip     = '<Zip>';
        $customer->shipping_address = '<Address>';
        $customer->billing_name     = '<Customer Name>';
        $customer->billing_country  = '<Country>';
        $customer->billing_state    = '<State>';
        $customer->billing_city     = '<City>';
        $customer->billing_phone    = '<Customer Phone Number>';
        $customer->billing_zip      = '<Zip>';
        $customer->billing_address  = '<Address>';
        $invoice->sku         = 'Test123';

        $totalTaxPrice = 0;
        $taxesData     = [];

        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $item           = new \stdClass();
            $item->name     = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax      = 5;
            $item->discount = 50;
            $item->price    = 100;

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            foreach ($taxes as $k => $tax) {
                $taxPrice         = 10;
                $totalTaxPrice    += $taxPrice;
                $itemTax['name']  = 'Tax ' . $k;
                $itemTax['rate']  = '10 %';
                $itemTax['price'] = '$10';
                $itemTax['tax_price'] = 10;
                $itemTaxes[]      = $itemTax ;

                // $taxPrice         = 10;
                // $totalTaxPrice    += $taxPrice;
                // $itemTax['name']  = 'Tax ' . $k;
                // $itemTax['rate']  = '10 %';
                // $itemTax['price'] = '$10';
                // $itemTaxes[]      = $itemTax;
                if (array_key_exists('Tax ' . $k, $taxesData)) {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                } else {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $invoice->invoice_id = 1;
        $invoice->issue_date = date('Y-m-d H:i:s');
        $invoice->due_date   = date('Y-m-d H:i:s');
        $invoice->itemData   = $items;

        $invoice->totalTaxPrice = 60;
        $invoice->totalQuantity = 3;
        $invoice->totalRate     = 300;
        $invoice->totalDiscount = 10;
        $invoice->taxesData     = $taxesData;
        $invoice->customField   = [];
        $customFields           = [];

        $preview    = 1;
        $color      = '#' . $color;
        $font_color = Utility::getFontColor($color);

        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $invoice_logo = Utility::getValByName('invoice_logo');
        if(isset($invoice_logo) && !empty($invoice_logo))
        {
            // $img = asset(\Storage::url('invoice_logo/') . $invoice_logo);
            $img = Utility::get_file('invoice_logo/') . $invoice_logo;
        }
        else{
        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }
        return view('invoice.templates.' . $template, compact('invoice', 'preview', 'color', 'img', 'settings', 'customer', 'font_color', 'customFields'));
    }

    public function invoice($invoice_id)
    {
        $settings = Utility::settings();

        $invoiceId = Crypt::decrypt($invoice_id);
        $invoice   = Invoice::where('id', $invoiceId)->first();

        $data  = DB::table('settings');
        $data  = $data->where('created_by', '=', $invoice->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $customer      = $invoice->customer;
        $items         = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate     = 0;
        $totalDiscount = 0;
        $taxesData     = [];
        foreach ($invoice->items as $product) {
            $item              = new \stdClass();
            $item->name        = !empty($product->product()) ? $product->product()->name : '';
            $item->quantity    = $product->quantity;
            $item->tax         = $product->tax;
            $item->discount    = $product->discount;
            $item->price       = $product->price;
            $item->description = $product->description;
            $totalQuantity += $item->quantity;
            $totalRate     += $item->price;
            $totalDiscount += $item->discount;
            $taxes = Utility::tax($product->sum('tax'));
            $itemTaxes = [];
            // if (!empty($item->tax)) {
            //     foreach ($taxes as $tax) {
            //         $taxPrice=Utility::taxRate($tax->rate, $item->price, $item->quantity,$item->discount);
            //         $totalTaxPrice += $taxPrice;
            //         $itemTax['name']  = $tax->name;
            //         $itemTax['rate']  = $tax->rate . '%';
            //         $itemTax['price'] = Utility::priceFormat($settings, $taxPrice);
            //         $itemTax['tax_price'] =$taxPrice;
            //         $itemTaxes[]      = $itemTax;
            //         if (array_key_exists($tax->name, $taxesData)) {
            //             $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
            //         } else {
            //             $taxesData[$tax->name] = $taxPrice + $taxes;
            //         }
            //     }
            //     $item->itemTax = $itemTaxes;
            // } else {
            //     $item->itemTax = [];
            // }
            // $items[] = $item;
        }
        $invoice->itemData      = $items;
        $invoice->totalTaxPrice = $totalTaxPrice;
        $invoice->totalQuantity = $totalQuantity;
        $invoice->totalRate     = $totalRate;
        $invoice->totalDiscount = $totalDiscount;
        $invoice->taxesData     = $taxesData;
        $invoice->customField   = CustomField::getData($invoice, 'invoice');
        $customFields           = [];
        if (!empty(\Auth::user())) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
        }
        //Set your logo
        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($invoice->created_by);
        $invoice_logo = $settings_data['invoice_logo'];
         if(isset($invoice_logo) && !empty($invoice_logo))
        {
            // $img = asset(\Storage::url('invoice_logo/') . $invoice_logo);
            $img = Utility::get_file('invoice_logo/') . $invoice_logo;
        }
        else{
         $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }
        if ($invoice) {
            $color      = '#' . $settings['invoice_color'];
            $font_color = Utility::getFontColor($color);

            return view('invoice.templates.' . $settings['invoice_template'], compact('invoice', 'color', 'settings', 'customer', 'img', 'font_color', 'customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function saveTemplateSettings(Request $request)
    {
        $user = \Auth::user();
        $post = $request->all();
        unset($post['_token']);

        if($request->invoice_logo)
        {
            $request->validate(
                [
                    'invoice_logo' => 'image',
                ]
            );
            $dir = 'invoice_logo/';
            $invoice_logo         = $user->id.'_invoice_logo.png';
            $validation =[
                'mimes:'.'png',
                'max:'.'20480',
            ];

            $path = Utility::upload_file($request,'invoice_logo',$invoice_logo,$dir,$validation);
            if($path['flag'] == 1){
                $proposal_logo = $path['url'];
            }else{
                return redirect()->back()->with('error', __($path['msg']));
            }
            // $path                 = $request->file('invoice_logo')->storeAs('/invoice_logo', $invoice_logo);
            $post['invoice_logo'] = $invoice_logo;
        }

        if (isset($post['invoice_template']) && (!isset($post['invoice_color']) || empty($post['invoice_color']))) {
            $post['invoice_color'] = "ffffff";
        }
        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->creatorId(),
                ]
            );
        }
        return redirect()->back()->with('success', __('Invoice Setting updated successfully'));
    }
    public function items(Request $request)
    {
        $items = InvoiceProduct::where('invoice_id', $request->invoice_id)->where('product_id', $request->product_id)->first();

        return json_encode($items);
    }
    public function payinvoice($invoice_id)
    {
        if (!empty($invoice_id)) {
            $id = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);

            $invoice = Invoice::where('id', $id)->first();

            if (!is_null($invoice)) {

                $settings = Utility::settings();

                $items         = [];
                $totalTaxPrice = 0;
                $totalQuantity = 0;
                $totalRate     = 0;
                $totalDiscount = 0;
                $taxesData     = [];

                foreach ($invoice->items as $item) {
                    $totalQuantity += $item->quantity;
                    $totalRate     += $item->price;
                    $totalDiscount += $item->discount;
                    $taxes         = Utility::tax($item->sum('tax'));

                    $itemTaxes = [];
                    foreach ($taxes as $tax) {
                        if (!empty($tax)) {
                            $taxPrice            = Utility::taxRate($tax->rate, $item->price, $item->quantity);
                            $totalTaxPrice       += $taxPrice;
                            $itemTax['tax_name'] = $tax->tax_name;
                            $itemTax['tax']      = $tax->tax . '%';
                            $itemTax['price']    = Utility::priceFormat($settings, $taxPrice);
                            $itemTaxes[]         = $itemTax;

                            if (array_key_exists($tax->name, $taxesData)) {
                                $taxesData[$itemTax['tax_name']] = $taxesData[$tax->tax_name] + $taxPrice;
                            } else {
                                $taxesData[$tax->tax_name] = $taxPrice;
                            }
                        } else {
                            $taxPrice            = Utility::taxRate(0, $item->price, $item->quantity);
                            $totalTaxPrice       += $taxPrice;
                            $itemTax['tax_name'] = 'No Tax';
                            $itemTax['tax']      = '';
                            $itemTax['price']    = Utility::priceFormat($settings, $taxPrice);
                            $itemTaxes[]         = $itemTax;

                            if (array_key_exists('No Tax', $taxesData)) {
                                $taxesData[$tax->tax_name] = $taxesData['No Tax'] + $taxPrice;
                            } else {
                                $taxesData['No Tax'] = $taxPrice;
                            }
                        }
                    }
                    $item->itemTax = $itemTaxes;
                    $items[]       = $item;
                }
                $invoice->items         = $items;
                $invoice->totalTaxPrice = $totalTaxPrice;
                $invoice->totalQuantity = $totalQuantity;
                $invoice->totalRate     = $totalRate;
                $invoice->totalDiscount = $totalDiscount;
                $invoice->taxesData     = $taxesData;

                $ownerId = $invoice->created_by;


                $company_setting = Utility::settingById($ownerId);

                $payment_setting = Utility::invoice_payment_settings($ownerId);

                $users = User::where('id', $invoice->created_by)->first();

                if (!is_null($users)) {
                    \App::setLocale($users->lang);
                } else {
                    $users = User::where('type', 'owner')->first();
                    \App::setLocale($users->lang);
                }

                $invoice    = Invoice::where('id', $id)->first();
                $customer = $invoice->customer;
                $iteams   = $invoice->items;
                $company_payment_setting = Utility::getCompanyPaymentSetting($invoice->created_by);
                // dd($company_payment_setting);
                // dd($iteams);
                return view('invoice.invoicepay', compact('invoice', 'iteams', 'company_setting', 'users', 'company_payment_setting'));
            } else {
                return abort('404', 'The Link You Followed Has Expired');
            }
        } else {
            return abort('404', 'The Link You Followed Has Expired');
        }
    }
    public function export()
    {
        $name = 'customer_' . date('Y-m-d i:h:s');
        $data = Excel::download(new InvoiceExport(), $name . '.xlsx');

        return $data;
    }
}