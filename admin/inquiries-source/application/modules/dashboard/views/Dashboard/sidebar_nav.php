<?php
if (!isset($itdash) || !isset($user_position) || !isset($ccontroller) || !isset($cmethod)) {
    $cmodule = isset($cmodule) ? $cmodule : $this->uri->segment(1);
    $ccontroller = isset($ccontroller) ? $ccontroller : $this->uri->segment(2);
    $cmethod = isset($cmethod) ? $cmethod : $this->uri->segment(3);
    if (!isset($itdash)) {
        $itdash = (!$ccontroller && $cmodule == 'dashboard') ? 'dashboard' : 'notdashboard';
    }
    if (!isset($user_position)) {
        $user_position = $this->session->userdata('user_position');
        if ($user_position == 'Super Admin') {
            $user_position = 'Admin';
        }
    }
}
?>
                <div class="sidebar-wrapper">
                    <ul class="nav">
                        <li class="<?php
                        if ($itdash == "dashboard") {
                            echo "active";
                        }
                        ?>">
                            <a href="<?php echo base_url('dashboard'); ?>">
                                <em class="icon ni ni-dashboard"></em>
                                <p><?php echo $this->lang->line('dash_menu_dash'); ?></p>
                            </a>
                        </li>

                        <?php if ($user_position == "Admin") { ?>

                            <li class="<?php
                            if ($ccontroller == "website" || $ccontroller == "page" || $ccontroller == "menu" || $ccontroller == "section") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-globe"></em>
                                    <p><?php echo $this->lang->line('dash_menu_website'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "website" || $ccontroller == "page" || $ccontroller == "section" || $ccontroller == "menu") {
                                    echo "active";
                                }
                                ?> nav_child">

                                    <li class="<?php
                                    if ($cmethod == "header") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/website/header'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_basic'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($ccontroller == "menu") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/menu'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_menus'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "slider" || $cmethod == "slideredit") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/website/slider'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_slider'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "gallery") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/website/gallery'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_gallery'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "emailsettings") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/website/emailsettings'); ?>">
                                            <p>Email/SMTP Settings</p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($ccontroller == "section") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/section'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_section'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($ccontroller == "page") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/page'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_page'); ?></p>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager", "Staff"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "event") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-calendar-booking-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_events'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "event") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "addevent") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/event/addevent'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_addevent'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allevents") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/event/allevents'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allevents'); ?></p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager", "Staff"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "notice") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-bookmark-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_notice'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "notice") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "addnotice") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/notice/addnotice'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_addnotice'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allnotices") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/notice/allnotices'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allnotices'); ?></p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager", "Staff"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "inquiry") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a href="<?php echo base_url('dashboard/inquiry/allinquiries'); ?>">
                                    <em class="icon ni ni-mail"></em>
                                    <p><?php echo $this->lang->line('dash_menu_inquiry'); ?>
                                        <?php
                                        $this->db->where_in('status', array('new', 'guest_replied'));
                                        $newInquiryCount = (int) $this->db->count_all_results('inquiry');
                                        ?>
                                        <span id="inquiry-menu-badge" class="badge inquiry-menu-badge" style="background:#e53935;color:#fff;border-radius:10px;padding:2px 7px;font-size:11px;<?php echo $newInquiryCount > 0 ? '' : 'display:none;'; ?>"><?php echo $newInquiryCount; ?></span>
                                    </p>
                                </a>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager", "Staff"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "speech") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-chat-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_speech'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "speech") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "addspeech") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/speech/addspeech'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_addspeech'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allspeech") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/speech/allspeech'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allspeech'); ?></p>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "department") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-layers-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_department'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "department") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "adddepartment") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/department/adddepartment'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_adddepartment'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "alldepartment") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/department/alldepartment'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_alldepartment'); ?></p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "committee") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-view-group-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_committee'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "committee") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "addcommittee") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/committee/addcommittee'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_addcommittee'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allcommittee") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/committee/allcommittee'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allcommittee'); ?></p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager", "Staff"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "member") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-users-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_members'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "member") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "addmember") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/member/addmember'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_addmembers'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allmembers") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/member/allmembers'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allmembers'); ?></p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "board_of_directors") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-building-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_board_of_directors'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "board_of_directors") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "addboard_of_directors") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/board_of_directors/addboard_of_directors'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_addboard_of_directors'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allBoard_of_directors") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/board_of_directors/allBoard_of_directors'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allboard_of_directors'); ?></p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "cooperative_officers") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-briefcase"></em>
                                    <p><?php echo $this->lang->line('dash_menu_cooperative_officers'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "cooperative_officers") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "addcooperative_officers") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/cooperative_officers/addcooperative_officers'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_addcooperative_officers'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allCooperative_officers") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/cooperative_officers/allCooperative_officers'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allcooperative_officers'); ?></p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <li class="<?php
                        if ($ccontroller == "dashboard" && $cmethod == "reports") {
                            echo "active";
                        }
                        ?>">
                            <a href="<?php echo base_url('dashboard/dashboard/reports'); ?>">
                                <em class="icon ni ni-growth-fill"></em>
                                <p>Analytics Reports</p>
                            </a>
                        </li>

                        <?php if (in_array($user_position, array("Admin", "Manager"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "staff") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-user-list-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_churchstaffs'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "staff") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "addstaff") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/staff/addstaff'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_addchurchstaff'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allstaffs") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/staff/allstaffs'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allchurchstaffs'); ?></p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <?php if ($user_position == "Admin") { ?>
                            <li class="<?php
                            if ($ccontroller == "user" || $ccontroller == "rolesetup") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-account-setting-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_users'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "user" || $ccontroller == "rolesetup") {
                                    echo "active";
                                }
                                ?> nav_child">

                                    <li class="<?php
                                    if ($cmethod == "adduser") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/user/adduser'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_adduser'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allusers") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/user/allusers'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allusers'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($ccontroller == "rolesetup") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/rolesetup'); ?>">
                                            <p>Roles Setup</p>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager", "Staff"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "seminar") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-award"></em>
                                    <p><?php echo $this->lang->line('dash_menu_seminars'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "seminar") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "addseminar") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/seminar/addseminar'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_addseminar'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allseminar") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/seminar/allseminar'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allseminars'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "allregistered") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/seminar/applicants'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_allapplicants'); ?></p>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                        <?php } ?>

                        <?php if (in_array($user_position, array("Admin", "Manager", "Staff"))) { ?>
                            <li class="<?php
                            if ($ccontroller == "attendance") {
                                echo "active";
                            }
                            ?> nav_parent">
                                <a>
                                    <em class="icon ni ni-check-circle-fill"></em>
                                    <p><?php echo $this->lang->line('dash_menu_attendance'); ?> <em class="icon ni ni-chevron-right menu-toggle-icon"></em></p>
                                </a>

                                <ul class="<?php
                                if ($ccontroller == "attendance") {
                                    echo "active";
                                }
                                ?> nav_child">
                                    <li class="<?php
                                    if ($cmethod == "attendancetype") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/attendance/addtype'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_attendancetype'); ?></p>
                                        </a>
                                    </li>

                                    <li class="<?php
                                    if ($cmethod == "attendance") {
                                        echo "active";
                                    }
                                    ?>">
                                        <a href="<?php echo base_url('dashboard/attendance'); ?>">
                                            <p><?php echo $this->lang->line('dash_menu_attendancebrowse'); ?></p>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                        <?php } ?>

                        <?php if ($user_position == "Admin") { ?>
                            <li class="<?php
                            if ($ccontroller == "import") {
                                echo "active";
                            }
                            ?>">
                                <a href="<?php echo base_url('dashboard/import'); ?>">
                                    <em class="icon ni ni-upload-cloud"></em>
                                    <p><?php echo $this->lang->line('dash_menu_import'); ?></p>
                                </a>
                            </li>
                        <?php } ?>

                        <?php if ($user_position == "Admin" || $user_position == "Super Admin") { ?>
                            <li class="<?php
                            if ($ccontroller == "setting" && $cmethod == "backup") {
                                echo "active";
                            }
                            ?>">
                                <a href="<?php echo base_url('dashboard/setting/backup'); ?>">
                                    <em class="icon ni ni-db-fill"></em>
                                    <p>Backup Settings</p>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
