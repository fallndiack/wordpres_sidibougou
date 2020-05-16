<?php
/*
* Template Name: Contact page
* description: >-
Page pour nous contacter
*/

?>


<?php get_header() ?>

<div class="container">
    <div class="row">



        <!--Section: Contact v.1-->
        <section class="section pb-5">
            <h2 class="section-heading h1 pt-4">Nous Contacter</h2>
            <!--Section description-->
            <p class="section-description pb-4">
                Vous voulez participer au développement du village,
                vous avez des idées qui peuvent faire avancer les choses,
                n'hesitez pas à nous envoyer un email.
                On sera ravi de vous lire et de vous répondre dans les meilleurs délais.

            </p>

            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="info-box  mb-4">
                        <i class="bx bx-envelope"></i>
                        <h3>Email</h3>
                        <p>sidibougouvillage@gmail.com</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="info-box  mb-4">
                        <i class="bx bx-phone-call"></i>
                        <h3>Telephone</h3>
                        <p>+221 77 810 41 71</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="info-box mb-4">
                        <i class="bx bx-map"></i>
                        <h3>Passez nous voir</h3>
                        <p>Entre Mbour et Nianing à 1,5km de Warang</p>
                    </div>
                </div>
            </div>
            <!--Section heading-->

            <div class="row">

                <!--Grid column-->
                <div class="col-lg-5 mb-4">


                    <?= do_shortcode('[contact-form-7 id="279" title="Formulaire de contact 1"]') ?>


                </div>
                <!--Grid column-->

                <!--Grid column-->
                <div class="col-lg-7" style="padding-top: 90px">

                    <!--Google map-->
                    <div id="map-container-google-11" class="z-depth-1-half map-container-6" style="height: 400px">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d30917.752273212318!2d-16.9324488!3d14.3856612!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTTCsDIzJzAwLjAiTiAxNsKwNTUnMDAuMCJX!5e0!3m2!1sfr!2sbe!4v1588804041632!5m2!1sfr!2sbe" width="600" height="450" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe> </div>

                    <br>
                    <!--Buttons-->
                    <div class="row text-center">
                        <div class="col-md-4">
                            <a class="btn-floating blue accent-1"><i class="fas fa-map-marker-alt"></i></a>
                            <p>Sidi Bougou village</p>
                            <p>Mbour, Thiès Sénégal</p>
                        </div>

                        <div class="col-md-4">
                            <a class="btn-floating blue accent-1"><i class="fas fa-phone"></i></a>
                            <p>+221 77 810 41 71</p>

                        </div>

                        <div class="col-md-4">
                            <a class="btn-floating blue accent-1"><i class="fas fa-envelope"></i></a>
                            <p>contact@sidibougou.com</p>

                        </div>
                    </div>

                </div>
                <!--Grid column-->

            </div>

        </section>
        <!--Section: Contact v.1-->



    </div>
</div>






<?php get_footer(); ?>