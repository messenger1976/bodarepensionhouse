    <!-- Page Header Start -->
    <?php
        // Get the Contact Us menu image
        $this->db->where('menuname', 'Contact Us');
        $menu = $this->db->get('menu')->row();
        $bgImage = (isset($menu) && !empty($menu->menuimage)) 
            ? base_url() . 'images/website/menu/' . $menu->menuimage 
            : base_url() . 'themes/bodare/website/assets/img/default-bg.jpg';
    ?>
    <div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: linear-gradient(to right, rgb(2, 36, 91) 0%, rgba(2, 36, 91, 0) 100%), url(<?php echo $bgImage; ?>) center center no-repeat">
        <div class="container py-5">
            <h1 class="display-3 text-white animated slideInRight">Contact Us</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb animated slideInRight mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header End -->

    <?php
        $mapQuery = 'BODARE MPC & Community Bldg, J.A. Clarin St., Dao District, Tagbilaran City, Bohol, Philippines 6300';
        $mapEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($mapQuery) . '&output=embed';
    ?>
    <div class="animate-in cs_sections map" data-anim-type="bounce-in-up-large"  data-anim-delay="600"  >
        <iframe
            width="100%"
            height="700"
            frameborder="0" style="border:0; pointer-events: none;"
            src="<?php echo $mapEmbedUrl; ?>" allowfullscreen>
        </iframe>
    </div>

    <!-- Contact Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <p class="fw-medium text-uppercase text-primary mb-2">Contact Us</p>
                <h1 class="display-5 mb-5">Get In Touch With Us</h1>
            </div>
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="d-flex flex-column h-100">
                        <div class="mb-4">
                            <h3 class="mb-3">Contact Information</h3>
                            <p class="mb-4">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                        <i class="fa fa-map-marker-alt text-white"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0">Our Location</h5>
                                        <span><?php echo getBasic()->address; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                        <i class="fa fa-phone-alt text-white"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0">Call Us</h5>
                                        <span><?php echo getBasic()->contact; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                        <i class="fa fa-envelope-open text-white"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0">Email Us</h5>
                                        <span><?php echo getBasic()->email; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                    <?php
                    $contact_success = $this->session->flashdata('contact_success');
                    $contact_error = $this->session->flashdata('contact_error');
                    if ($contact_success) {
                    ?>
                        <div class="alert alert-success" role="alert" style="margin-bottom:20px;">
                            <?php echo $contact_success; ?>
                        </div>
                    <?php } ?>
                    <?php if ($contact_error) { ?>
                        <div class="alert alert-danger" role="alert" style="margin-bottom:20px;">
                            <?php echo $contact_error; ?>
                        </div>
                    <?php } ?>
                    <form id="contactform" action="<?php echo base_url();?>home/home/contactWithUs" method="post">
                        <input type="hidden" name="redirect_to" value="home/contact">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required maxlength="150">
                                    <label for="name">Your Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Your Email" required maxlength="255">
                                    <label for="email">Your Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required maxlength="255">
                                    <label for="subject">Subject</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Leave a message here" id="body" name="body" style="height: 150px" required maxlength="5000"></textarea>
                                    <label for="body">Message</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary py-3 px-5" type="submit" id="submit">
                                    <i class="fa fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->

