@extends('layouts.admin')
@section('page-title')
{{__('Manage Recurring Payments')}}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Recurring Payment')}}</li>
@endsection

@section('action-btn')
<div class="float-end">

    <!-- <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">
            <i class="ti ti-filter"></i>
        </a> -->
    @can('create recurringpayment')
    <a href="#" data-url="{{ route('recurringpayment.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" data-size="lg" data-title="{{__('Create New Recurring Payment')}}" title="{{__('Create')}}" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class=" multi-collapse mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(array('route' => array('recurringpayment.index'),'method' => 'GET','id'=>'recurringpayment_form')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'),['class'=>'text-type']) }}
                                        {{ Form::text('date', isset($_GET['date'])?$_GET['date']:null, array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1')) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('account', __('Account'),['class'=>'text-type']) }}
                                        {{ Form::select('account',$account,isset($_GET['account'])?$_GET['account']:'', array('class' => 'form-control select' ,'id'=>'choices-multiple')) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('vender', __('Vendor'),['class'=>'text-type']) }}
                                        {{ Form::select('vender',$vender,isset($_GET['vender'])?$_GET['vender']:'', array('class' => 'form-control select','id'=>'choices-multiple1')) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('category', __('Category'),['class'=>'text-type']) }}
                                        {{ Form::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select','id'=>'choices-multiple2')) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('recurringpayment_form').submit(); return false;" data-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('recurringpayment.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>{{__('Date')}}</th>
                                <!-- <th>{{__('End Date')}}</th>
                                <th>{{__('Period')}}</th> -->
                                <th>{{__('Vendor')}}</th>
                                <th>{{__('Policy Number')}}</th>
                                <th>{{__('Tax')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Account')}}</th>
                                <th>{{__('Category')}}</th>
                                <th>{{__('Reference')}}</th>
                                <th>{{__('Description')}}</th>
                                <th>{{__('Payment Receipt')}}</th>
                                @if(Gate::check('edit payment') || Gate::check('delete payment'))
                                <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recurringpayments as $recurringpayment)
                            @php
                            $recurringpaymentpath=\App\Models\Utility::get_file('uploads/payment');
                            @endphp
                            <tr class="font-style">
                                <td>{{ Auth::user()->dateFormat($recurringpayment->recurring_date)}}</td>
                                <!-- <td>{{ Auth::user()->dateFormat($recurringpayment->end_date)}}</td>
                                <td>{{ Auth::user()->dateFormat($recurringpayment->period)}}</td> -->
                                <td>{{ !empty($recurringpayment->vender)?$recurringpayment->vender->name:'-'}}</td>
                                <td>{{ Auth::user()->priceFormat($recurringpayment->policy_number)}}</td>
                                <td>{{ !empty($recurringpayment->taxes)?$recurringpayment->taxes->name:'-'}}</td>
                                <td>{{ Auth::user()->priceFormat($recurringpayment->amount)}}</td>
                                <td>{{ !empty($recurringpayment->bankAccount)?$recurringpayment->bankAccount->bank_name.' '.$recurringpayment->bankAccount->holder_name:''}}</td>
                                <td>{{ !empty($recurringpayment->category)?$recurringpayment->category->name:'-'}}</td>
                                <td>{{ !empty($recurringpayment->reference)?$recurringpayment->reference:'-'}}</td>
                                <td>{{ !empty($recurringpayment->description)?$recurringpayment->description:'-'}}</td>
                                <td>
                                    @if(!empty($recurringpayment->add_receipt))
                                    <a class="action-btn bg-primary ms-2 btn btn-sm align-items-center" href="{{ $recurringpaymentpath . '/' . $recurringpayment->add_receipt }}" download="">
                                        <i class="ti ti-download text-white"></i>
                                    </a>
                                    {{-- <a href="{{asset(Storage::url('uploads/payment')).'/'.$recurringpayment->add_receipt}}" download="" class="btn btn-sm btn-primary btn-icon rounded-pill" target="_blank"><span class="btn-inner--icon"><i class="ti ti-download"></i></span></a> --}}
                                    <a href="{{ $recurringpaymentpath . '/' . $recurringpayment->add_receipt }}" class="btn btn-sm btn-secondary btn-icon rounded-pill" target="_blank"><span class="btn-inner--icon"><i class="ti ti-crosshair"></i></span></a>
                                    @else
                                    -
                                    @endif
                                </td>
                                @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
                                <td class="action text-end">
                                    @can('edit payment')
                                    <div class="action-btn bg-info ms-2">
                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('recurringpayment.edit',$recurringpayment->id) }}" data-ajax-popup="true" data-title="{{__('Edit Recurring Payment')}}" data-size="lg" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                            <i class="ti ti-edit text-white"></i>
                                        </a>
                                    </div>
                                    @endcan
                                    @can('delete payment')
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['recurringpayment.destroy', $recurringpayment->id],'id'=>'delete-form-'.$recurringpayment->id]) !!}
                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$recurringpayment->id}}').submit();">
                                            <i class="ti ti-trash text-white"></i>
                                        </a>
                                        {!! Form::close() !!}
                                    </div>
                                    @endcan
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection