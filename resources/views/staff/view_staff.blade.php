  @extends('layout.app')

  @section('title', 'Staff View')

  @section('content')
  <style>
    .profile-card .profile-name {
    text-align: center;
    font-weight: 600;
    word-break: break-word;
}
  </style>
      <div class="content">
          <div class="page-header">
              <div class="page-title">
                  <h4>Staff View</h4>
              </div>

              <div class="page-btn d-flex gap-2">

                  <!-- Edit -->
                  @if (app('hasPermission')(8, 'edit'))
                  <a href="{{ route('staff.edit', $id) }}" class="btn btn-added">
                      <i class="fa fa-edit me-1"></i> Edit
                  </a>
                  @endif

                  <!-- Back -->
                  @if (app('hasPermission')(8, 'view'))
                  <a href="{{ route('staff.list') }}" class="btn" style="background: #1b2850; color: #fff;">
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
                                              <div class="col-lg-3 col-md-4 label">Role</div>
                                              <div class="col-lg-9 col-md-8"><span class="profile-role"></span></div>
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
                                              <div class="col-lg-3 col-md-4 label">GST Number</div>
                                              <div class="col-lg-9 col-md-8"><span class="profile-gst_number"></span></div>
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
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>

                  <hr>


                  <h5 class="mt-4 fw-bold">PERMISSION :</h5>



                  <div class="table-responsive">
                      <table class="table table-bordered table-hover align-middle" id="module-permission-table">
                          <thead class="table-light">
                              <tr class="text-center">
                                  <th class="text-start">Module Name</th>
                                  <th>View</th>
                                  <th>Insert</th>
                                  <th>Edit</th>
                                  <th>Delete</th>

                              </tr>
                          </thead>
                          <tbody>
                              <!-- Filled by JavaScript -->
                          </tbody>
                      </table>
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

              $.ajax({
                  url: `/api/staff/${userId}`,
                  method: 'GET',
                  headers: {
                      "Authorization": "Bearer " + authToken,
                  },
                  success: function(res) {

                      $('.profile-name').text(res.name ?? 'N/A');
                      $('.profile-role').text(res.role ?? 'N/A');
                      $('.profile-email').text(res.email ?? 'N/A');
                      $('.profile-phone').text(res.phone ?? 'N/A');
                      $('.profile-gst_number').text(res.gst_number ?? 'N/A');
                      $('.profile-role').text(res.role ?? 'N/A');
                      $('.profile-address').text(res.address ?? 'N/A');
                      $('.profile-country').text(res.country ?? 'N/A');
                      $('.profile-city').text(res.city ?? 'N/A');

                      if (res.profile_image) {
                          $('img[alt="Profile"]').attr(
                              'src',
                              `{{ env('ImagePath') . '/storage/' }}${res.profile_image}`
                          );
                      } else {
                          $('img[alt="Profile"]').attr(
                              'src',
                              `{{ env('ImagePath') . '/admin/assets/img/customer/customer5.jpg' }}`
                          );
                      }
                      let permissionRows = '';
                      if (res.permissions && res.permissions.length > 0) {
                          res.permissions.forEach(function(perm) {
                              permissionRows += `
            <tr class="text-center">
                <td class="text-start">${perm.module}</td>
                <td>${perm.view ? '✔️' : '❌'}</td>
                <td>${perm.add ? '✔️' : '❌'}</td>
                <td>${perm.edit ? '✔️' : '❌'}</td>
                <td>${perm.delete ? '✔️' : '❌'}</td>
            </tr>
        `;
                          });
                      } else {
                          permissionRows =
                              `<tr><td colspan="5" class="text-center">No permissions assigned.</td></tr>`;
                      }

                      $('#module-permission-table tbody').html(permissionRows);

                  },
                  error: function(err) {
                      Swal.fire("Error!", "Staff not found!", "error");

                  }
              });
          });
      </script>
  @endpush
