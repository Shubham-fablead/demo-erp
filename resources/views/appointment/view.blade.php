@extends('layout.app')

@section('title', 'Appointment View')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Appointment View</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('appointments.index') }}" class="btn btn-added">
                    Back
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-12 col-sm-12">
                        <div class="card">
                            <div class="card-header bg-light border-bottom">
                                <h5 class="mb-0 text-secondary">Appointment Information</h5>
                            </div>
                            <div class="card-body pt-3">
                                <div class="tab-content pt-2">
                                    <div class="tab-pane fade show active profile-overview" id="profile-overview">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Customer Name:</div>
                                                    <div class="col-lg-7 col-md-6">{{ $appointment->name ?? 'N/A' }}</div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Phone:</div>
                                                    <div class="col-lg-7 col-md-6">{{ $appointment->phone ?? 'N/A' }}</div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Address:</div>
                                                    <div class="col-lg-7 col-md-6">{{ $appointment->address ?? 'N/A' }}
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Vehicle Number:</div>
                                                    <div class="col-lg-7 col-md-6">
                                                        {{ $appointment->vehicle_number ?? 'N/A' }}</div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Vehicle Type:</div>
                                                    <div class="col-lg-7 col-md-6">{{ $appointment->vehicle_type ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Vehicle Model:</div>
                                                    <div class="col-lg-7 col-md-6">
                                                        @if ($appointment->vehicle_model)
                                                            @php
                                                                $model = $modals
                                                                    ->where('id', $appointment->vehicle_model)
                                                                    ->first();
                                                            @endphp
                                                            {{ $model->model_name ?? 'N/A' }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Appointment Date:</div>
                                                    <div class="col-lg-7 col-md-6">
                                                        {{ $appointment->appointment_date ?? 'N/A' }}</div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Appointment Time:</div>
                                                    <div class="col-lg-7 col-md-6">
                                                        {{ $appointment->appointment_time ?? 'N/A' }}</div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Status:</div>
                                                    <div class="col-lg-7 col-md-6">{{ $appointment->status ?? 'N/A' }}</div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-5 col-md-6 label fw-bold">Remarks:</div>
                                                    <div class="col-lg-7 col-md-6">{{ $appointment->remarks ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
