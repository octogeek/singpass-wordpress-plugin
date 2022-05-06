<?php

function qr_code_scripts(){

	$path = plugin_dir_url( __FILE__ );

    echo '<script src="https://stg-id.singpass.gov.sg/static/ndi_embedded_auth.js"></script>';
    echo '<script src="'.$path.'../content/singpass.js"></script>';
}

function show_qr_code() {
wp_enqueue_style( 'wp-pointer' );
wp_enqueue_script( 'wp-pointer' );
wp_enqueue_script( 'utils' );
?>

<body onload="init()">
<div id="ndi-qr"></div>
</body><?php
}
?>