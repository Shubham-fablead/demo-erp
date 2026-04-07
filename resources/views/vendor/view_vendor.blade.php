    @extends('layout.app')

    @section('title', 'Vendor View')

    @section('content')
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Vendor View</h4>
                </div>
             
                <div class="page-btn d-flex gap-2">

                    <!-- Edit -->
                    @if (app('hasPermission')(10, 'edit'))
                    <a href="{{ route('vendor.edit', $id) }}" class="btn btn-added">
                        <i class="fa fa-edit me-1"></i> Edit
                    </a>
                    @endif

                    <!-- Back -->
                    @if (app('hasPermission')(10, 'view'))
                    <a href="{{ route('vendor.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                        Back
                    </a>
                    @endif
                </div>
            </div>
            <div class="">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-sm-4">
                            <div class="card">
                                <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                                    <img src="" alt="Profile" class="img-fluid rounded-circle border"
                                        style="width: 150px; height: 150px; object-fit: cover;">
                                    <!-- profile-img.jpg -->
                                    <h5 class="mt-3"><span class="profile-name"></span></h5>
                                    <!-- <h4><span class="profile-role"></span></h4> -->
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-sm-8">
                            <div class="card">
                                <div class="card-body pt-3">

                                    <div class="tab-content pt-2">

                                        <div class="tab-pane fade show active profile-overview" id="profile-overview">

                                            <h5 class="card-title">Profile Details</h5>

                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 label">Name</div>
                                                <div class="col-lg-9 col-md-8"><span class="profile-name"></span></div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 label">Email</div>
                                                <div class="col-lg-9 col-md-8"><span class="profile-email"></span></div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 label">Contact Number</div>
                                                <div class="col-lg-9 col-md-8"><span class="profile-phone"></span></div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 label">PAN Number</div>
                                                <div class="col-lg-9 col-md-8"><span class="profile-pan_number"></span>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 label">GST Number</div>
                                                <div class="col-lg-9 col-md-8"><span class="profile-gst_number"></span>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 label">Address</div>
                                                <div class="col-lg-9 col-md-8"><span class="profile-address"></span></div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 label">Coutry</div>
                                                <div class="col-lg-9 col-md-8"><span class="profile-country"></span></div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 label">City</div>
                                                <div class="col-lg-9 col-md-8"><span class="profile-city"></span></div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 label">State Code</div>
                                                <div class="col-lg-9 col-md-8"><span class="profile-state_code"></span></div>
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
    @push('js')
        <script>
            $(document).ready(function() {
                var authToken = localStorage.getItem("authToken");
                let userId = '{{ $id }}';
                const stateCodeMap = {
                    '01': 'Jammu and Kashmir',
                    '02': 'Himachal Pradesh',
                    '03': 'Punjab',
                    '04': 'Chandigarh',
                    '05': 'Uttarakhand',
                    '06': 'Haryana',
                    '07': 'Delhi',
                    '08': 'Rajasthan',
                    '09': 'Uttar Pradesh',
                    '10': 'Bihar',
                    '11': 'Sikkim',
                    '12': 'Arunachal Pradesh',
                    '13': 'Nagaland',
                    '14': 'Manipur',
                    '15': 'Mizoram',
                    '16': 'Tripura',
                    '17': 'Meghalaya',
                    '18': 'Assam',
                    '19': 'West Bengal',
                    '20': 'Jharkhand',
                    '21': 'Odisha',
                    '22': 'Chhattisgarh',
                    '23': 'Madhya Pradesh',
                    '24': 'Gujarat',
                    '25': 'Daman and Diu',
                    '26': 'Dadra and Nagar Haveli and Daman and Diu',
                    '27': 'Maharashtra',
                    '28': 'Andhra Pradesh',
                    '29': 'Karnataka',
                    '30': 'Goa',
                    '31': 'Lakshadweep',
                    '32': 'Kerala',
                    '33': 'Tamil Nadu',
                    '34': 'Puducherry',
                    '35': 'Andaman and Nicobar Islands',
                    '36': 'Telangana',
                    '37': 'Andhra Pradesh (New)',
                    '38': 'Ladakh'
                };

                function formatStateCode(stateValue) {
                    if (!stateValue || stateValue.toString().trim() === '') {
                        return 'N/A';
                    }

                    const raw = stateValue.toString().trim();
                    const combinedMatch = raw.match(/^(\d{1,3})\s*-\s*(.+)$/);
                    if (combinedMatch) {
                        const code = combinedMatch[1].padStart(2, '0');
                        const name = combinedMatch[2].trim();
                        return `${code} - ${name}`;
                    }

                    if (/^\d{1,3}$/.test(raw)) {
                        const code = raw.padStart(2, '0');
                        return stateCodeMap[code] ? `${code} - ${stateCodeMap[code]}` : code;
                    }

                    const normalizedName = raw.toLowerCase().replace(/\s+/g, ' ');
                    for (const [code, name] of Object.entries(stateCodeMap)) {
                        if (name.toLowerCase() === normalizedName) {
                            return `${code} - ${name}`;
                        }
                    }

                    return raw;
                }

                $.ajax({
                    url: `/api/vendor/${userId}`,
                    method: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(res) {
                        $('.profile-name').text(res.name ?? 'N/A');
                        $('.profile-email').text(res.email ?? 'N/A');
                        $('.profile-phone').text(res.phone ?? 'N/A');
                        $('.profile-role').text(res.role ?? 'N/A');
                        $('.profile-address').text(res.address && res.address.trim() !== '' ? res.address :
                            'N/A');
                        $('.profile-country').text(res.country && res.country.trim() !== '' ? res.country :
                            'N/A');
                        $('.profile-city').text(res.city && res.city.trim() !== '' ? res.city : 'N/A');
                        $('.profile-state_code').text(formatStateCode(res.state_code));
                        $('.profile-pan_number').text(res.pan_number && res.pan_number.trim() !== '' ? res
                            .pan_number : 'N/A');
                        $('.profile-gst_number').text(res.gst_number && res.gst_number.trim() !== '' ? res
                            .gst_number : 'N/A');

                        if (res.profile_image) {
                            $('img[alt="Profile"]').attr('src',
                                `{{ env('ImagePath') . '/storage/' }}${res.profile_image}`);
                        } else {
                            $('img[alt="Profile"]').attr('src',
                                '{{ env('ImagePath') . '/admin/assets/img/customer/customer5.jpg' }}');
                        }
                    },
                    error: function(err) {
                        Swal.fire("Error!", "Vendor not found!", "error");
                        window.location.href = "{{ route('vendor.list') }}";
                    }
                });
            });
        </script>
    @endpush
