@extends('layouts.app')
@section('content')

<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="shop-checkout container">
        <h2 class="page-title">Shipping and Checkout</h2>
        <div class="checkout-steps">
            <a href="{{route('cart.index')}}" class="checkout-steps__item active">
                <span class="checkout-steps__item-number">01</span>
                <span class="checkout-steps__item-title">
                    <span>Shopping Bag</span>
                    <em>Manage Your Items List</em>
                </span>
            </a>
            <a href="javascript:void(0)" class="checkout-steps__item active">
                <span class="checkout-steps__item-number">02</span>
                <span class="checkout-steps__item-title">
                    <span>Shipping and Checkout</span>
                    <em>Checkout Your Items List</em>
                </span>
            </a>
            <a href="{{route('cart.order.confirmation')}}" class="checkout-steps__item">
                <span class="checkout-steps__item-number">03</span>
                <span class="checkout-steps__item-title">
                    <span>Confirmation</span>
                    <em>Review And Submit Your Order</em>
                </span>
            </a>
        </div>
        <form name="checkout-form" action="{{route('cart.place.order')}}" method="post">
            @csrf
            <div class="checkout-form">
                <div class="billing-info__wrapper">
                    <div class="row">
                        <div class="col-6">
                            <h4>SHIPPING DETAILS</h4>
                        </div>
                        <div class="col-6">
                        </div>
                    </div>
                    @if($address)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="my-account__address-list">
                                <div class="my-account__address-list-item">
                                    <div class="my-account__address-list-item__detail">
                                        <p>{{$address->name}}</p>
                                        <p>{{$address->address}}</p>
                                        <p>{{$address->landmark}}</p>
                                        <p>{{$address->city}}, {{$address->state}}, {{$address->country}}</p>
                                        <p>{{$address->zip}}</p>
                                        <br />
                                        <p>{{$address->phone}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    @else
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <div class=" my-3">
                                <label for="name">Full Name *</label>
                                <input type="text" class="form-control" name="name" value="{{old('name')}}" required="">
                                @error('name') <span class="text-danger">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" my-3">
                                <label for="phone">Phone Number *</label>
                                <input type="text" class="form-control" name="phone" value="{{old('phone')}}" required="">
                                @error('phone') <span class="text-danger">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class=" my-3">
                                <label for="zip">Pincode *</label>
                                <input type="text" class="form-control" name="zip" value="{{old('zip')}}" required="">
                                @error('zip') <span class="text-danger">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class=" mt-3 mb-3">
                                <label for="state">State *</label>
                                <input type="text" class="form-control" name="state" value="{{old('state')}}" required="">
                                @error('state') <span class="text-danger">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class=" my-3">
                                <label for="city">Town / City *</label>
                                <input type="text" class="form-control" name="city" value="{{old('city')}}" required="">
                                @error('city') <span class="text-danger">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" my-3">
                                <label for="address">House no, Building Name *</label>
                                <input type="text" class="form-control" name="address" value="{{old('address')}}" required="">
                                @error('address') <span class="text-danger">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" my-3">
                                <label for="locality">Road Name, Area, Colony *</label>
                                <input type="text" class="form-control" name="locality" value="{{old('locality')}}" required="">
                                @error('locality') <span class="text-danger">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class=" my-3">
                                <label for="landmark">Landmark *</label>
                                <input type="text" class="form-control" name="landmark" value="{{old('landmark')}}" required="">
                                @error('landmark') <span class="text-danger">{{$message}}</span>@enderror
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="checkout__totals-wrapper">
                    <div class="sticky-content">
                        <div class="checkout__totals">
                            <h3>Your Order</h3>
                            <table class="checkout-cart-items">
                                <thead>
                                    @foreach (Cart::instance('cart') as $item )
                                    <tr>
                                        <th>PRODUCT</th>
                                        <th align="right">SUBTOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            {{$item->name}} x {{$item->qty}}
                                        </td>
                                        <td align="right">
                                            ${{$item->subtotal()}}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if(Session::has('discounts'))
                            <table class="cart-totals">

                                <tbody>
                                    <tr>
                                        <th>Subtotal</th>
                                        <td>${{Cart::instance('cart')->subtotal()}}</td>
                                    </tr>
                                    <tr>
                                        <th>Discount {{Session::get('coupon')['code']}}</th>
                                        <td>${{Session::get('discounts')['discount']}}</td>
                                    </tr>
                                    <tr>
                                        <th>Subtotal after discount</th>
                                        <td>${{Session::get('discounts')['subtotal']}}</td>
                                    </tr>
                                    <tr>
                                        <th>Shipping</th>
                                        <td>
                                            <div>Free</div>
                                            <!-- <div class="form-check">
                        <input class="form-check-input form-check-input_fill" type="checkbox" value=""
                          id="free_shipping">
                        <label class="form-check-label" for="free_shipping">Free shipping</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input form-check-input_fill" type="checkbox" value="" id="flat_rate">
                        <label class="form-check-label" for="flat_rate">Flat rate: $49</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input form-check-input_fill" type="checkbox" value=""
                          id="local_pickup">
                        <label class="form-check-label" for="local_pickup">Local pickup: $8</label>
                      </div>
                      <div>Shipping to AL.</div>
                      <div>
                        <a href="#" class="menu-link menu-link_us-s">CHANGE ADDRESS</a>
                      </div> -->
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>VAT</th>
                                        <td>${{Session::get('discounts')['tax']}}</td>
                                    </tr>
                                    <tr>
                                        <th>Total</th>
                                        <td>${{Session::get('discounts')['total']}}</td>
                                    </tr>
                                </tbody>
                            </table>

                            @else
                            <table class="checkout-totals">
                                <tbody>
                                    <tr>
                                        <th>SUBTOTAL</th>
                                        <td align="right">${{Cart::instance('cart')->subtotal()}}</td>
                                    </tr>
                                    <tr>
                                        <th>SHIPPING</th>
                                        <td align="right">Free shipping</td>
                                    </tr>
                                    <tr>
                                        <th>VAT</th>
                                        <td align="right">${{Cart::instance('cart')->tax()}}</td>
                                    </tr>
                                    <tr>
                                        <th>TOTAL</th>
                                        <td align="right">${{Cart::instance('cart')->total()}}</td>
                                    </tr>

                                </tbody>
                            </table>
                            @endif
                        </div>
                        <div class="checkout__payment-methods">
                            <!-- <div class="form-check">
                  <input class="form-check-input form-check-input_fill" type="radio" name="checkout_payment_method"
                    id="checkout_payment_method_1" checked>
                  <label class="form-check-label" for="checkout_payment_method_1">
                    Direct bank transfer
                    <p class="option-detail">
                      Make your payment directly into our bank account. Please use your Order ID as the payment
                      reference.Your order will not be shipped until the funds have cleared in our account.
                    </p>
                  </label>
                </div> -->
                            <div class="form-check">
                                <input class="form-check-input form-check-input_fill" type="radio" name="mode"
                                    id="mode1" value="card">
                                <label class="form-check-label" for="mode1">
                                    Debit or credit card


                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input form-check-input_fill" type="radio" name="mode"
                                    id="mode2" value="paypal">
                                <label class="form-check-label" for="mode2">
                                    Paypal

                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input form-check-input_fill" type="radio" name="mode"
                                    id="mode3" value="cod">
                                <label class="form-check-label" for="mode3">
                                    Cash on delivery

                                </label>
                            </div>

                            <div class="policy-text">
                                Your personal data will be used to process your order, support your experience throughout this
                                website, and for other purposes described in our <a href="terms.html" target="_blank">privacy
                                    policy</a>.
                            </div>
                        </div>
                        <button class="btn btn-primary btn-checkout" type="submit">PLACE ORDER</button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</main>

@endsection