<?php 
/*
Plugin name: CFDB7 Connector
Plugin URI: https://ciphercoin.com/
Description: Contact form 7 - CFDB7 iOS/ Android App Connector
Author: Arshid
Author URI: https://ciphercoin.com/
Version: 1.0.0
*/

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require plugin_dir_path(__FILE__).'/class-cfdb7-entry-api.php';
CFDB7_Entry_API::get_instance();

add_action( 'admin_menu', 'cfdb7_app_connector_admin_menu', 99 );

function cfdb7_app_connector_admin_menu(){
    add_submenu_page('cfdb7-list.php', 'App Connector', 'App Connector', 'cfdb7_access',
    'cfdb7-api',  'cfdb7_api_sub_menu' );
}

function cfdb7_api_sub_menu(){

    require plugin_dir_path(__FILE__).'/vendor/autoload.php';

    $cfdb7_key = get_option('cfdb7-api-key');
    $web_title = get_option('blogname');

    if( empty( $cfdb7_key ) && empty($_POST['cfdb7-api-key'])){
        $cfdb7_key = cfdb7_csv_randon(55);
        update_option('cfdb7-api-key', $cfdb7_key);
    }
    if( isset($_POST['cfdb7-api-key']) ){
        $cfdb7_key = cfdb7_csv_randon(55);
        update_option('cfdb7-api-key', $cfdb7_key);
    }

    ?>
    <div class="wrap">
        <h2>QR Code</h2>
        <?php 
        $options = new QROptions([
            'version'    => 5,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'   => QRCode::ECC_L
        ]);

        $qrcode  = new QRCode($options);

        echo $qrcode->render(home_url("::$cfdb7_key::$web_title"));
        // echo '<img src="'.$qrcode->render($data).'" alt="QR Code" />';
        ?>
        <form action="" method="post">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <!-- <th scope="row"><label for="cfdb7-api-key">Public CSV Key</label></th> -->
                        <td>
                            <input type="hidden" name="cfdb7-api-key" type="text" id="cfdb7-api-key" 
                            value="<?php echo $cfdb7_key ?>" class="regular-text" readonly/>
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type="submit" value="Reset" class="button button-primary" />
        </form>
    </div>
    <?php 
}

if( ! function_exists( 'cfdb7_csv_randon' ) ){
    function cfdb7_csv_randon($n) { 
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $randomString = ''; 

        for ($i = 0; $i < $n; $i++) { 
            $index = rand(0, strlen($characters) - 1); 
            $randomString .= $characters[$index]; 
        } 

        return $randomString; 
    }
}
