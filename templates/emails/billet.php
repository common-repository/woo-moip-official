<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Moip\Core;

$billet_link = get_post_meta( $order_id, '_wbo_billet_link', true );
$first_name  = get_post_meta( $order_id, '_billing_first_name', true );
$site_url    = site_url();
$top_img     = Core::plugins_url( 'assets/images/billet-email-top.png' );

?>
<!-- BODY -->
<body topmargin="0" rightmargin="0"
    bottommargin="0" leftmargin="0" marginwidth="0" marginheight="0" width="100%"
    style="border-collapse: collapse; border-spacing: 0; margin: 0;
    padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased;
    text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;
    line-height: 100%;
	background-color: #FFFFFF;
	color: #000000;"
	bgcolor="#FFFFFF"
	text="#000000">
<!-- SECTION / BACKGROUND -->
<!-- Set section background color -->
<table width="100%" align="center" border="0"
    cellpadding="0" cellspacing="0"
    style="border-collapse: collapse; border-spacing: 0;
    margin: 0; padding: 0; width: 100%;" class="background">
    <tr>
        <td align="center" valign="top"
        style="width: 100%; height: 230px; background-image: url('<?php echo esc_attr( $top_img ); ?>');
        background-repeat: no-repeat; background-size: 100% 230px;">
            <!-- WRAPPER -->
            <table border="0" cellpadding="0" cellspacing="0" align="center"
	        width="600" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
	        max-width: 600px;" class="wrapper">
                <tr>
                    <td align="center" valign="top"
                    style="border-collapse: collapse; border-spacing: 0;
                    margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%;
                    width: 87.5%; padding-top: 20px;">
                </tr>
                <!-- HEADER -->
                <tr>
                    <td align="center" valign="top" style="border-collapse: collapse;
                    border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%;
                    padding-right: 6.25%; width: 87.5%; font-size: 24px; font-weight: bold;
                    line-height: 130%; padding-top: 20px; color: #FFFFFF;
                    font-family: sans-serif;" class="header">
                            <?php echo __( 'Moip Billet', 'woo-moip-official' ); ?>
                    </td>
                </tr>
                <!-- SUBHEADER -->
                <tr>
                    <td align="center" valign="top" style="border-collapse: collapse;
                    border-spacing: 0; margin: 0; padding: 0; padding-bottom: 3px;
                    padding-left: 6.25%; padding-right: 6.25%; font-size: 18px;
                    font-weight: 300; line-height: 150%; padding-top: 15px; color: #FFFFFF;
                    font-family: sans-serif;" class="subheader">
                        <?php bloginfo( 'name' ); ?>
                    </td>
                </tr>
            <!-- End of WRAPPER -->
            </table>
        <!-- SECTION / BACKGROUND -->
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" style="border-collapse: collapse;
        border-spacing: 0; margin: 0; padding: 0; padding-top: 5px;"
	    bgcolor="#FFFFFF">
        <!-- WRAPPER -->
        <table border="0" cellpadding="0" cellspacing="0" align="center"
            width="600" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
            max-width: 600px;">
            <!-- FLOATERS -->
            <tr>
                <table width="100%" border="0" cellpadding="0" cellspacing="0" align="right"
                    valign="top" style="border-collapse: collapse; mso-table-lspace: 0pt;
                    mso-table-rspace: 0pt; border-spacing: 0; margin: 0; padding: 0;
                    display: inline-table; float: none;" class="floater">
                    <tr>
                        <td align="center" valign="top" style="border-collapse: collapse;
                        border-spacing: 0; margin: 0; padding: 0; padding-left: 15px;
                        padding-right: 15px; font-size: 17px; font-weight: 400; line-height: 160%;
                        padding-top: 30px; font-family: sans-serif; color: #001631;">
                        <?php
                           echo sprintf(
                            '<p>%s <strong>%s</strong>, %s</p>',
                            __( 'Hi ', 'woo-moip-official' ),
                            $first_name,
                            __( ' all right?', 'woo-moip-official' )
                            );
                        ?>
                        <p><?php echo __( 'To view your billet, just click on the button below:', 'woo-moip-official' ); ?></p>
                        </td>
                    </tr>
                </table>
            </tr>
            <!-- BUTTON -->
            <tr>
                <td align="center" valign="top" style="border-collapse: collapse;
                border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%;
                padding-right: 6.25%; width: 87.5%; padding-top: 30px; padding-bottom: 35px;" class="button">
                    <a href="<?php echo esc_url( $billet_link ); ?>" target="_blank" style="text-decoration: none;">
                        <table border="0" cellpadding="0" cellspacing="0" align="center"
                        style="max-width: 320px; min-width: 130px; border-collapse: collapse;
                        border-spacing: 0; padding: 0;">
                        <tr>
                            <td align="center" valign="middle" style="padding: 25px 60px; margin: 0;
                            text-decoration: none; border-collapse: collapse; border-spacing: 0;"
                            bgcolor="#ff3424">
                            <a target="_blank" style="text-decoration: none; color: #FFFFFF;
                            font-family: sans-serif; font-size: 17px; font-weight: 400; line-height: 120%;"
                            href="<?php echo esc_url( $billet_link ); ?>">
                                <?php echo __( 'View billet', 'woo-moip-official'); ?>
                            </a>
                            </td>
                        </tr>
                        </table>
                    </a>
                </td>
            </tr>
            <tr>
                <table width="100%" border="0" cellpadding="0" cellspacing="0" align="right"
                valign="top" style="border-collapse: collapse; mso-table-lspace: 0pt;
                mso-table-rspace: 0pt; border-spacing: 0; margin: 0; padding: 0;
                display: inline-table; float: none;" class="floater">
                    <tr>
                        <td align="center" valign="top" style="border-collapse: collapse;
                        border-spacing: 0; margin: 0; padding: 0; padding-left: 15px;
                        padding-right: 15px; font-size: 17px; font-weight: 400; line-height: 160%;
                        padding-top: 10px; font-family: sans-serif; color: #000000;">
                            <strong><?php echo __( 'Attention: Do not make payment of expired billets, in this case, a new purchase must be made!', 'woo-moip-official' ); ?></strong>
                            <p style="color: #ff3424;"><?php echo __( 'If you have any questions or problems, please contact us.', 'woo-moip-official' ); ?></p>
                        </td>
                    </tr>
                </table>
            </tr>
        <!-- End of WRAPPER -->
        </table>
        <!-- SECTION / BACKGROUND -->
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" style="border-collapse: collapse;
        border-spacing: 0; margin: 0; padding: 0;"
        bgcolor="#F0F0F0">
        <!-- WRAPPER -->
        <table border="0" cellpadding="0" cellspacing="0" align="center"
            width="600" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
            max-width: 600px;" class="wrapper">
            <!-- FOOTER -->
            <tr>
                <td align="center" valign="top" style="border-collapse: collapse;
                    border-spacing: 0; margin: 0; padding: 0; font-size: 13px;
                    font-weight: 400; line-height: 150%; padding-top: 20px;
                    padding-bottom: 20px; color: #999999;
                    font-family: sans-serif;" class="footer">
                    <p>
                        <a href="<?php echo esc_url( $site_url ); ?>"
                        target="_blank" style="text-decoration: underline; color: #999999;
                        font-family: sans-serif; font-size: 13px; font-weight: 400;
                        line-height: 150%;"><?php bloginfo( 'name' ); ?>
                        </a>
                    </p>
                </td>
            </tr>
        <!-- End of WRAPPER -->
        </table>
        <!-- End of SECTION / BACKGROUND -->
        </td>
    </tr>
</table>
</body>
