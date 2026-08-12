@extends('layouts.app')

@section('content')

<section class="page-hero">
    <div class="container text-center">
        <h1>Barangay Services</h1>
        <p>List of services you can request online</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div class="row">

            <div class="col-md-4 mb-4">
                <div class="service-box">
                    <h5>Barangay Clearance</h5>
                    <p>Official clearance for employment and legal purposes.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="service-box">
                    <h5>Certificate of Indigency</h5>
                    <p>Proof of financial status.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="service-box">
                    <h5>Purok Clearance</h5>
                    <p>Clearance from assigned purok.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="service-box">
                    <h5>Cedula</h5>
                    <p>Community tax certificate.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="service-box">
                    <h5>Resident Records</h5>
                    <p>Registration and updating of resident information.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="service-box">
                    <h5>Complaints & Requests</h5>
                    <p>Submit concerns and suggestions.</p>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
