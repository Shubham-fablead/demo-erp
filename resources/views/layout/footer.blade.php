<script src="{{ env('ImagePath').'/admin/assets/js/jquery-3.6.0.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/feather.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/jquery.slimscroll.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/jquery.dataTables.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/dataTables.bootstrap4.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/bootstrap.bundle.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/apexchart/apexcharts.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/apexchart/chart-data.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/moment.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/flot/jquery.flot.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/flot/jquery.flot.fillbetween.js' }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot.tooltip/0.9.0/jquery.flot.tooltip.min.js"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/flot/jquery.flot.pie.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/flot/chart-data.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/script.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/js/bootstrap-datetimepicker.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/owlcarousel/owl.carousel.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/select2/js/select2.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/sweetalert/sweetalert2.all.min.js' }}"></script>
<script src="{{ env('ImagePath').'/admin/assets/plugins/sweetalert/sweetalerts.min.js' }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>



<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>




<!-- Active Link Script -->
<script>
    $(document).ready(function () {
        var currentUrl = window.location.href.split(/[?#]/)[0]; // remove query/hash if any

        $('#sidebar-menu a').each(function () {
            var linkUrl = this.href.split(/[?#]/)[0];

            if (linkUrl === currentUrl) {
                $(this).addClass('active');

                // If it's inside a submenu, expand it
                var submenuLi = $(this).closest('li.submenu');
                if (submenuLi.length > 0) {
                    submenuLi.find('ul').show(); // Show the submenu
                    submenuLi.find('a:first').addClass('active subdrop'); // Highlight parent
                }

                // Scroll the active menu item into view
                setTimeout(() => {
                    this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        });
    });

</script>