<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <?php include '../files/csslib.php' ?> <!-- including libraries -->

</head>
<body>

<!-- including header and banner -->
<?php include_once '../files/nav.php' ?>

<br><br>
<main class="main">
    

    <!-- Features Details Section -->
    <section id="features-details" class="features-details section">

      <div class="container">

        <div class="row gy-4 justify-content-between features-item">

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <img src="../assets/img/features-2.jpg" class="img-fluid" alt="">
          </div>

          <div class="col-lg-5 d-flex align-items-center" data-aos="fade-up" data-aos-delay="200">
            <div class="content">
              <h3>Kyokushin Kai Karate history</h3>
              <p>
              Kyokushin Kai Karate, often known simply as Kyokushin, 
              is a style of full-contact karate founded in 1964 by Masutatsu Oyama, 
              a Korean-Japanese martial artist. Oyama was born as Choi Yeong-eui in Korea 
              in 1923 and later moved to Japan, where he immersed himself in various martial arts, 
              including Shotokan karate, judo, and Goju-ryu karate. Driven by a desire to develop a style 
              that emphasized both physical conditioning and mental resilience, Oyama undertook rigorous training, 
              often spending extended periods in isolation in the mountains, refining his techniques and philosophy.
              </p>
              <!-- <a href="#" class="btn more-btn">Learn More</a> -->
            </div>
          </div>

        </div><!-- Features Item -->

        <div class="row gy-4 justify-content-between features-item">

          <div class="col-lg-5 d-flex align-items-center order-2 order-lg-1" data-aos="fade-up" data-aos-delay="100">

            <div class="content">
              <h3>Benefit of karate</h3>
              <p>
              Karate offers transformative benefits that extend far beyond the dojo. Physically, 
              it boosts strength, flexibility, coordination, and cardiovascular health, making it 
              a comprehensive workout for the entire body. Mentally, karate demands focus and discipline, 
              training practitioners to concentrate on precise movements and overcome challenges with a calm, 
              steady mindset. This heightened mental clarity often translates into improved focus and resilience 
              in everyday tasks, whether at school, work, or home.
              </p>
             <!-- <ul>
                <li><i class="bi bi-easel flex-shrink-0"></i> Et corporis ea eveniet ducimus.</li>
                <li><i class="bi bi-patch-check flex-shrink-0"></i> Exercitationem dolorem sapiente.</li>
                <li><i class="bi bi-brightness-high flex-shrink-0"></i> Veniam quia modi magnam.</li>
              </ul>
              <p></p>
              <a href="#" class="btn more-btn">Learn More</a> -->
            </div>

          </div>

          <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-up" data-aos-delay="200">
            <img src="../assets/img/features-1.jpg" class="img-fluid" alt="">
          </div>

        </div><!-- Features Item -->

        </div>

</section><!-- /Features Details Section -->

</main>


<!-- INCLUDING Footer -->
<?php include '../files/footer.php' ?>


    
</body>
</html>