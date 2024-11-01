<?php
  // only load head + scripts

$swifty_scc_get_styles = apply_filters( 'swifty_scc_get_styles', '_NOT_SET_FLAG_' );
do_action( 'swifty_enqueue_scripts' );

 ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">

        <?php wp_print_head_scripts(); ?>
        <?php wp_print_media_templates(); ?>

        <style type="text/css"><?php echo $swifty_scc_get_styles; ?></style>

    </head>

    <body>
      <!-- empty body with only scripts -->
      <?php wp_print_footer_scripts(); ?>
      <?php do_action( 'swifty_print_footer' ); ?>

    </body>
</html>