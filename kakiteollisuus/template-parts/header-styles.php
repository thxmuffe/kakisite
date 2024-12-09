<!-- header styles -->

<?php
   $localFonts = apply_filters('get_local_fonts', '');
?>
<?php if ($localFonts) : ?> 
   <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/<?php echo $localFonts; ?>" media="screen" type="text/css" />
<?php else : ?>
   <?php endif; ?>
<link id="u-google-font" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i">
<style> .u-header {
  background-image: none;
}
.u-header .u-sheet-1 {
  min-height: 165px;
}
.u-header .u-image-1 {
  width: 100px;
  height: 115px;
  margin: 18px auto 0 32px;
}
.u-header .u-logo-image-1 {
  width: 100%;
  height: 100%;
}
.u-header .u-menu-1 {
  margin: -101px 37px 46px auto;
}
.u-header .u-hamburger-link-1 {
  font-size: calc(1em + 35.5px);
  padding: 18px 24px;
}
.u-header .u-nav-1 {
  font-size: 1rem;
}
.u-header .u-nav-2 {
  font-size: 1.25rem;
}
@media (max-width: 1199px) {
  .u-header .u-logo-image-1 {
    max-width: 64px;
    max-height: 64px;
  }
  .u-header .u-menu-1 {
    width: auto;
    margin-top: -101px;
    margin-right: 37px;
  }
  .u-header .u-nav-1 {
    letter-spacing: normal;
  }
}
@media (max-width: 991px) {
  .u-header .u-image-1 {
    width: 125px;
    height: 142px;
  }
  .u-header .u-menu-1 {
    margin-top: -105px;
  }
}
@media (max-width: 767px) {
  .u-header .u-image-1 {
    width: auto;
    height: 142px;
  }
  .u-header .u-menu-1 {
    margin-top: -104px;
  }
}
@media (max-width: 575px) {
  .u-header .u-sheet-1 {
    min-height: 189px;
  }
  .u-header .u-image-1 {
    width: 58px;
    height: 146px;
  }
  .u-header .u-menu-1 {
    margin-top: -108px;
  }
}</style>
