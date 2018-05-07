@extends('layouts.app')

@section('content')
    <main style="width: 100%">
        <div class="main-back" style="position: absolute;"></div>
        @php
            if(isset($_SESSION['client'])){
                $client_email = $_SESSION['client'];
                $user = App\Model\Base\User::where('email', $client_email)->with('attributes', 'orders')->first();
            } else{
                //$client_email = '';
                $user = NULL;
            }
//var_dump($client_email); die;

          //  var_dump($user); die;
            if($user == NULL){
            //var_dump($user); die;
                redirect('sso-login');
            }
            //var_dump($user); die;
            $orders = App\Model\Shop\Order::where('user_id', $user->id)->with('products')->get();

        @endphp
        <section class="content profile">
            <div class="container">
                <div class="article-row row">
                    <div class="col-sm-4 profile-links">
                        <div>
                            <a href="" class="personal active" data-target="#personalF" data-wpel-link="internal">{{ __('default.personal_info') }}</a>
                        </div>
                        <div>
                            <a href="" class="history" data-target="#historyField" data-wpel-link="internal"> {{ __('default.orders_history') }} </a>
                        </div>
                        <div>
                            <a href="{{ env(strtoupper($locale) . '_DOMAIN') . '/sso-login?action=logout'}}" class="exit" data-wpel-link="internal">{{ __('default.logout') }}</a>
                        </div>
                    </div>
                    <div class="article-content col-sm-8">
                        <div class="article-text">
                            <div id="personalF">
                                <form id="personalForm">
                                    {{csrf_field()}}
                                    <div class="form-group">
                                        <input type="text" value="{{$user->attributes->fname}}" name="first_name" placeholder="First Name" class="form-control full-width" required="required" data-bv-message=" " style="padding-left: 14px"/>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" value="{{$user->attributes->lname}}" name="last_name" placeholder="Last Name" class="form-control full-width" required="required" data-bv-message=" " style="padding-left: 14px"/>
                                    </div>
                                    <!--<div class="form-group">
                                        <input type="password" name="password" class="form-control" placeholder="Passsword" data-bv-identical-field="password_confirm" data-bv-message="Пароли не совпадают"/>
                                        <div class="checkbox-wrapper">
                                            <input type="checkbox" name="tfa" class="form-control tfa-check" />
                                            <label class="form-label">двуфакторная авторизация</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="re-password" class="form-control" placeholder="Repeat password" data-bv-identical-field="password_confirm" data-bv-message="Пароли не совпадают"/>
                                    </div>-->
                                    <!--<div class="form-group tfa hidden">
                                        [twofactor_user_settings]
                                    </div>-->
                                    <div class="form-group">
                                        <input type="email" name="email"  value="{{$user->email}}" disabled class="form-control" placeholder="E-mail" style="padding-left: 14px"/>
                                    </div>
                                    <div class="form-group">
                                        <input type="tel" name="phone" value="{{$user->attributes->phone}}" class="form-control" placeholder="Phone" required="required" data-bv-message="" style="padding-left: 14px"/>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="address" value="{{$user->attributes->address}}" class="form-control full-width" placeholder="Delivery address" style="padding-left: 14px"/>
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" name="submit" class="btn-default" value="Save"/>
                                    </div>
                                    <input type="hidden" name="action" value="bitmain_account_register">
                                    <input type="hidden" name="user" value="{{$user->id}}">
                                </form>
                                <p class="result" data-text="Profile updated!"></p>

                            </div>
                            <div id="historyField">
                                <div class="table-like">
                                    <div class="table-row table-header">
                                        <div class="table-cell  ">{{ __('default.number_and_date') }}</div>
                                        <div class="table-cell ">{{ __('default.product_and_price') }}</div>
                                        <div class="table-cell table-cell-title">{{ __('default.count') }}</div>
                                        <div class="table-cell">{{ __('default.cost') }}</div>
                                        <div class="table-cell table-cell-status">{{ __('default.status') }}</div>
                                        <div class="table-cell"></div>
                                    </div>
                                    @foreach($orders as $order)
                                        @php
                                            $btcCost = $order->countBtcCost();
                                            //var_dump($btcCost); die;
                                            $status_logs = $order->logs->sortBy('created_at');
                                            //var_dump($status_logs); die;
                                        @endphp
                                        <div class="table-row  table-row-border table-row-top-several">
                                            <div class="table-cell table-cell-border">
                                                <div class="order-number-wrap">
                                                    <span class="order-number">#{{$order->number}}</span>
                                                    <span class="order-data">@php echo date('d-m-Y', strtotime($order->created_at)) @endphp</span>
                                                </div>
                                            </div>
                                            @if(count($order->products) > 1)
                                                <div class="table-cell table-product-cell">
                                                    <div class="order_thumbs order_thumbs_several">
                                                        <span class="several_products">@php echo count($order->products) @endphp {{ __('default.items_profile') }}</span>
                                                        <a href=".order-{{$order->number}}" data-wpel-link="internal" class="">
                                                            <span class="show_products"><i class="fa fa-chevron-down" aria-hidden="true"></i>{{ __('default.show') }}</span>
                                                            <span class="hide_products"><i class="fa fa-chevron-up" aria-hidden="true"></i>{{ __('default.hide') }}</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            @else
                                                @foreach($order->products as $product)
                                                @php
                                                   //  var_dump($product->pivot); die;
                                                    $price = number_format($product->pivot->cost, 2, '.', '');
                                                    $btcPrice = number_format($product->pivot->btcCost, 4, '.', '');

/*                                                    if($product->auto_price){
                                                        $price = number_format($product->calcAutoPrice(), 2, '.', '');
                                                    } else{
                                                        $price = number_format($product->price, 2, '.', '');
                                                    }
                                                    $btcPrice = number_format($product->calcBtcPrice(), 4, '.', '');*/
                                                @endphp
                                                <div class="table-cell table-product-cell">
                                                    <div class="order_thumbs">
                                                        <img src="@if(count($product->images)){{asset('uploads/' . App\Model\Shop\ProductImage::where('product_id', $product->id)->first()->name)}}@endif" title="{{$product->title}}">
                                                        <div class="cost">
                                                            <a href="{{$product->page->link}}" data-wpel-link="internal">{{$product->title}}</a>
                                                            <span class="hidden-md">{{ __('default.item_cost') }}</span>
                                                            <span class="table-price">${{ $price }}</span>
                                                            @if($btcPrice > 0)<span class="table-bitcoin">{{ $btcPrice }}<i class="fa fa-bitcoin"></i></span>@endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach

                                            @endif
                                            <div class="table-cell number" style="padding-top: 0px !important; vertical-align: middle !important;">
                                                <span class="hidden-md">{{ __('default.count') }}</span>
                                                <span> @php $count = 0; foreach($order->carts as $cart){ $count += $cart->count; } echo $count @endphp</span>
                                            </div>
                                            <div class="table-cell number number-price">
                                                <span class="hidden-md">{{ __('default.total') }}</span>
                                                <span class="table-price"><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">$</span>{{ number_format($order->cost, 2, '.', '') }}</span></span>
                                                @if($btcCost > 0)<span class="table-bitcoin">{{ $btcCost }}<i class="fa fa-bitcoin"></i></span>@endif
                                            </div>
                                            <div class="table-cell status">
                                                <span class="">
                                                    <!--<p class="hidden-md">{{ __('default.status') }}</p>-->
                                                    <span class="mark cancelled" style="color: {{$order->status->color}}">{{ __('common.status_' . str_replace(' ', '_', mb_strtolower($order->status->title))) }}</span><br>
                                                    @if(isset($status_logs) && count($status_logs) > 0)
                                                        <a class="order-history" data-wpel-link="internal">{{ __('default.history') }}
                                                        <div class="history-dd" style="height: auto !important">
                                                            <div class="modal-body">
                                                                @foreach($status_logs as $log)
                                                                    @php
                                                                        $convert_log = str_replace(' ', '_', mb_strtolower($log->value));
                                                                        //var_dump($convert_log); die;
                                                                    @endphp
                                                                    <h3>@php echo date('d F Y ', strtotime($log->created_at)) . ' at ' . date('H:i', strtotime($log->created_at)) @endphp</h3>
                                                                    <div class="comment-order">
																        {{ __('default.order_status_changed_from') }} @if(isset($prev)) {{ __('common.status_' . $prev) }} @else {{ __('default.new_order') }} @endif {{ __('default.to') }} {{ __('common.status_' . $convert_log) }}.
                                                                    </div>
                                                                    @php
                                                                        $prev = $convert_log;
                                                                    @endphp
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </a>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="table-cell" style="width: 10px"></div>
                                        </div>
                                        @if(count($order->products) > 1)
                                            @foreach($order->products as $product)
                                                @php
                                                    $price = number_format($product->pivot->cost, 2, '.', '');
                                                    $btcPrice = number_format($product->pivot->btcCost, 4, '.', '');
                                                    //var_dump($btcPrice); die;
/*                                                    if($product->auto_price){
                                                        $price = number_format($product->calcAutoPrice(), 2, '.', '');
                                                    } else{
                                                        $price = number_format($product->price, 2, '.', '');
                                                    }
                                                    $btcPrice = number_format($product->calcBtcPrice(), 4, '.', '');*/
                                                @endphp
                                                <div class="table-row hidden-block table-row-several order-{{$order->number}}">
                                                    <div class="table-cell table-cell-border table-cell-border-none">
                                                    </div>
                                                    <div class="table-cell table-product-cell">
                                                        <div class="order_thumbs">
                                                            <img src="@if(count($product->images)){{asset('uploads/' . $product->images[0]->name)}}@endif" title="{{$product->title}}">
                                                            <div class="cost">
                                                                <a href="{{$product->page->link}}" data-wpel-link="internal">{{$product->title}}</a>
                                                                <span class="hidden-md">{{ __('default.cost') }}</span>
                                                                <span class="table-price">${{ $price }}</span>
                                                                @if($btcPrice != 0)<span class="table-bitcoin">{{ $btcPrice }}<i class="fa fa-bitcoin"></i></span>@endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="table-cell number" style="padding-top: 0px !important; vertical-align: middle !important;">
                                                        <span class="hidden-md">{{ __('default.count') }}</span>
                                                        <span> {{$product->pivot->count}} </span>
                                                    </div>
                                                    <div class="table-cell number number-price">
                                                        <span class="hidden-md">{{ __('default.item_cost') }}</span>
                                                        <span class="table-price">$@php echo $price * $product->pivot->count; @endphp</span>
                                                        @if($btcPrice > 0)<span class="table-bitcoin">{{ $btcPrice * $product->pivot->count }}<i class="fa fa-bitcoin"></i></span>@endif
                                                    </div>
                                                    <div class="table-cell status">
                                                    </div>
                                                    <div class="table-cell "></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection