<footer class="footer nk-footer bg-white">
    <div class="container-fluid">
        <div class="nk-footer-wrap">
            <div class="nk-footer-copyright">Multi Purpose Cooperative Admin</div>
            <div class="nk-footer-copyright">&copy; <?php echo date('Y'); ?> Bodare MPC CMS</div>
        </div>
    </div>
</footer>
                        </div><!-- container-xl -->
                    </div><!-- nk-content -->
                </div><!-- nk-wrap -->
            </div><!-- nk-main -->
        </div><!-- nk-app-root -->


<!--   Core JS Files   -->
<script> var baseurl = "<?php echo base_url(); ?>"; </script>

<script src="<?php echo base_url(); ?>assets/dashlite/js/bundle.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo base_url(); ?>assets/dashlite/js/scripts.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo base_url(); ?>js/coop-modal-compat.js?v=<?php echo time(); ?>"></script>
<script>
/* Sidebar submenu toggle - independent so other script errors cannot break it */
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    function childMenu(parent) {
        if (!parent || !parent.children) {
            return null;
        }
        for (var i = 0; i < parent.children.length; i++) {
            var el = parent.children[i];
            if (el.tagName === 'UL' && el.classList.contains('nav_child')) {
                return el;
            }
        }
        return null;
    }

    function setIcon(parent, isOpen) {
        var p = parent.querySelector('a > p');
        if (!p) {
            return;
        }
        var icon = p.querySelector('i.right') || p.querySelector('i:last-child');
        if (icon) {
            icon.textContent = isOpen ? 'remove_circle' : 'add_circle';
        }
    }

    function closeAll(except) {
        var parents = document.querySelectorAll('.coop-sidebar-nav li.nav_parent');
        for (var i = 0; i < parents.length; i++) {
            if (except && parents[i] === except) {
                continue;
            }
            parents[i].classList.remove('open');
            var child = childMenu(parents[i]);
            if (child) {
                child.classList.remove('open');
            }
            setIcon(parents[i], false);
        }
    }

    ready(function () {
        var nav = document.querySelector('.coop-sidebar-nav');
        if (!nav) {
            return;
        }

        var activeParents = nav.querySelectorAll('li.nav_parent.active');
        for (var a = 0; a < activeParents.length; a++) {
            activeParents[a].classList.add('open');
            var activeChild = childMenu(activeParents[a]);
            if (activeChild) {
                activeChild.classList.add('open');
            }
            setIcon(activeParents[a], true);
        }

        var allParents = nav.querySelectorAll('li.nav_parent');
        for (var p = 0; p < allParents.length; p++) {
            if (!allParents[p].classList.contains('open')) {
                setIcon(allParents[p], false);
            }
        }

        nav.addEventListener('click', function (event) {
            var target = event.target;
            if (!target) {
                return;
            }

            // Ignore clicks on submenu links so they navigate normally
            if (target.closest && target.closest('ul.nav_child a')) {
                return;
            }

            var link = target.closest ? target.closest('li.nav_parent > a') : null;
            if (!link) {
                // Fallback for older browsers
                var node = target;
                while (node && node !== nav) {
                    if (node.tagName === 'A' && node.parentElement && node.parentElement.classList.contains('nav_parent')) {
                        link = node;
                        break;
                    }
                    node = node.parentElement;
                }
            }
            if (!link || !nav.contains(link)) {
                return;
            }

            var parent = link.parentElement;
            var child = childMenu(parent);
            if (!child) {
                return; // no submenu, allow default link behavior
            }

            event.preventDefault();
            event.stopPropagation();

            var willOpen = !parent.classList.contains('open');
            closeAll(parent);

            if (willOpen) {
                parent.classList.add('open');
                child.classList.add('open');
                setIcon(parent, true);
            } else {
                parent.classList.remove('open');
                child.classList.remove('open');
                setIcon(parent, false);
            }
        }, true);
    });
})();
</script>

<script src="<?php echo base_url(); ?>assets/js/jquery-ui.min.js" type="text/javascript"></script>

<!-- Image Cropper -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropper/3.1.3/cropper.min.js"></script>	

<!-- DatePicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>

<!--  Charts Plugin -->
<script src="<?php echo base_url(); ?>assets/js/chartist.min.js"></script>

<!--  Plugin for the Wizard -->
<script src="<?php echo base_url(); ?>assets/js/jquery.bootstrap-wizard.js"></script>

<!--   Sharrre Library    -->
<script src="<?php echo base_url(); ?>assets/js/jquery.sharrre.js"></script>


<!-- Select Plugin -->
<script src="<?php echo base_url(); ?>assets/js/jquery.select-bootstrap.js"></script>

<!--  DataTables.net Plugin    -->
<script src="<?php echo base_url(); ?>assets/js/jquery.datatables.js"></script>

<!--  Full Calendar Plugin    -->
<script src="<?php echo base_url(); ?>assets/js/fullcalendar.min.js"></script>

<!-- Material Dashboard javascript methods removed for DashLite -->

<!--  Jquery Sortable Plugin    -->
<script src="<?php echo base_url(); ?>js/jquery-sortable.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom JS Files Added -->
<script src="<?php echo base_url(); ?>js/dashboard.js?v=<?php echo time(); ?>"></script>

<!-- Include Trumbowyg Editor JS -->
<script src="<?php echo base_url(); ?>trumbowyg/dist/trumbowyg.min.js"></script>


<!-- Nice Select -->
<script src="<?php echo base_url(); ?>js/jquery.nice-select.min.js"></script>

<!-- Color Picker -->
<script src="<?php echo base_url(); ?>js/bootstrap-colorpicker.min.js"></script>

<!-- Jquery datatable tools js -->
<script type="text/javascript" src="<?php echo base_url('datatables/js/dataTables.bootstrap4.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('datatables/js/dataTables.buttons.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('datatables/js/buttons.bootstrap4.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('datatables/js/buttons.print.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('datatables/js/pdfmake.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('datatables/js/pdfmake.min.js.map'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('datatables/js/vfs_fonts.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('datatables/js/buttons.flash.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('datatables/js/buttons.html5.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('datatables/js/buttons.colVis.min.js'); ?>"></script>
<script src="<?php echo base_url(); ?>js/coop-table-tranx.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo base_url(); ?>js/coop-table-page-header.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo base_url(); ?>js/coop-form-elements.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo base_url(); ?>js/iniDatatables.js"></script>

<script>


<?php if ($this->uri->segment(1) == 'dashboard' && $this->uri->segment(2) == '' || $this->uri->segment(1) == 'dashboard' && $this->uri->segment(2) == 'dashboard') { ?>

    /*  **************** Simple Bar Chart - barchart ******************** */

    var dataSimpleBarChart = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        series: [
            [<?php echo $browse_collect_jan; ?>, <?php echo $browse_collect_feb; ?>, <?php echo $browse_collect_mar; ?>, <?php echo $browse_collect_apr; ?>, <?php echo $browse_collect_may; ?>, <?php echo $browse_collect_jun; ?>, <?php echo $browse_collect_jul; ?>, <?php echo $browse_collect_aug; ?>, <?php echo $browse_collect_sep; ?>, <?php echo $browse_collect_oct; ?>, <?php echo $browse_collect_nov; ?>, <?php echo $browse_collect_dec; ?>],
            [<?php echo $browse_spend_jan; ?>, <?php echo $browse_spend_feb; ?>, <?php echo $browse_spend_mar; ?>, <?php echo $browse_spend_apr; ?>, <?php echo $browse_spend_may; ?>, <?php echo $browse_spend_jun; ?>, <?php echo $browse_spend_jul; ?>, <?php echo $browse_spend_aug; ?>, <?php echo $browse_spend_sep; ?>, <?php echo $browse_spend_oct; ?>, <?php echo $browse_spend_nov; ?>, <?php echo $browse_spend_dec; ?>],
            [<?php echo $browse_collect_jan - $browse_spend_jan; ?>, <?php echo $browse_collect_feb - $browse_spend_feb; ?>, <?php echo $browse_collect_mar - $browse_spend_mar; ?>, <?php echo $browse_collect_apr - $browse_spend_apr; ?>, <?php echo $browse_collect_may - $browse_spend_may; ?>, <?php echo $browse_collect_jun - $browse_spend_jun; ?>, <?php echo $browse_collect_jul - $browse_spend_jul; ?>, <?php echo $browse_collect_aug - $browse_spend_aug; ?>, <?php echo $browse_collect_sep - $browse_spend_sep; ?>, <?php echo $browse_collect_oct - $browse_spend_oct; ?>, <?php echo $browse_collect_nov - $browse_spend_nov; ?>, <?php echo $browse_collect_dec - $browse_spend_dec; ?>]
        ]
    };

    var optionsSimpleBarChart = {
        seriesBarDistance: 10,
        axisX: {
            showGrid: true
        },
        height: '200px'
    };

    var responsiveOptionsSimpleBarChart = [
        ['screen and (max-width: 640px)', {
                seriesBarDistance: 5,
                axisX: {
                    labelInterpolationFnc: function (value) {
                        return value[0];
                    }
                }
            }]
    ];

    var simpleBarChart = Chartist.Bar('#simpleBarChart', dataSimpleBarChart, optionsSimpleBarChart, responsiveOptionsSimpleBarChart);

    //start animation for the Emails Subscription Chart
    if (typeof md !== 'undefined' && md.startAnimationForBarChart) {
        md.startAnimationForBarChart(simpleBarChart);
    }

<?php } ?>

    //$.noConflict();
    jQuery(document).ready(function ($) {}); // End Of NoConflict 
        
    /*************** Destroying Scrollbar On Cropper Container *********************/
    try {
        if (window.jQuery && jQuery.fn.perfectScrollbar) {
            jQuery(".cropper-container").perfectScrollbar('destroy');
        }
    } catch (e) {}

    /*************** HTML Text Editor Trumbowyg *********************/
    try {
        if (window.jQuery && jQuery.fn.trumbowyg) {
            jQuery('textarea').trumbowyg({
                svgPath: "<?php echo base_url(); ?>trumbowyg/dist/ui/icons.svg",
                btns: [
                    ['viewHTML'],
                    ['undo', 'redo'],
                    ['formatting'],
                    ['strong', 'em'],
                    ['link'],
                    ['insertImage'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat']
                ]
            });
        }
    } catch (e) {}
    /*$('textarea').trumbowyg({
        svgPath: "<?php echo base_url(); ?>trumbowyg/dist/ui/icons.svg"
    });*/
    
    jQuery(document).ready(function ($) {

        // Keep plugins optional so other features still work if they fail
        try {
            if ($.fn.datepicker) {
                $('.datepicker').datepicker({ format: 'dd-mm-yyyy' });
            }
        } catch (e) {}
        try {
            if ($('#color').length && $.fn.colorpicker) {
                $('#color').colorpicker();
            }
        } catch (e) {}
        try {
            // Destroy legacy nice-select; Select2 is handled once by coop-form-elements.js
            if ($.fn.niceSelect) {
                try { $('.select').niceSelect('destroy'); } catch (ignore) {}
                $('.nice-select').remove();
            }
            if (window.coopEnhanceAdminForms) {
                window.coopEnhanceAdminForms();
            }
        } catch (e) {}

    });



/******************************************************************************/
/******************************************************************************/
/******************************************************************************/    

    /************************************************/
    /******** Sorting Default Table ******************/
    /************************************************/
    $('.sorted_table').on('click', function () {
        var group = $(".sorted_table").sortable({
            containerSelector: 'table',
            itemPath: '> tbody',
            itemSelector: 'tr',
            placeholder: '<tr class="placeholder"/>',
            delay: 500,
            onDrop: function ($item, container, _super) {
                var data = group.sortable("serialize").get();
                var jsonString = JSON.stringify(data, null, ' ');
                _super($item, container);
                $.post(baseurl + 'dashboard/section/sortSection', {sort: jsonString}, function () {
                });
                //$('#serialize_output').html(jsonString);
            }
        });
    });

    /************************************************/
    /******** Sorting Menu Table ******************/
    /************************************************/
    $('.sorted_menu_table').on('click', function () {
        var group = $(".sorted_menu_table").sortable({
            containerSelector: 'table',
            itemPath: '> tbody',
            itemSelector: 'tr',
            placeholder: '<tr class="placeholder"/>',
            delay: 500,
            onDrop: function ($item, container, _super) {
                var data = group.sortable("serialize").get();
                var jsonString = JSON.stringify(data, null, ' ');
                _super($item, container);
                $.post(baseurl + 'dashboard/menu/sortmenu', {sort: jsonString}, function () {
                });
                $('#serialize_output').html(jsonString);
            }
        });
    });

    /************************************************/
    /******** Sorting Gallery Table ******************/
    /************************************************/
    $('.sorted_gallery_table').on('click', function () {
        var group = $(".sorted_gallery_table").sortable({
            containerSelector: 'table',
            itemPath: '> tbody',
            itemSelector: 'tr',
            placeholder: '<tr class="placeholder"/>',
            delay: 500,
            onDrop: function ($item, container, _super) {
                var data = group.sortable("serialize").get();
                var jsonString = JSON.stringify(data, null, ' ');
                _super($item, container);
                $.post(baseurl + 'dashboard/website/sortgallery', {sort: jsonString}, function () {
                });
                $('#serialize_output').html(jsonString);
            }
        });
    });

    /************************************************/
    /******** Sorting Slider Table ******************/
    /************************************************/
    $('.sorted_slider_table').on('click', function () {
        var group = $(".sorted_slider_table").sortable({
            containerSelector: 'table',
            itemPath: '> tbody',
            itemSelector: 'tr',
            placeholder: '<tr class="placeholder"/>',
            delay: 500,
            onDrop: function ($item, container, _super) {
                var data = group.sortable("serialize").get();
                var jsonString = JSON.stringify(data, null, ' ');
                _super($item, container);
                $.post(baseurl + 'dashboard/website/slidersort', {sort: jsonString}, function () {
                });
                $('#serialize_output').html(jsonString);
            }
        });
    });

/******************************************************************************/
/******************************************************************************/
/******************************************************************************/


    /************************************************/
    /******** Default Image Preview *****************/
    /************************************************/
    function previewFile() {
        var preview = document.querySelector('img#image');
        var file = document.querySelector('input[type=file]').files[0];
        var reader = new FileReader();

        reader.addEventListener("load", function () {
            preview.src = reader.result;
        }, false);

        if (file) {
            reader.readAsDataURL(file);
        }

        reader.onload = (function () {

            // Destroy cropper
            $('#image').cropper('destroy');

            // Replace url
            preview.src = reader.result;

            $('#image').cropper({
                aspectRatio: 1 / 1,
                viewMode: 3,
                dragMode: 'move',
                crop: function (e) {
                    // Output the result data for cropping image.
                    $("input#x").val(e.x);
                    $("input#y").val(e.y);
                    $("input#width").val(e.width);
                    $("input#height").val(e.height);
                }
            });

            var x = $("input#x").val();
            console.log(x);

        });
    }

    /************************************************/
    /******** Event Banner Image Cropper **********/
    /************************************************/
    function eventFeaturePhoto() {
        var preview = document.querySelector('img#image');
        var file = document.querySelector('input[type=file]').files[0];
        var reader = new FileReader();

        reader.addEventListener("load", function () {
            preview.src = reader.result;
        }, false);

        if (file) {
            reader.readAsDataURL(file);
        }

        reader.onload = (function () {

            // Destroy cropper
            $('#image').cropper('destroy');

            // Replace url
            preview.src = reader.result;

            $('#image').cropper({
                aspectRatio: 16 / 9,
                viewMode: 3,
                dragMode: 'move',
                crop: function (e) {
                    // Output the result data for cropping image.
                    $("input#x").val(e.x);
                    $("input#y").val(e.y);
                    $("input#width").val(e.width);
                    $("input#height").val(e.height);
                }
            });

            var x = $("input#x").val();
            console.log(x);

        });
    }
    
    
    /************************************************/
    /******** Sermon Banner Image Cropper **********/
    /************************************************/
    function sermonFeaturePhoto() {
        var preview = document.querySelector('img#image');
        var file = document.querySelector('input[type=file]').files[0];
        var reader = new FileReader();

        reader.addEventListener("load", function () {
            preview.src = reader.result;
        }, false);

        if (file) {
            reader.readAsDataURL(file);
        }

        reader.onload = (function () {

            // Destroy cropper
            $('#image').cropper('destroy');

            // Replace url
            preview.src = reader.result;

            $('#image').cropper({
                aspectRatio: 16 / 9,
                viewMode: 3,
                dragMode: 'move',
                crop: function (e) {
                    // Output the result data for cropping image.
                    $("input#x").val(e.x);
                    $("input#y").val(e.y);
                    $("input#width").val(e.width);
                    $("input#height").val(e.height);
                }
            });

            var x = $("input#x").val();
            console.log(x);

        });
    }
    
    /************************************************/
    /******** Seminar Banner Image Cropper **********/
    /************************************************/
    function seminarbanner() {
        var preview = document.querySelector('img#image');
        var file = document.querySelector('input[type=file]').files[0];
        var reader = new FileReader();

        reader.addEventListener("load", function () {
            preview.src = reader.result;
        }, false);

        if (file) {
            reader.readAsDataURL(file);
        }

        reader.onload = (function () {

            // Destroy cropper
            $('#image').cropper('destroy');

            // Replace url
            preview.src = reader.result;

            $('#image').cropper({
                //aspectRatio: 16 / 9,
                viewMode: 3,
                dragMode: 'move',
                crop: function (e) {
                    // Output the result data for cropping image.
                    $("input#x").val(e.x);
                    $("input#y").val(e.y);
                    $("input#width").val(e.width);
                    $("input#height").val(e.height);
                }
            });

            var x = $("input#x").val();
            console.log(x);

        });
    }

    /************************************************/
    /******** Section Banner Image Cropper **********/
    /************************************************/
    function sectionbanner() {
        var preview = document.querySelector('img#image');
        var file = document.querySelector('input[type=file]').files[0];
        var reader = new FileReader();

        reader.addEventListener("load", function () {
            preview.src = reader.result;
        }, false);

        if (file) {
            reader.readAsDataURL(file);
        }

        reader.onload = (function () {

            // Destroy cropper
            $('#image').cropper('destroy');

            // Replace url
            preview.src = reader.result;

            $('#image').cropper({
                aspectRatio: 16 / 9,
                viewMode: 3,
                dragMode: 'move',
                crop: function (e) {
                    // Output the result data for cropping image.
                    $("input#x").val(e.x);
                    $("input#y").val(e.y);
                    $("input#width").val(e.width);
                    $("input#height").val(e.height);
                }
            });

            var x = $("input#x").val();
            console.log(x);

        });
    }

    /************************************************/
    /******** Slider Image Cropper **********/
    /************************************************/
    function sliderbanner() {
        var preview = document.querySelector('img#image');
        var file = document.querySelector('input[type=file]').files[0];
        var reader = new FileReader();

        reader.addEventListener("load", function () {
            preview.src = reader.result;
        }, false);

        if (file) {
            reader.readAsDataURL(file);
        }

        reader.onload = (function () {

            // Destroy cropper
            $('#image').cropper('destroy');

            // Replace url
            preview.src = reader.result;

            $('#image').cropper({
                aspectRatio: 16 / 9,
                viewMode: 3,
                dragMode: 'move',
                crop: function (e) {
                    // Output the result data for cropping image.
                    $("input#x").val(e.x);
                    $("input#y").val(e.y);
                    $("input#width").val(e.width);
                    $("input#height").val(e.height);
                }
            });

            var x = $("input#x").val();
            console.log(x);

        });
    }

    /************************************************/
    /******** Gallery Image Cropper **********/
    /************************************************/
    function gallerybanner() {
        var preview = document.querySelector('img#image');
        var file = document.querySelector('input[type=file]').files[0];
        var reader = new FileReader();

        reader.addEventListener("load", function () {
            preview.src = reader.result;
        }, false);

        if (file) {
            reader.readAsDataURL(file);
        }

        reader.onload = (function () {

            // Destroy cropper
            $('#image').cropper('destroy');

            // Replace url
            preview.src = reader.result;

            $('#image').cropper({
                aspectRatio: 16 / 9,
                viewMode: 3,
                dragMode: 'move',
                crop: function (e) {
                    // Output the result data for cropping image.
                    $("input#x").val(e.x);
                    $("input#y").val(e.y);
                    $("input#width").val(e.width);
                    $("input#height").val(e.height);
                }
            });

            var x = $("input#x").val();
            console.log(x);

        });
    }
</script>

<?php
$inquiry_poll_role = $this->session->userdata('user_position');
if ($inquiry_poll_role === 'Super Admin') {
    $inquiry_poll_role = 'Admin';
}
if (in_array($inquiry_poll_role, array('Admin', 'Manager', 'Staff'), TRUE)) {
?>
<script>window.INQUIRY_POLL_URL = <?php echo json_encode(base_url('dashboard/inquiry/poll')); ?>;</script>
<script src="<?php echo base_url(); ?>js/inquiry-poll.js?v=<?php echo time(); ?>"></script>
<?php } ?>

</body>
</html>
