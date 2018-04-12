@extends('layouts.app')

@php 
$mainProducts = $select('\App\Model\Shop\Product')
	->whereHas('categories', function ($q) {
		$q->where('title', 'Base');
		$q->where('delete', 0);
		$q->where('context_id', $it->context_id);
	})
	->with('page', 'options', 'images')
	->get();

$secondaryProducts = $select('\App\Model\Shop\Product')
	->whereHas('categories', function ($q) {
		$q->where('title', 'Secondary');
		$q->where('delete', 0);
		$q->where('context_id', $it->context_id);
	})
	->with('page', 'options', 'images')
	->get();
//var_dump($secondaryProducts); die;
@endphp

@section('content')
<main>
<div class="main-back"></div>
	
<section class="content item items">
	<div class="container">
		<div class="clearfix" style="clear: both"></div>
		@foreach($mainProducts as $item)
			@if (isset($item->page))
				@include('parts.shop.item', $item)
			@endif
		@endforeach
	</div>
</section>

<section class="related">
	<div class="container">
		<div class="row">
			<header>{{ __('default.optional_equipment') }}</header>
			<div id="relatedSlider" class="related-slider owl-carousel owl-theme">
				@foreach ($secondaryProducts as $item)
					@if (isset($item->page))
						@include('parts.shop.slideItem', $item)
					@endif
				@endforeach
			</div>
		</div>
	</div>
</section>
</main>
@include('parts.shop.report-availability_form')
@endsection
