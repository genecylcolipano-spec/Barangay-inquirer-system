@extends('layouts.app')

@section('content')

<section class="page-hero">
    <div class="container text-center">
        <h1>Contact Us</h1>
        <p>We would love to hear from you</p>
    </div>
</section>

<section class="page-section">
    <div class="container">

        <div class="row">

            <div class="col-md-6 mb-4">
                <h4>Barangay Information</h4>
                <p>Email: barangay@gmail.com</p>
                <p>Phone: 0912-345-6789</p>
                <p>Location: Barangay Hall</p>
            </div>

            <div class="col-md-6 mb-4">
                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="phone">Phone (Optional)</label>
                        <input type="text" name="phone" class="form-control" placeholder="Your Phone">
                    </div>

                    <div class="form-group mb-3">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="message">Message</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>

        </div>

    </div>
</section>

@endsection
