<!-- Carousel Start -->
    <div class="container-fluid px-0 mb-5">
        <div id="header-carousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">



                

                <?php $i=0; foreach ($slider as $slide) { $i++;?>     
                    <div class="carousel-item <?php if($i == 1){echo "active";} ?>">
                        <img class="w-100" src="<?php echo base_url();?>images/website/slider/resize/<?php echo $slide->filename; ?>" alt="Image">
                        <div class="carousel-caption">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-lg-10 text-start">
                                        <p class="fs-5 fw-medium text-primary text-uppercase animated slideInRight"><?php echo $slide->subtitle; ?></p>
                                        <h1 class="display-1 text-white mb-5 animated slideInRight"><?php echo $slide->content; ?></h1>
                                        <?php
                                        if(!empty($slide->button_text)){
                                        ?>
                                            <a href="<?php echo $slide->button_link; ?>" class="btn btn-primary py-3 px-5 animated slideInRight"><?php echo $slide->button_text; ?></a>
                                            
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                     
                <?php } ?>

                


                
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#header-carousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <!-- Carousel End -->

    <!-- About Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="row gx-3 h-100">
                        <div class="col-6 align-self-start wow fadeInUp" data-wow-delay="0.1s">
                            <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/about-1.jpg">
                        </div>
                        <div class="col-6 align-self-end wow fadeInDown" data-wow-delay="0.1s">
                            <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/about-2.jpg">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                    <p class="fw-medium text-uppercase text-primary mb-2">About Us</p>
                    <h1 class="display-5 mb-4">Brief History</h1>
                    <p class="mb-4">The Bohol DAR Employee & Community Multi - Purpose Cooperative is a duly registered cooperative initially organized in 1991. It was constituted by a group of seventy-five(75) determined DAR employees whose main purpose was to give financial assistance to its DAR members.</p>

                    <p class="mb-4">The Cooperative started with a paid- up capital of only Php 12,840.00. This was initially extended to the members in the forms of loans with an interest rate of 5% (now increased to 16%). Through prudent management. BODARE has long been experiencing a net surplus from its lending operations.
                    </p>
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0 bg-primary p-5">
                            <h1 class="display-2">34</h1>
                            <h5 class="text-white">Years of</h5>
                            <h5 class="text-white">Existence</h5>
                        </div>
                        <div class="ms-4">
                            <h3 class="mb-4">Goals and Objectives</h3>
                            <p><i class="fa fa-check text-primary me-2"></i>To implement credit assistance with reasonable interest , mortuary and health programs, time and savings deposits and other services.</p>
                            <p><i class="fa fa-check text-primary me-2"></i>To provide trainings and information related to officers , staff and members development .</p>
                            <p class="mb-0"><i class="fa fa-check text-primary me-2"></i>To access technical and financial assistance to concerned stockholders</p>
                            
                        </div>
                    </div>
                    <div class="row pt-2">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                    <i class="fa fa-envelope-open text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-2">Email us</p>
                                    <h5 class="mb-0">bodarempc@yahoo.com</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                    <i class="fa fa-phone-alt text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-2">Call us</p>
                                    <h5 class="mb-0">038-422-8034</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Facts Start -->
    <div class="container-fluid facts my-5 p-5">
        <div class="row g-5">
            <div class="col-md-6 col-xl-3 wow fadeIn" data-wow-delay="0.1s">
                <div class="text-center border p-5">
                    <i class="fa fa-certificate fa-3x text-white mb-3"></i>
                    <h1 class="display-2 text-primary mb-0" data-toggle="counter-up">34</h1>
                    <span class="fs-5 fw-semi-bold text-white">Years Existence</span>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 wow fadeIn" data-wow-delay="0.3s">
                <div class="text-center border p-5">
                    <i class="fa fa-users-cog fa-3x text-white mb-3"></i>
                    <h1 class="display-2 text-primary mb-0" data-toggle="counter-up">135</h1>
                    <span class="fs-5 fw-semi-bold text-white">Staff Members</span>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 wow fadeIn" data-wow-delay="0.5s">
                <div class="text-center border p-5">
                    <i class="fa fa-users fa-3x text-white mb-3"></i>
                    <h1 class="display-2 text-primary mb-0" data-toggle="counter-up">1228</h1>
                    <span class="fs-5 fw-semi-bold text-white">Happy Members</span>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 wow fadeIn" data-wow-delay="0.7s">
                <div class="text-center border p-5">
                    <i class="fa fa-check-double fa-3x text-white mb-3"></i>
                    <h1 class="display-2 text-primary mb-0">136M</h1>
                    <span class="fs-5 fw-semi-bold text-white">Assets</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Facts End -->

        <!-- Features Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="position-relative me-lg-4">
                        <img class="img-fluid w-100" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/feature.jpg" alt="">
                        <span
                            class="position-absolute top-50 start-100 translate-middle bg-white rounded-circle d-none d-lg-block"
                            style="width: 120px; height: 120px;"></span>
                        <button type="button" class="btn-play" data-bs-toggle="modal"
                            data-src="https://www.youtube.com/embed/DWRcNpR6Kdc" data-bs-target="#videoModal">
                            <span></span>
                        </button>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                    <p class="fw-medium text-uppercase text-primary mb-2">Why Choosing Us!</p>
                    <h1 class="display-5 mb-4">Few Reasons Why People Choosing Us!</h1>
                    <p class="mb-4">These are the values that define us. They are the heart of our cooperatives culture and the principles we live by, guiding us in everything we do.
                    </p>
                    <div class="row gy-4">
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                    <i class="fa fa-check text-white"></i>
                                </div>
                                <div class="ms-4">
                                    <h4>Committed</h4>
                                    <span>To the ongoing education of its members about the cooperative principles and values. Seeks ways to improve its services and operations to better meet the evolving needs of its members.</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                    <i class="fa fa-check text-white"></i>
                                </div>
                                <div class="ms-4">
                                    <h4>Alliance</h4>
                                    <span>Committed to the well-being of its broader community. It works for the sustainable development of its members' communities through shared initiatives and policies.</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                    <i class="fa fa-check text-white"></i>
                                </div>
                                <div class="ms-4">
                                    <h4>Reliable</h4>
                                    <span>Built on trust and transparency, our purpose is to serve you, our members, consistently and for the long term.</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                    <i class="fa fa-check text-white"></i>
                                </div>
                                <div class="ms-4">
                                    <h4>Excellent</h4>
                                    <span>It means going beyond what's expected to create exceptional value for our members and community.</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                    <i class="fa fa-check text-white"></i>
                                </div>
                                <div class="ms-4">
                                    <h4>Service Oriented</h4>
                                    <span>Where members are the users of the service and share in the benefits.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Features End -->


    <!-- Video Modal Start -->
    <div class="modal modal-video fade" id="videoModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-0">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Youtube Video</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- 16:9 aspect ratio -->
                    <div class="ratio ratio-16x9">
                        <iframe class="embed-responsive-item" src="" id="video" allowfullscreen
                            allowscriptaccess="always" allow="autoplay"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Video Modal End -->


    <!-- Service Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <style>
                .service-item {
                    margin: 105px 0 35px 0;
                }

                .service-item .service-img {
                    width: 210px;
                    height: 210px;
                    top: -105px;
                    padding: 16px;
                }

                .service-item .service-img img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .service-item .service-title,
                .service-item .service-text {
                    padding: 110px 30px 25px 30px;
                }

                .service-item .btn {
                    bottom: -28px;
                }
            </style>
            <div class="text-center mx-auto pb-4 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <!--<p class="fw-medium text-uppercase text-primary mb-2">Our Products & Services</p>-->
                <h1 class="display-5 mb-4">Our Products & Services</h1>
            </div>
            <div class="row gy-5 gx-4">
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="service-item">
                        <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-1.jpg" alt="">
                        <div class="service-img">
                            <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-1.jpg" alt="">
                        </div>
                        <div class="service-detail">
                            <div class="service-title">
                                <hr class="w-25">
                                <h3 class="mb-0">Lending Operations</h3>
                                <hr class="w-25">
                            </div>
                            <div class="service-text">
                                <p class="text-white mb-0">Salary, Petty Cash, Motor Vehicle Loans, Appliance, Business, Bonus, PO’s Grocery and Dept.</p>
                            </div>
                        </div>
                        <a class="btn btn-light" href="#">Read More</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="service-item">
                        <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-2.jpg" alt="">
                        <div class="service-img">
                            <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-2.jpg" alt="">
                        </div>
                        <div class="service-detail">
                            <div class="service-title">
                                <hr class="w-25">
                                <h3 class="mb-0">Building Rental</h3>
                                <hr class="w-25">
                            </div>
                            <div class="service-text">
                                <p class="text-white mb-0">This expansive office suite offers a premier location and a functional layout designed for productivity. The space includes private offices, a large central conference room with a projector, and a welcoming reception area for clients. It provides commanding views and an abundance of natural light. The building features 24/7 security, high-speed fiber optic internet, and a fully equipped kitchen area for tenant use</p>
                            </div>
                        </div>
                        <a class="btn btn-light" href="#">Read More</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="service-item">
                        <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-3.jpg" alt="">
                        <div class="service-img">
                            <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-3.jpg" alt="">
                        </div>
                        <div class="service-detail">
                            <div class="service-title">
                                <hr class="w-25">
                                <h3 class="mb-0">Savings and Time Deposits</h3>
                                <hr class="w-25">
                            </div>
                            <div class="service-text">
                                <p class="text-white mb-0">Your future is worth investing in. Open a savings account with us today and watch your money grow. It's safe, simple, and the smart choice for a secure tomorrow.</p>
                            </div>
                        </div>
                        <a class="btn btn-light" href="#">Read More</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="service-item">
                        <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-4.jpg" alt="">
                        <div class="service-img">
                            <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-4.jpg" alt="">
                        </div>
                        <div class="service-detail">
                            <div class="service-title">
                                <hr class="w-25">
                                <h3 class="mb-0">Mortuary Aid</h3>
                                <hr class="w-25">
                            </div>
                            <div class="service-text">
                                <p class="text-white mb-0">Relieve the financial and emotional stress of an unexpected loss.</p>
                            </div>
                        </div>
                        <a class="btn btn-light" href="#">Read More</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="service-item">
                        <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-5.jpg" alt="">
                        <div class="service-img">
                            <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-5.jpg" alt="">
                        </div>
                        <div class="service-detail">
                            <div class="service-title">
                                <hr class="w-25">
                                <h3 class="mb-0">BODARE Coop Pension House</h3>
                                <hr class="w-25">
                            </div>
                            <div class="service-text">
                                <p class="text-white mb-0">Why pay more for a fancy room you'll barely use? At BODARE Pension House, we give you everything you need for a comfortable stay at a fraction of the cost. Clean rooms, friendly service, and a great location. Book now and start your adventure!</p>
                            </div>
                        </div>
                        <a class="btn btn-light" href="#">Read More</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="service-item">
                        <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-6.jpg" alt="">
                        <div class="service-img">
                            <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/service-6.jpg" alt="">
                        </div>
                        <div class="service-detail">
                            <div class="service-title">
                                <hr class="w-25">
                                <h3 class="mb-0">BODARE Crown Residences</h3>
                                <hr class="w-25">
                            </div>
                            <div class="service-text">
                                <p class="text-white mb-0">Coming Soon...</p>
                            </div>
                        </div>
                        <a class="btn btn-light" href="#">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Service End -->



    <!-- Project Start -->
    <div class="container-fluid bg-dark pt-5 my-5 px-0">
        <div class="text-center mx-auto mt-5 wow fadeIn" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="fw-medium text-uppercase text-primary mb-2">Our Gallery</p>
            <h1 class="display-5 text-white mb-5">See What We Have Recently</h1>
        </div>
        <div class="owl-carousel project-carousel wow fadeIn" data-wow-delay="0.1s">
            <?php foreach ($gallery as $gallery) { ?>
                <a class="project-item" href="">
                    <img class="img-fluid" src="<?php echo base_url(); ?>images/website/gallery/small/<?php echo $gallery->filename; ?>" alt="<?php echo $gallery->filename; ?>" data-image="<?php echo base_url(); ?>images/website/gallery/large/<?php echo $gallery->filename; ?>"
                        data-description="<?php echo $gallery->filename; ?>">
                    <div class="project-title">
                        <h5 class="text-primary mb-0"><?php echo $gallery->title; ?></h5>
                    </div>
                </a>

                
            <?php } ?>

            
        </div>
    </div>
    <!-- Project End -->

<!-- Cooperative Officers Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-12 wow fadeIn" data-wow-delay="0.5s">
                    <p class="fw-medium text-uppercase text-primary mb-2">Our Leadership</p>
                    <h1 class="display-5 mb-4">Cooperative Officers</h1>
                    <p class="mb-4">The Cooperative Officers of the Bohol DAR Employee & Community Multi-Purpose Cooperative is composed of dedicated leaders committed to the organization's mission and the welfare of its members.</p>
                </div>
            </div>

            <!-- Cooperative Officers Grouped by Department -->
            <?php foreach ($cooperative_officers as $department => $members): ?>
                <div class="mt-5">
                    <h3 class="text-primary mb-4 border-bottom pb-3">
                        <i class="bi bi-diagram-3"></i> <?php echo ucwords($department); ?>
                    </h3>
                    <div class="row g-4">
                        <?php foreach ($members as $member): ?>
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                                <div class="card">
                                    <img class="card-img-top" src="<?php echo base_url(); ?>images/<?php if($member->profileimage){ echo "cooperative_officers/profile/" . $member->profileimage; }else{ echo "avatar.png"; } ?>" alt="<?php echo $member->fname . " " . $member->lname; ?>">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-primary"><?php echo $member->position; ?></h5>
                                        <h4 class="card-title mb-3"><?php echo $member->fname . " " . $member->lname; ?></h4>
                                        <p class="card-text"><?php echo word_limiter(strip_tags($member->speech), 20); ?></p>
                                        <a href="<?php echo base_url(); ?>home/cooperative_officers/view/<?php echo $member->cooperative_officersid; ?>" class="btn btn-primary btn-sm">View Profile</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <div class="row mt-5">
                <div class="col-12">
                    <?php echo $pagination; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Cooperative Officers End -->
    
<?php 
if(0) {
foreach ($section as $section) { ?>


<!-- Staff Start -->
 <?php if ($section->background) {
            echo '<div class="container-fluid bg-dark pt-5 my-5 px-0">';
        }else{
            echo '<div class="container-xxl py-5">';
        }
?>
    
        <div class="container">
            <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <p class="fw-medium text-uppercase text-primary mb-2"><?php echo $section->title; ?></p>
                <h1 class="display-5 mb-5"><?php echo $section->subtitle; ?></h1>
            </div>
            <div class="row g-4">
                <?php
                    
                    if ($section->shortcode) {
                        $SCAttArray = explode(",", $section->shortcode);
                        echo shortCode($SCAttArray[0], $SCAttArray[1], $SCAttArray[2], $SCAttArray[3], $SCAttArray[4]);
                    }
                    ?>
                                 
                        <p><?php echo $section->content; ?></p>
                        <?php if ($section->link) { ?>
                        <div class="service-item">
                            <a class="btn btn-light" href="<?php echo $section->link; ?>"><?php echo $section->btntext; ?></a>
                        </div>
                        <?php } ?>
                       
                
                
            </div>
        </div>
    </div>
    <!-- Team End -->


        <!--<div class="animate-in cs_sections <?php
        if ($section->background) {
            echo "parallax";
        }
        ?>" data-parallax="scroll" data-image-src="images/section/crop/<?php echo $section->background; ?>" data-anim-type="bounce-in-up-large"  data-anim-delay="600">
            <div class="content">
                <div class="container">
                    <h2><?php echo $section->title; ?></h2>
                    <div class="separator-container">
                        <div class="separator line-separator">♦</div>
                    </div>

                    <?php
                    
                    if ($section->shortcode) {
                        $SCAttArray = explode(",", $section->shortcode);
                        echo shortCode($SCAttArray[0], $SCAttArray[1], $SCAttArray[2], $SCAttArray[3], $SCAttArray[4]);
                    }
                    ?>

                    <div class="col-md-offset-1 col-lg-10 col-md-10 col-sm-12 col-xs-12">                        
                        <p><?php echo $section->content; ?></p>
                        <?php if ($section->link) { ?>
                        <div class="col-lg-offset-5 col-md-offset-5 col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <a class="read_more" href="<?php echo $section->link; ?>"><?php echo $section->btntext; ?></a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } }?>-->

    <!-- Staff Start -->
    <!--<div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <p class="fw-medium text-uppercase text-primary mb-2">Our Staff</p>
                <h1 class="display-5 mb-5">Dedicated Staff Members</h1>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item">
                        <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/team-1.jpg" alt="">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-square bg-primary" style="width: 90px; height: 90px;">
                                <i class="fa fa-2x fa-share text-white"></i>
                            </div>
                            <div class="position-relative overflow-hidden bg-light d-flex flex-column justify-content-center w-100 ps-4"
                                style="height: 90px;">
                                <h5>Rob Miller</h5>
                                <span class="text-primary">CEO & Founder</span>
                                <div class="team-social">
                                    <a class="btn btn-square btn-dark rounded-circle mx-1" href=""><i
                                            class="fab fa-facebook-f"></i></a>
                                    <a class="btn btn-square btn-dark rounded-circle mx-1" href=""><i
                                            class="fab fa-twitter"></i></a>
                                    <a class="btn btn-square btn-dark rounded-circle mx-1" href=""><i
                                            class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item">
                        <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/team-2.jpg" alt="">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-square bg-primary" style="width: 90px; height: 90px;">
                                <i class="fa fa-2x fa-share text-white"></i>
                            </div>
                            <div class="position-relative overflow-hidden bg-light d-flex flex-column justify-content-center w-100 ps-4"
                                style="height: 90px;">
                                <h5>Adam Crew</h5>
                                <span class="text-primary">Project Manager</span>
                                <div class="team-social">
                                    <a class="btn btn-square btn-dark rounded-circle mx-1" href=""><i
                                            class="fab fa-facebook-f"></i></a>
                                    <a class="btn btn-square btn-dark rounded-circle mx-1" href=""><i
                                            class="fab fa-twitter"></i></a>
                                    <a class="btn btn-square btn-dark rounded-circle mx-1" href=""><i
                                            class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item">
                        <img class="img-fluid" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/team-3.jpg" alt="">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-square bg-primary" style="width: 90px; height: 90px;">
                                <i class="fa fa-2x fa-share text-white"></i>
                            </div>
                            <div class="position-relative overflow-hidden bg-light d-flex flex-column justify-content-center w-100 ps-4"
                                style="height: 90px;">
                                <h5>Peter Farel</h5>
                                <span class="text-primary">Engineer</span>
                                <div class="team-social">
                                    <a class="btn btn-square btn-dark rounded-circle mx-1" href=""><i
                                            class="fab fa-facebook-f"></i></a>
                                    <a class="btn btn-square btn-dark rounded-circle mx-1" href=""><i
                                            class="fab fa-twitter"></i></a>
                                    <a class="btn btn-square btn-dark rounded-circle mx-1" href=""><i
                                            class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->
    <!-- Team End -->


    <!-- Testimonial Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                <p class="fw-medium text-uppercase text-primary mb-2">Testimonial</p>
                <h1 class="display-5 mb-5">What Our Members Say!</h1>
            </div>
            <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.1s">
                <div class="testimonial-item text-center">
                    <div class="testimonial-img position-relative">
                        <img class="img-fluid rounded-circle mx-auto mb-5" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/testimonial-1.jpg">
                        <div class="btn-square bg-primary rounded-circle">
                            <i class="fa fa-quote-left text-white"></i>
                        </div>
                    </div>
                    <div class="testimonial-text text-center rounded p-4">
                        <p>BODARE has truly transformed my financial life! As a DAR employee, I availed their salary loan program during a family emergency and the process was quick and hassle-free. The competitive interest rates and flexible payment terms made it easy for me to manage. I'm also impressed with their insurance and benefits programs. BODARE is more than just a cooperative—they're a family that cares!</p>
                        <h5 class="mb-1">Maria Santos</h5>
                        <span class="fst-italic">DAR Employee & Member</span>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <div class="testimonial-img position-relative">
                        <img class="img-fluid rounded-circle mx-auto mb-5" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/testimonial-2.jpg">
                        <div class="btn-square bg-primary rounded-circle">
                            <i class="fa fa-quote-left text-white"></i>
                        </div>
                    </div>
                    <div class="testimonial-text text-center rounded p-4">
                        <p>I've been with BODARE for 12 years and watched them grow into an outstanding cooperative. Their time and savings program helped me build my emergency fund, and their housing loan allowed me to build my dream home. The transparency in their operations and the integrity of their leadership gives me confidence. BODARE truly prioritizes member welfare above all else. Highly recommended!</p>
                        <h5 class="mb-1">Juan Dela Cruz</h5>
                        <span class="fst-italic">Member, 12 Years</span>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <div class="testimonial-img position-relative">
                        <img class="img-fluid rounded-circle mx-auto mb-5" src="<?php echo base_url(); ?>themes/bodare/website/assets/img/testimonial-3.jpg">
                        <div class="btn-square bg-primary rounded-circle">
                            <i class="fa fa-quote-left text-white"></i>
                        </div>
                    </div>
                    <div class="testimonial-text text-center rounded p-4">
                        <p>What I love most about BODARE is their commitment to the community and their members' growth. Their educational programs and seminars have helped me improve my skills and financial literacy. The cooperative officers are always approachable and genuinely interested in helping members achieve their goals. BODARE isn't just about loans—it's about building a stronger, more prosperous community together. I'm proud to be a member!</p>
                        <h5 class="mb-1">Anna Garcia</h5>
                        <span class="fst-italic">Community Member & Member</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Testimonial End -->









    
    

    <?php
        $homeMapQuery = 'BODARE MPC & Community Bldg, J.A. Clarin St., Dao District, Tagbilaran City, Bohol, Philippines 6300';
        $homeMapEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($homeMapQuery) . '&output=embed';
    ?>
    <div class="animate-in cs_sections map" data-anim-type="bounce-in-up-large"  data-anim-delay="600"  >
        <iframe
            width="100%"
            height="700"
            frameborder="0" style="border:0; pointer-events: none;"
            src="<?php echo $homeMapEmbedUrl; ?>" allowfullscreen>
        </iframe>
    </div>

    <!-- Contact Start -->
    <div id="contact-us" class="container-xxl py-5">
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
                    $contact_scroll = $this->session->flashdata('contact_scroll');
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
                    <form id="home-contactform" action="<?php echo base_url();?>home/home/contactWithUs" method="post">
                        <input type="hidden" name="redirect_to" value="home">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="home-contact-name" name="name" placeholder="Your Name" required maxlength="150">
                                    <label for="home-contact-name">Your Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="home-contact-email" name="email" placeholder="Your Email" required maxlength="255">
                                    <label for="home-contact-email">Your Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="home-contact-subject" name="subject" placeholder="Subject" required maxlength="255">
                                    <label for="home-contact-subject">Subject</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Leave a message here" id="home-contact-body" name="body" style="height: 150px" required maxlength="5000"></textarea>
                                    <label for="home-contact-body">Message</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary py-3 px-5" type="submit">
                                    <i class="fa fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if ($contact_scroll || $contact_success || $contact_error) { ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var section = document.getElementById('contact-us');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
    <?php } ?>
    <!-- Contact End -->