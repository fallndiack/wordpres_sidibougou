<?php

/**
 * The template for displaying the footer
 *
 * Contains the opening of the #site-footer div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

?>
<!-- ======= Footer ======= -->
<footer id="footer">

	<div class="footer-newsletter">
		<div class="container">
			<div class="row">
				<div class="col-lg-6">
					<h4>Our Newsletter</h4>
					<p>Tamen quem nulla quae legam multos aute sint culpa legam noster magna</p>
				</div>
				<div class="col-lg-6">
					<form action="" method="post">
						<input type="email" name="email"><input type="submit" value="Subscribe">
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="footer-top">
		<div class="container">
			<div class="row">

				<div class="col-lg-3 col-md-6 footer-links">
					<h4>MENU</h4>
					<ul>
						<li><i class="bx bx-chevron-right"></i> <a href="<?php echo get_page_link(get_page_by_title('Sidi Bougou Village')->ID); ?>">Accueil</a></li>
						<li><i class="bx bx-chevron-right"></i> <a href="<?php echo get_page_link(get_page_by_title('Liste des Actualités')->ID); ?>">Actualités</a></li>
						<li><i class="bx bx-chevron-right"></i> <a href="<?php echo get_page_link(get_page_by_title('Liste des événements à venir')->ID); ?>">Evénements</a></li>
						<li><i class="bx bx-chevron-right"></i> <a href="<?php echo get_page_link(get_page_by_title('Edifices et Monuments')->ID); ?>">Edifices et Monuments</a></li>
						<li><i class="bx bx-chevron-right"></i> <a href="<?php echo get_page_link(get_page_by_title('Nous Contacter')->ID); ?>">Nous Contacter</a></li>
						<li><i class="bx bx-chevron-right"></i> <a href="#">Privacy policy</a></li>
					</ul>
				</div>

				<div class="col-lg-3 col-md-6 footer-links">
					<h4>Our Services</h4>
					<ul>
						<li><i class="bx bx-chevron-right"></i> <a href="#">Web Design</a></li>
						<li><i class="bx bx-chevron-right"></i> <a href="#">Web Development</a></li>
						<li><i class="bx bx-chevron-right"></i> <a href="#">Product Management</a></li>
						<li><i class="bx bx-chevron-right"></i> <a href="#">Marketing</a></li>
						<li><i class="bx bx-chevron-right"></i> <a href="#">Graphic Design</a></li>
					</ul>
				</div>

				<div class="col-lg-3 col-md-6 footer-contact">
					<h4>Contact Us</h4>
					<p>
						A108 Adam Street <br>
						New York, NY 535022<br>
						United States <br><br>
						<strong>Phone:</strong> +1 5589 55488 55<br>
						<strong>Email:</strong> info@example.com<br>
					</p>

				</div>

				<div class="col-lg-3 col-md-6 footer-info">
					<h3>About Eterna</h3>
					<p>Cras fermentum odio eu feugiat lide par naso tierra. Justo eget nada terra videa magna derita valies darta donna mare fermentum iaculis eu non diam phasellus.</p>
					<div class="social-links mt-3">
						<a href="#" class="twitter"><i class="bx bxl-twitter"></i></a>
						<a href="#" class="facebook"><i class="bx bxl-facebook"></i></a>
						<a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
						<a href="#" class="google-plus"><i class="bx bxl-skype"></i></a>
						<a href="#" class="linkedin"><i class="bx bxl-linkedin"></i></a>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="container">
		<div class="copyright">
			&copy; Copyright <strong><span>Sidi Bougou</span></strong>. Tous droits réservés
		</div>
		<div class="credits">
			<!-- All the links in the footer should remain intact. -->
			<!-- You can delete the links only if you purchased the pro version. -->
			<!-- Licensing information: https://bootstrapmade.com/license/ -->
			<!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/eterna-free-multipurpose-bootstrap-template/ -->
			Designed by <a href="https://ndiackfall.com/" target="blank">Ndiack Fall</a>
		</div>
	</div>
</footer><!-- End Footer -->

<a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>



<?php wp_footer(); ?>

</body>

</html>