<?php
require_once '../db/util.php';
require_once '../db/pdo.php';
session_start();

try {
  $sql = "SELECT * FROM testimonial t
          INNER JOIN user u ON t.user_id = u.user_id
          WHERE u.status = 1";
  $stmt = $pdo->query($sql);
  $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  die("Error fetching testimonials: " . $e->getMessage());
}
try{
  $sql2 = "SELECT * FROM announcement a 
          INNER JOIN master m ON a.master_id = m.master_id
          WHERE m.status = 1";
          $stmt = $pdo->query($sql2);
          $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  die("Error fetching announcements: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | KarateMoris</title>
    
    <?php include '../files/csslib.php' ?> <!-- including libraries -->

</head>
<body>

<!-- including header and banner -->
<?php include_once '../files/nav.php' ?>



<main class="main">

<?php include_once '../files/hero.php' ?>

    <!-- Featured Services Section -->
    <section id="featured-services" class="featured-services section light-background">

      <div class="container">

     <center><h2>Announcements</h2></center> 

        <div class="row gy-4">
        <?php foreach ($announcements as $announcement): ?>
          <div class="col-xl-4 col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item d-flex">
              <div class="icon flex-shrink-0"><img src="../client/uploads/<?php echo $announcement['image'] ?>" class="testimonial-img" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%;"></div>
              <div>
                <h4 class="title"><a href="#" class="stretched-link"><?= htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']) ?></a></h4>
                <p class="description"><?= htmlspecialchars($announcement['description']) ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <!-- End Service Item -->

          <!-- <div class="col-xl-4 col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item d-flex">
              <div class="icon flex-shrink-0"><i class="bi bi-card-checklist"></i></div>
              <div>
                <h4 class="title"><a href="#" class="stretched-link">Dolor Sitema</a></h4>
                <p class="description">Minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip exa</p>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-lg-6" data-aos="fade-up" data-aos-delay="300">
            <div class="service-item d-flex">
              <div class="icon flex-shrink-0"><i class="bi bi-bar-chart"></i></div>
              <div>
                <h4 class="title"><a href="#" class="stretched-link">Sed ut perspiciatis</a></h4>
                <p class="description">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum</p>
              </div>
            </div>
          </div> -->

        </div>

      </div>

    </section><!-- /Featured Services Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
            <p class="who-we-are">Who We Are</p>
            <h3>Build yourself</h3>
            <p class="fst-italic">
            Whether you're a beginner looking to build confidence, an advanced student aiming to refine your skills, 
            or a parent seeking a positive and structured activity for your child, you've come to the right place. Here at KarateMoris, 
            we foster an environment of respect, dedication, and empowerment through the art of karate.
            </p>
            <ul>
              <li><i class="bi bi-check-circle"></i> <span>Karate training enhances strength, flexibility, coordination, and endurance, leading to better overall health.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Karate encourages mindfulness, concentration, and discipline, helping practitioners stay focused and calm both in training and daily life.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Karate builds self-confidence by teaching practical self-defense techniques and boosting self-assurance in challenging situations.</span></li>
            </ul>
            <a href="story.php" class="read-more"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
          </div>

          <div class="col-lg-6 about-images" data-aos="fade-up" data-aos-delay="200">
            <div class="row gy-4">
              <div class="col-lg-6">
                <img src="../assets/img/about-company-1.jpg" class="img-fluid" alt="">
              </div>
              <div class="col-lg-6">
                <div class="row gy-4">
                  <div class="col-lg-12">
                    <img src="../../assets/img/about-company-3.jpg" class="img-fluid" alt="">
                  </div>
                  <div class="col-lg-8">
                    <img src="../../assets/img/about-company-2.jpg" class="img-fluid" alt="">
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>
    </section><!-- /About Section -->

    <!-- Clients Section -->

    <section id="clients" class="clients section">
    <div class="container" data-aos="fade-up">
        <div class="row gy-4">
            <?php 
            // Fetch approved and non-expired advertisements
            $stmt = $pdo->prepare("
                SELECT * FROM advertisement 
                WHERE status = 1 
                AND expire_date >= CURRENT_DATE
                ORDER BY date_confirm DESC
            ");
            $stmt->execute();
            $advertisements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($advertisements as $ad): 
                $logoPath = "../upload/" . htmlentities($ad['logopath']);
            ?>
                <div class="col-xl-2 col-md-3 col-6 client-logo">
                    <a href="<?= htmlentities($ad['websiteurl']) ?>" 
                       target="_blank" 
                       title="<?= htmlentities($ad['company_name']) ?>">
                        <img src="<?= $logoPath ?>" 
                             class="img-fluid" 
                             alt="<?= htmlentities($ad['alternatetext']) ?>">
                    </a>
                </div>
            <?php endforeach; 

            // Fill remaining slots with default logos if needed
            $remainingSlots = 6 - count($advertisements);
            for ($i = 0; $i < $remainingSlots; $i++): ?>
                <div class="col-xl-2 col-md-3 col-6 client-logo">
                    <img src="../upload-<?= ($i + 1) ?>.png" 
                         class="img-fluid" 
                         alt="Partner Logo">
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section> 

    <!-- Features Section -->
    <section id="features" class="features section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Founder of Kyokushin Kai Karate</h2>
        <p>Masutatsu Oyama</p>
      </div><!-- End Section Title -->

      <div class="container">
        <div class="row justify-content-between">

          <div class="col-lg-5 d-flex align-items-center">

            <ul class="nav nav-tabs" data-aos="fade-up" data-aos-delay="100">
              <li class="nav-item">
                <a class="nav-link active show" data-bs-toggle="tab" data-bs-target="#features-tab-1">
                  <i class="bi bi-heart"></i>
                  <div>
                    <h4 class="d-none d-lg-block">Sensei Oyama</h4>
                    <p>
                    Osu, Sensei Masutatsu Oyama.

With deep respect and gratitude, we honor the life and legacy of Sensei Oyama, a true pioneer and warrior of martial arts. His dedication, strength, and unwavering pursuit of the "ultimate truth" have inspired millions worldwide and shaped the spirit of Kyokushin Karate. His philosophy taught us that through discipline, hard work, and courage, we can overcome any challenge, both on and off the mat. 

Today, we bow in respect to his memory, honoring his vision and commitment to cultivating resilience, humility, and excellence. We carry forward his teachings with pride, embracing his path as we strive to better ourselves and seek our own ultimate truths. 

Osu!
                    </p>
                  </div>
                </a>
              </li>
              <!-- <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-2">
                  <i class="bi bi-box-seam"></i>
                  <div>
                    <h4 class="d-none d-lg-block">Unde praesenti mara setra le</h4>
                    <p>
                      Recusandae atque nihil. Delectus vitae non similique magnam molestiae sapiente similique
                      tenetur aut voluptates sed voluptas ipsum voluptas
                    </p>
                  </div>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-3">
                  <i class="bi bi-brightness-high"></i>
                  <div>
                    <h4 class="d-none d-lg-block">Pariatur explica nitro dela</h4>
                    <p>
                      Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum
                      Debitis nulla est maxime voluptas dolor aut
                    </p>
                  </div>
                </a>
              </li> -->
            </ul><!-- End Tab Nav -->

          </div>

          <div class="col-lg-6">

            <div class="tab-content" data-aos="fade-up" data-aos-delay="200">

              <div class="tab-pane fade active show" id="features-tab-1">
                <img src="../assets/img/tabs-1.jpg" alt="" class="img-fluid">
              </div><!-- End Tab Content Item -->

              <div class="tab-pane fade" id="features-tab-2">
                <img src="../assets/img/tabs-2.jpg" alt="" class="img-fluid">
              </div><!-- End Tab Content Item -->

              <div class="tab-pane fade" id="features-tab-3">
                <img src="../assets/img/tabs-3.jpg" alt="" class="img-fluid">
              </div><!-- End Tab Content Item -->
            </div>

          </div>

        </div>

      </div>

    </section><!-- /Features Section -->

     

    <!-- Services Section -->
    <section id="services" class="services section light-background">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Services</h2>
        <p>KarateMoris Services</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row g-5">

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item item-cyan position-relative">
            <i class="bi bi-broadcast icon"></i>
              
              <div>
                <h3>Efficient User Management</h3>
                <p>Provide smooth registration and login experiences for Admins, Masters, and Users, complete with secure authentication and profile customization options.</p>
                <a href="#" class="read-more stretched-link">Learn More <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item item-orange position-relative">
            <i class="bi bi-calendar4-week icon"></i>
              
              <div>
                <h3>Comprehensive Class Booking</h3>
                <p>Easily book time slots for classes and special events with a calendar-based interface, ensuring you never miss a training session.</p>
                <a href="#" class="read-more stretched-link">Learn More <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
            <div class="service-item item-teal position-relative">
              <i class="bi bi-easel icon"></i>
              <div>
                <h3>Resource Sharing & Management</h3>
                <p>Access and manage a rich library of videos, notes, and learning resources. Masters can upload materials, and users can easily view and download them.</p>
                <a href="#" class="read-more stretched-link">Learn More <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="400">
            <div class="service-item item-red position-relative">
              <i class="bi bi-bounding-box-circles icon"></i>
              <div>
                <h3>Health & Wellness Tracking</h3>
                <p>Users can communicate health issues, allowing Masters to adjust training accordingly, fostering a safe training environment.</p>
                <a href="#" class="read-more stretched-link">Learn More <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="500">
            <div class="service-item item-indigo position-relative">
            <i class="bi bi-activity icon"></i>
              <div>
                <h3>Performance Analytics</h3>
                <p>Access personalized performance analytics and tracking, enabling Admins and Masters to evaluate engagement and Users to monitor progress.</p>
                <a href="#" class="read-more stretched-link">Learn More <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="600">
            <div class="service-item item-pink position-relative">
              <i class="bi bi-chat-square-text icon"></i>
              <div>
                <h3>Community Engagement</h3>
                <p>Join forums, view announcements, and receive notifications. Stay connected with your community through events, newsletters, and daily challenges.</p>
                <a href="#" class="read-more stretched-link">Learn More <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div><!-- End Service Item -->

        </div>

      </div>

    </section><!-- /Services Section -->

    
    <!-- Pricing Section -->
    <!-- <section id="pricing" class="pricing section">

     
      <div class="container section-title" data-aos="fade-up">
        <h2>Pricing</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div>

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="pricing-item">
              <h3>Free Plan</h3>
              <p class="description">Ullam mollitia quasi nobis soluta in voluptatum et sint palora dex strater</p>
              <h4><sup>$</sup>0<span> / month</span></h4>
              <a href="#" class="cta-btn">Start a free trial</a>
              <p class="text-center small">No credit card required</p>
              <ul>
                <li><i class="bi bi-check"></i> <span>Quam adipiscing vitae proin</span></li>
                <li><i class="bi bi-check"></i> <span>Nec feugiat nisl pretium</span></li>
                <li><i class="bi bi-check"></i> <span>Nulla at volutpat diam uteera</span></li>
                <li class="na"><i class="bi bi-x"></i> <span>Pharetra massa massa ultricies</span></li>
                <li class="na"><i class="bi bi-x"></i> <span>Massa ultricies mi quis hendrerit</span></li>
                <li class="na"><i class="bi bi-x"></i> <span>Voluptate id voluptas qui sed aperiam rerum</span></li>
                <li class="na"><i class="bi bi-x"></i> <span>Iure nihil dolores recusandae odit voluptatibus</span></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="pricing-item featured">
              <p class="popular">Popular</p>
              <h3>Business Plan</h3>
              <p class="description">Ullam mollitia quasi nobis soluta in voluptatum et sint palora dex strater</p>
              <h4><sup>$</sup>29<span> / month</span></h4>
              <a href="#" class="cta-btn">Start a free trial</a>
              <p class="text-center small">No credit card required</p>
              <ul>
                <li><i class="bi bi-check"></i> <span>Quam adipiscing vitae proin</span></li>
                <li><i class="bi bi-check"></i> <span>Nec feugiat nisl pretium</span></li>
                <li><i class="bi bi-check"></i> <span>Nulla at volutpat diam uteera</span></li>
                <li><i class="bi bi-check"></i> <span>Pharetra massa massa ultricies</span></li>
                <li><i class="bi bi-check"></i> <span>Massa ultricies mi quis hendrerit</span></li>
                <li><i class="bi bi-check"></i> <span>Voluptate id voluptas qui sed aperiam rerum</span></li>
                <li class="na"><i class="bi bi-x"></i> <span>Iure nihil dolores recusandae odit voluptatibus</span></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="300">
            <div class="pricing-item">
              <h3>Developer Plan</h3>
              <p class="description">Ullam mollitia quasi nobis soluta in voluptatum et sint palora dex strater</p>
              <h4><sup>$</sup>49<span> / month</span></h4>
              <a href="#" class="cta-btn">Start a free trial</a>
              <p class="text-center small">No credit card required</p>
              <ul>
                <li><i class="bi bi-check"></i> <span>Quam adipiscing vitae proin</span></li>
                <li><i class="bi bi-check"></i> <span>Nec feugiat nisl pretium</span></li>
                <li><i class="bi bi-check"></i> <span>Nulla at volutpat diam uteera</span></li>
                <li><i class="bi bi-check"></i> <span>Pharetra massa massa ultricies</span></li>
                <li><i class="bi bi-check"></i> <span>Massa ultricies mi quis hendrerit</span></li>
                <li><i class="bi bi-check"></i> <span>Voluptate id voluptas qui sed aperiam rerum</span></li>
                <li><i class="bi bi-check"></i> <span>Iure nihil dolores recusandae odit voluptatibus</span></li>
              </ul>
            </div>
          </div> --><!-- End Pricing Item -->

       <!--  </div> --> 

    <!--   </div>

    </section> --><!-- /Pricing Section -->

    <!-- Faq Section -->
   <!--  <section id="faq" class="faq section">

     
      <div class="container section-title" data-aos="fade-up">
        <h2>Frequently Asked Questions</h2>
      </div>

      <div class="container">

        <div class="row justify-content-center">

          <div class="col-lg-10" data-aos="fade-up" data-aos-delay="100">

            <div class="faq-container">

              <div class="faq-item faq-active">
                <h3>Non consectetur a erat nam at lectus urna duis?</h3>
                <div class="faq-content">
                  <p>Feugiat pretium nibh ipsum consequat. Tempus iaculis urna id volutpat lacus laoreet non curabitur gravida. Venenatis lectus magna fringilla urna porttitor rhoncus dolor purus non.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div>

              <div class="faq-item">
                <h3>Feugiat scelerisque varius morbi enim nunc faucibus?</h3>
                <div class="faq-content">
                  <p>Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim. Mauris ultrices eros in cursus turpis massa tincidunt dui.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div>

              <div class="faq-item">
                <h3>Dolor sit amet consectetur adipiscing elit pellentesque?</h3>
                <div class="faq-content">
                  <p>Eleifend mi in nulla posuere sollicitudin aliquam ultrices sagittis orci. Faucibus pulvinar elementum integer enim. Sem nulla pharetra diam sit amet nisl suscipit. Rutrum tellus pellentesque eu tincidunt. Lectus urna duis convallis convallis tellus. Urna molestie at elementum eu facilisis sed odio morbi quis</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div>

              <div class="faq-item">
                <h3>Ac odio tempor orci dapibus. Aliquam eleifend mi in nulla?</h3>
                <div class="faq-content">
                  <p>Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim. Mauris ultrices eros in cursus turpis massa tincidunt dui.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div>

              <div class="faq-item">
                <h3>Tempus quam pellentesque nec nam aliquam sem et tortor?</h3>
                <div class="faq-content">
                  <p>Molestie a iaculis at erat pellentesque adipiscing commodo. Dignissim suspendisse in est ante in. Nunc vel risus commodo viverra maecenas accumsan. Sit amet nisl suscipit adipiscing bibendum est. Purus gravida quis blandit turpis cursus in</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div>

              <div class="faq-item">
                <h3>Perspiciatis quod quo quos nulla quo illum ullam?</h3>
                <div class="faq-content">
                  <p>Enim ea facilis quaerat voluptas quidem et dolorem. Quis et consequatur non sed in suscipit sequi. Distinctio ipsam dolore et.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div>

            </div>

          </div>

        </div>

      </div>

    </section> -->
    <!-- /Faq Section -->

   <!-- Testimonials Section -->
<section id="testimonials" class="testimonials section light-background" style="padding: 60px 0; background: #f8f9fa;">
  <!-- Section Title -->
  <div class="container" style="text-align: center; margin-bottom: 40px;">
    <h2>Testimonials</h2>
    <p>Tell your story.</p>
  </div>

  <div class="container">
    <div class="row testimonial-container" style="position: relative;">
      <div class="col-12">
        <div class="testimonial-wrapper" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 250px)); gap: 20px;  justify-content: center; max-width: 800px; margin: 0 auto;">
          <?php foreach ($testimonials as $index => $testimonial): ?>
            <div class="testimonial-card" data-page="<?= floor($index / 3) + 1 ?>" 
                 style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: block;">
              <!-- Stars -->
              <div style="color: #ffc107; margin-bottom: 15px;">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
              </div>
              
              <!-- Testimonial Content -->
              <div style="min-height: 120px; margin-bottom: 20px;">
                <p style="font-size: 14px; line-height: 1.6; color: #444;"><?= htmlspecialchars($testimonial['message']) ?></p>
              </div>
              
              <!-- Profile -->
              <div style="text-align: center;">
                <img 
                  src="../client/uploads/<?= htmlspecialchars($testimonial['image']) ?>" 
                  alt="Profile of <?= htmlspecialchars($testimonial['first_name']) ?>"
                  style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 10px;"
                >
                <h3 style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">
                  <?= htmlspecialchars($testimonial['first_name'] . ' ' . $testimonial['last_name']) ?>
                </h3>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php 
        $items_per_page = 3;
        $total_items = count($testimonials);
        $total_pages = ceil($total_items / $items_per_page);
        
        if ($total_pages > 1): 
        ?>
        <div class="pagination-wrapper" style="display: flex; justify-content: center; margin-top: 30px; gap: 10px;">
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <button onclick="changePage(<?= $i ?>)" class="page-btn" data-page="<?= $i ?>"
                    style="width: 40px; height: 40px; border-radius: 50%; border: none; 
                           background: <?= $i === 1 ? '#007bff' : '#e9ecef' ?>; 
                           color: <?= $i === 1 ? 'white' : '#333' ?>; 
                           cursor: pointer;">
              <?= $i ?>
            </button>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
// Execute this when the page loads to show only the first page initially
document.addEventListener('DOMContentLoaded', function() {
    changePage(1);
});

function changePage(pageNum) {
    // Hide all testimonials first
    document.querySelectorAll('.testimonial-card').forEach(card => {
        card.style.display = 'none';
    });
    
    // Show testimonials for selected page with a smooth fade
    document.querySelectorAll(`.testimonial-card[data-page="${pageNum}"]`).forEach(card => {
        card.style.display = 'block';
    });
    
    // Update pagination buttons
    document.querySelectorAll('.page-btn').forEach(btn => {
        if (parseInt(btn.dataset.page) === pageNum) {
            btn.style.background = '#007bff';
            btn.style.color = 'white';
        } else {
            btn.style.background = '#e9ecef';
            btn.style.color = '#333';
        }
    });
}
</script>
  </main>




<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>


  
</body>
</html>