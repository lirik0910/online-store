@extends('layouts.app')

@section('content')
<main>

<div class="main-back"></div>
@php
$product = App\Model\Shop\Product::where('page_id', $it->id)->with('images', 'options')->first();
@endphp
<section class="content item">
<div class="container">

@isset($product)
<div class="article-row row">
	<div class="col-sm-4">
		<div id="isync1" class="  owl-carousel owl-theme">
			@foreach ($product->images as $item)
			<div class="product-item">
				<img width="300" height="300" src="{{ asset('uploads/' . $item->name) }}" class="attachment-medium size-medium" alt="img" title="{{ htmlentities($it->title) }}" />
			</div>
			@endforeach
		</div>

		<div id="isync2" class="visible-md owl-carousel owl-theme">
			@foreach ($product->images as $item)
			<div class="product-item">
				<img width="47" height="47" src="{{ asset('uploads/' . $item->name) }}" class="attachment-i47 size-i47" alt="img" title="{{ htmlentities($it->title) }}" />
			</div>
			@endforeach
		</div>
	</div>

	<div class="col-sm-8">
		<div class="article-text">
			<h1>{{$product->title}}</h1>

			<div class="tag tag-order">Pre-order</div>
			<div class="tag tag-waranty">Waranty 180</div>
	
			<div class="single-product-price">
				<span class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol">&#36;</span>
					{{ number_format($product->price, 2, '.', '') }}
				</span>
			</div>
							
			<form class="related-form item-count__container">
				<span class="input-number ">
					<input id="{{ 'count-products-' . $product->id }}" type="text" name="count" class="form-control form-number count add-to-cart-count" value="{{ isset($inCart[$product->id]) ? $inCart[$product->id] : 1 }}" data-id="{{ $product->id }}" />

					<div class="btn-count btn-count-plus" data-id="{{ $product->id }}">
						<i class="fa fa-plus"></i>
					</div>

					<div class="btn-count btn-count-minus" data-id="{{ $product->id }}">
						<i class="fa fa-minus"></i>
					</div>
				</span>

				@if (isset($inCart[$product->id]))
					<a data-success="{{ __('shop.added') }}" data-add="{{ __('shop.to_cart') }}" rel="nofollow" href="#" data-id="{{ $product->id }}" class="btn-default intocarts">
						<span>{{ __('shop.added') }}</span>
						<i class="fa fa-spin fa-refresh" style="display: none"></i>
					</a>
				@else
					<a data-success="{{ __('shop.added') }}" data-add="{{ __('shop.to_cart') }}" rel="nofollow" href="#" data-id="{{ $product->id }}" class="btn-default addtocarts">
						<span>{{ __('shop.to_cart') }}</span>
							<i class="fa fa-spin fa-refresh" style="display: none"></i>
					</a>
				@endif
			</form>
			<div class="tag tag-payback">{{ __('shop.payback') }}</div>
			
			<div class="single-product-tabs">
				<div class="product-tab-links">
					<a href="" class="active" data-target="#description" data-wpel-link="internal">{{ __('shop.description') }}</a>

					<a href="" class="" data-target="#details">
						<span class="hidden-xxs">{{ __('shop.characteristics') }}</span>
						<span class="visible-xxs">{{ __('shop.characteristics') }}</span>
					</a>
				</div>
				
				<div class="tabs-field">
					<div id="description">
						{!! $product->description !!}
					</div>
				
					<div id="details" style="display: block;">
						<table class="shop_attributes">
							<tbody>
							@foreach ($product->options as $item)
								@if ($item->type->title === 'characteristic')
								<tr>
									<th>{{ $item->name }}</th>
									<td>
										<p>{{ $item->value }}</p>
									</td>
									</tr>
								@endif
							@endforeach
							</tbody>
						</table>
					</div>
					
					<div id="video"></div>
				</div>
			</div>
		</div>
	</div>
</div>
@endisset

</div>
</section>


</main>
@endsection