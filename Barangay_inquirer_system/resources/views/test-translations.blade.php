@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Translation Test</h1>
            <p class="lead">Current Locale: <strong>{{ app()->getLocale() }}</strong></p>

            <div class="card">
                <div class="card-header">
                    <h5>Sample Translations</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Translation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>hero_title</td>
                                <td>{{ __('messages.hero_title') }}</td>
                            </tr>
                            <tr>
                                <td>about_title</td>
                                <td>{{ __('messages.about_title') }}</td>
                            </tr>
                            <tr>
                                <td>login</td>
                                <td>{{ __('messages.login') }}</td>
                            </tr>
                            <tr>
                                <td>register</td>
                                <td>{{ __('messages.register') }}</td>
                            </tr>
                            <tr>
                                <td>announcements</td>
                                <td>{{ __('messages.announcements') }}</td>
                            </tr>
                            <tr>
                                <td>read_more</td>
                                <td>{{ __('messages.read_more') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('language.switch', 'en') }}" class="btn btn-primary me-2">Switch to English</a>
                <a href="{{ route('language.switch', 'ceb') }}" class="btn btn-success">Switch to Cebuano</a>
                <a href="{{ url('/') }}" class="btn btn-secondary ms-2">Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endsection