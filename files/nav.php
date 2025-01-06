<?php
require_once '../db/util.php';
require_once '../db/pdo.php';
session_start();
?>
<header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="../assets/img/logoK.png" alt="">
        <h1 class="sitename">KarateMoris</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php" class="active">Home</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="index.html#features">Challenges</a></li>
          <li><a href="index.html#services">Services</a></li>
          <li><a href="index.html#pricing">Pricing</a></li>
          <li class="dropdown"><a href="#"><span>Community</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
            <?php
                if(isset($_SESSION['user_id'])){
              echo '<li><a href="posttestimonial.php">Post Testimonial</a></li>';
               
             echo '<li><a href="posthealthissues.php">Post Health Issues</a></li>';
            }
            ?>
            <?php
            if(isset($_SESSION['master_id'])){
             echo '<li><a href="viewhealthissues.php"> Health Issues</a></li>';
            }
            ?>
              <li class="dropdown"><a href="#"><span>Media & Resources</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              
                <ul>
                <?php
      if(isset($_SESSION['master_id'])){
        echo  '<li><a href="postvideo.php">Manage Video</a></li>';
        echo '<li><a href="postnotes.php">Manage Notes</a></li>';
        echo '<li><a href="postimages.php">Manage Gallery</a></li>';
        echo '<li><a href="postannouncement.php">Manage Announcemnt</a></li>';
      }
      ?>
      <?php
      if(isset($_SESSION['user_id'])){
        echo '<li><a href="viewvideo.php">Video</a></li>'; 
        echo  '<li><a href="viewnotes.php">Notes</a></li>';
        echo  '<li><a href="gallery.php">Gallery</a></li>';
      }
      ?>
                </ul>
              </li>
              <li class="dropdown"><a href="#"><span>Classes & Events</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                <ul>
                <?php
                if(isset($_SESSION['master_id'])){
                  echo  '<li><a href="postcompetition.php">Manage Competition</a></li>';
                  echo  '<li><a href="dojo.php">Manage Dojo</a></li>';
                  echo  '<li><a href="#">Manage Class Schedule</a></li>';
                  // echo  '<li><a href="#">Manage Booking</a></li>';
                echo '<li><a href="postmembershipcat.php">Manage membership</a></li>'; 
                }
                ?>
                 <?php
                if(isset($_SESSION['user_id'])){
                  echo  '<li><a href="viewdojo.php">Dojo</a></li>';
                  echo  '<li><a href="viewcompetition.php">Competition</a></li>';
                  echo  '<li><a href="manage_booking.php">Your booking</a></li>';
                }

                ?>
                </ul>
              </li>
             <!--  <li><a href="#">Dropdown 2</a></li>
              <li><a href="#">Dropdown 3</a></li>
              <li><a href="#">Dropdown 4</a></li> -->
            </ul>
          </li>
          
          <li><a href="contact.php">Contact</a></li><br>

          <li class="dropdown">
    <a href="#"><span>Master</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
    <ul>
        <?php
        if (isset($_SESSION['master_id'])) {
            // Master user is logged in
            echo '<li><a href="logout.php">Logout</a></li>';
            echo '<li><a href="masterprofile.php">Profile</a></li>';
        } else {
            // Master user is not logged in
            echo '<a class="btn btn-primary" href="mastersignup.php">Sign up</a>';
            echo '<a class="btn btn-success" href="masterlogin.php">Login</a>';
        }
        ?>
    </ul>
</li>

        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

     <!-- <a class="btn btn-primary" href="signup.php">Sign up</a>
      <a class="btn btn-success" href="login.php">Login</a> -->

      <?php
      if(isset($_SESSION['user_id'])){
        // User is logged in
        echo '<a class="btn btn-danger" href="logout.php">Logout</a>';
        echo '<a class="btn btn-success" href="userprofile.php">Profile</a>';
     
      
    } else {
      // User is not logged in
      echo '<a class="btn btn-primary" href="signup.php">Sign up</a>';
      echo '<a class="btn btn-success" href="login.php">Login</a>';

    }
      ?>
      
      

    </div>
  </header>