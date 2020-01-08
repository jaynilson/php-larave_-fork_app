{!! view_render_event('bagisto.shop.products.add_to_cart.before', ['product' => $product]) !!}

<div class="col-4 add-to-cart-btn">
    <form
        method="POST"
        action="{{ route('cart.add', $product->product_id) }}">

        @csrf

        <input type="hidden" name="product_id" value="{{ $product->product_id }}">
        <input type="hidden" name="quantity" value="1">
        <button
            type="submit"
            class="btn btn-add-to-cart"
            {{ ! $product->isSaleable() ? 'disabled' : '' }}>

            @if (! (isset($showCartIcon) && !$showCartIcon))
                <span class="rango-cart-1 fs20"></span>
            @endif

            <span class="fs14 align-vertical-top fw6">
                {{ __('shop::app.products.add-to-cart') }}
            </span>
        </button>
    </form>
</div>

@if (! (isset($showWishlist) && !$showWishlist))
    @include('shop::products.wishlist')
@endif

{!! view_render_event('bagisto.shop.products.add_to_cart.after', ['product' => $product]) !!}