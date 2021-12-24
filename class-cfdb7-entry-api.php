<?php 

class CFDB7_Entry_API{

    public static $instance;

    private function __construct(){
        add_action('rest_api_init', array($this, 'init'));
    }
    

    public static function get_instance(){
        if( empty( self::$instance ) ){
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(){
        register_rest_route( 'cfdb7/v1', '/submissions/(?P<form_id>\d+)/(?P<page>\d+)/(?P<key>\w+)',array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_submissions' )
        ));
        register_rest_route( 'cfdb7/v1', '/forms/(?P<key>\w+)',array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_forms' )
        ));
        register_rest_route( 'cfdb7/v1', '/change-status/(?P<id>\d+)/(?P<status>\w+)/(?P<key>\w+)',array(
            'methods'  => 'GET',
            'callback' => array( $this, 'change_status' )
        ));
        register_rest_route( 'cfdb7/v1', '/get-total-count/(?P<key>\w+)',array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_total_count' )
        ));
    }

    /**
     * Get submissions
     * @param key
     * @param form_id
     * @param page
     */

    public function get_submissions( $data ){

        $orderby      = isset($_GET['orderby']) ? 'form_date' : 'form_id';
        $order        = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
        $page         = (int) $data['page'];
        $key          = $data['key'];
        $limit        = 10;
        $page         = $page - 1;
        $start        = $page * $limit;

        if( get_option('cfdb7-api-key') != $key ) return ['status' => false];

        global $wpdb;

        $cfdb          = apply_filters( 'cfdb7_database', $wpdb );
        $table_name    = $cfdb->prefix.'db7_forms';
        $form_id       = (int) $data['form_id'];
        $res           = $cfdb->get_results( "SELECT * FROM $table_name 
                            WHERE form_post_id = '$form_id'
                            ORDER BY $orderby $order
                            LIMIT $start, $limit
                        ", ARRAY_A);
        
        $data = [];
        $bl   = array('\"',"\'",'/','\\','"',"'");
        $wl   = array('&quot;','&#039;','&#047;', '&#092;','&quot;','&#039;');
        $upload_dir    = wp_upload_dir();
        $cfdb7_dir_url = $upload_dir['baseurl'].'/cfdb7_uploads';

        foreach($res as $key => $val){
            $arr = unserialize( $val['form_value'] );
            foreach( $arr as $k => $v){
                $arr[ $k ] = str_replace($wl, $bl, $v);
                $arr[ $k ] = stripslashes( $arr[ $k ] );
                if( strpos($k, 'cfdb7_file') !== false ){
                    $arr[ $k ] = $cfdb7_dir_url.'/'.$arr[ $k ];
                }
            }
            $data[ $key ] = array_merge($val, $arr );
            $data[ $key ]['form_date'] = date_format(
                                            date_create_from_format(
                                                'Y-m-d H:i:s', $data[ $key ]['form_date']
                                            ), 
                                            'd M Y, h:i A'
                                        );

            unset($data[$key]['form_value']);
            unset($data[$key]['form_post_id']);
           // unset($data[$key]['form_id']);

        }
        $data = array(
            'status' => 1,
            'web_name' => get_bloginfo( 'name' ),
            'home_url' => home_url(),
            'form_name' => get_the_title( $form_id ),
            'form_id' => $form_id,
            'data' => $data,
        );
        return $data;
    }

    /**
     * Get forms
     * @param key
     */
    public function get_forms( $data ){

        if( get_option('cfdb7-api-key') != $data['key'] ) 
            return ['status' => false];

        
        $args = array(
            'post_type'=> 'wpcf7_contact_form',
            'order'    => 'ASC',
            'posts_per_page' => -1,
        );

        $the_query = new WP_Query( $args );

        global $wpdb;

        $cfdb          = apply_filters( 'cfdb7_database', $wpdb );
        $table_name    = $cfdb->prefix.'db7_forms';

        $data = array();
        $forms = array();
        $status = '%s:12:"cfdb7_status";s:6:"unread";%';

        while ( $the_query->have_posts() ) : $the_query->the_post();
            $form_post_id = get_the_id();
            $total_item   = $cfdb->get_var("SELECT COUNT(*) FROM $table_name WHERE form_post_id = $form_post_id");
            $unread_count = $cfdb->get_var("SELECT COUNT(*) FROM $table_name 
                                WHERE form_post_id = $form_post_id
                                AND form_value LIKE '$status'
                            ");
            $last_msg     = $cfdb->get_row("SELECT form_date from $table_name 
                                WHERE form_post_id = $form_post_id 
                                ORDER BY form_id DESC LIMIT 1",
                                ARRAY_A
                            ); 
            
            $last_msg_date = isset( $last_msg['form_date'] ) ? $last_msg['form_date'] : '';

            if( !empty( $last_msg_date ) ){
                $last_msg_date = date_format(
                    date_create_from_format(
                        'Y-m-d H:i:s', $last_msg_date
                    ), 
                    'd M Y, h:i A'
                );
            }

            $title = get_the_title();

            $forms[] = [ 
                'form_id' => $form_post_id, 
                'count' => $total_item, 
                'unread_count' => $unread_count,
                'title' => $title,
                'last_msg_date' => $last_msg_date 
            ];

        endwhile;

        return[
            'status' => 1,
            'forms' => $forms
        ];

    }

    public function get_total_count(){
        global $wpdb;

        $cfdb          = apply_filters( 'cfdb7_database', $wpdb );
        $table_name    = $cfdb->prefix.'db7_forms';
        $count         = $cfdb->get_var( "SELECT COUNT(*) FROM $table_name");
        return [
            'count' => $count
        ];
    }

    /**
     * Method POST
     */
    public function change_status( $data ){
        $id     = (int) $data['id'];
        $status = $data['status'] == 'read' ? 'read' : 'unread';
        $key    = $data['key'];

        if( get_option('cfdb7-api-key') != $key ) return ['status' => false];

        global $wpdb;

        $cfdb          = apply_filters( 'cfdb7_database', $wpdb );
        $table_name    = $cfdb->prefix.'db7_forms';

        $res = $cfdb->get_row("SELECT * FROM $table_name WHERE form_id = '$id' LIMIT 1 ");
        $form_value = unserialize( $res->form_value );
        $form_value['cfdb7_status'] = $status;
        
        $cfdb->update( $table_name, 
            [ 'form_value' => serialize( $form_value) ], 
            [ 'form_id' => $id ]
        );
    }


}