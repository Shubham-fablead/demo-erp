  @extends('layout.app')

  @section('title', 'Advance Payment View')

  @section('content')
      <div class="content">
          <div class="page-header">
              <div class="page-title">
                  <h4>Advance Payment View</h4>
              </div>
              <div class="page-btn d-flex gap-2">
                @if (app('hasPermission')(23, 'edit'))
                  <a href="{{ route('advance_pay.edit', $id) }}" class="btn btn-added">
                      <i class="fa fa-edit me-1"></i> Edit
                  </a>
                @endif
                @if (app('hasPermission')(23, 'view'))
                  <a href="{{ route('advance_pay.index') }}" class="btn" style="background: #1b2850; color: #fff;">
                      Back
                  </a>
                @endif
              </div>
          </div>
          <div class="">
              <div class="card-body">
                  <div class="row">
                      <!-- Staff Info -->
                      <div class="col-xl-4 col-sm-4">
                          <div class="card">
                              <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                                  <img src="" alt="Profile" class="img-fluid rounded-circle border profile-image"
                                      style="width: 150px; height: 150px; object-fit: cover;">
                                  <h5 class="mt-3 profile-name"></h5>
                              </div>
                          </div>
                      </div>

                      <!-- Payment Info -->
                      <div class="col-xl-8 col-sm-8">
                          <div class="card">
                              <div class="card-body pt-3">
                                  <h5 class="card-title">Payment Details</h5>

                                  <div class="row mb-2">
                                      <div class="col-lg-4 label">Staff Name</div>
                                      <div class="col-lg-8"><span class="profile-name"></span></div>
                                  </div>
                                  <hr>
                                  <div class="row mb-2">
                                      <div class="col-lg-4 label">Email</div>
                                      <div class="col-lg-8"><span class="profile-email"></span></div>
                                  </div>
                                  <hr>
                                  <div class="row mb-2">
                                      <div class="col-lg-4 label">Amount</div>
                                      <div class="col-lg-8"><span class="payment-amount"></span></div>
                                  </div>
                                  <hr>
                                  <div class="row mb-2">
                                      <div class="col-lg-4 label">Date</div>
                                      <div class="col-lg-8"><span class="payment-date"></span></div>
                                  </div>
                                  <hr>
                                  <div class="row mb-2">
                                      <div class="col-lg-4 label">Method</div>
                                      <div class="col-lg-8"><span class="payment-method"></span></div>
                                  </div>
                                  <hr>
                                  <div class="row mb-2">
                                      <div class="col-lg-4 label">Reason</div>
                                      <div class="col-lg-8"><span class="payment-reason"></span></div>
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

              function formatCurrencyIN(value) {
                  let number = parseFloat(value) || 0;

                  return '₹' + number.toLocaleString('en-IN', {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2
                  });
              }

              const authToken = localStorage.getItem("authToken");
              const paymentId = '{{ $id }}';

              $.ajax({
                  url: `/api/advance-payments/${paymentId}`,
                  method: 'GET',
                  headers: {
                      "Authorization": "Bearer " + authToken,
                  },
                  success: function(res) {
                      const staff = res.staff || {};
                      const imagePath = '{{ env('ImagePath') }}';

                      // Staff data
                      $('.profile-name').text(staff.name ?? 'N/A');
                      $('.profile-email').text(staff.email ?? 'N/A');

                      // Payment data
                      //   $('.payment-amount').text(res.amount ?? 'N/A');
                      $('.payment-amount').text(
                          res.amount ? formatCurrencyIN(res.amount) : 'N/A'
                      );
                      $('.payment-date').text(res.date ?? 'N/A');
                      $('.payment-method').text(res.method ?? 'N/A');
                      $('.payment-reason').text(res.reason ?? 'N/A');


                      // Profile image
                      const imageSrc = staff.profile_image ?
                          `${imagePath}/storage/${staff.profile_image}` :
                          `${imagePath}/admin/assets/img/customer/customer5.jpg`;
                      $('.profile-image').attr('src', imageSrc);
                  },
                  error: function(err) {
                      Swal.fire("Error!", "Advance payment record not found!", "error");
                  }
              });
          });
      </script>
  @endpush
