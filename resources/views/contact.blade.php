@extends('layouts.app')
@section('content')

<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="contact-us container">
        <div class="mw-930">
            <h2 class="page-title">CONTACT US</h2>
        </div>
    </section>

    <hr class="mt-2 text-secondary " />
    <div class="mb-4 pb-4"></div>

    <section class="contact-us container">
        <div class="mw-930">
            <div class="contact-us__form">
                @if (session('status'))
                <span>{{session('status')}}</span>
                @endif
                <form name="contact-us-form" class="needs-validation" novalidate="" method="POST" action="{{route('home.contact.store')}}">
                    @csrf
                    <h3 class="mb-5">Get In Touch</h3>
                    <div class=" my-4">
                        <label for="contact_us_name">Name *</label>

                        <input type="text" class="form-control" name="name" placeholder="Name *" required="" value="{{old('name')}}">
                        @error('name')<span class="text-danger">{{$message}}</span>@enderror
                    </div>
                    <div class=" my-4">
                        <label for="contact_us_name">Phone *</label>

                        <input type="text" class="form-control" name="phone" placeholder="Phone *" required="" value="{{old('phone')}}">
                        @error('phone')<span class="text-danger">{{$message}}</span>@enderror
                    </div>
                    <div class=" my-4">
                        <label for="contact_us_name">Email address *</label>

                        <input type="email" class="form-control" name="email" placeholder="Email address *" required="" value="{{old('email')}}">
                        @error('email')<span class="text-danger">{{$message}}</span>@enderror
                    </div>
                    <div class="my-4">
                        <label for="contact_us_name">Your Message *</label>

                        <textarea class="form-control form-control_gray" name="comment" placeholder="Your Message" cols="30"
                            rows="8" required="" value="">{{old('comment')}}</textarea>
                        @error('comment')<span class="text-danger">{{$message}}</span>@enderror
                    </div>
                    <div class="my-4">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

@endsection