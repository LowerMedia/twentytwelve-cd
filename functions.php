<?php

/*
#
#   ADD PRODUCTS CONTENT TYPE (CUSTOM POST TYPE)
#
*/

function strains_custom_init() {
  $labels = array(
    'name' => 'Strains',
    'singular_name' => 'Strain',
    'add_new' => 'Add Strain',
    'add_new_item' => 'Add New Strain',
    'edit_item' => 'Edit Strain',
    'new_item' => 'New Strain',
    'all_items' => 'All Strains',
    'view_item' => 'View Strain',
    'search_items' => 'Search Strains',
    'not_found' =>  'No Strains found',
    'not_found_in_trash' => 'No Strains found in Trash', 
    'parent_item_colon' => '',
    'menu_name' => 'Strains'
  );

  $args = array(
    'labels' => $labels,
    'description'   => 'Canna Delivery Strain',
    'menu_position' => 1,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => 'canna-delivery-strains' ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
    'taxonomies' => array('category', 'post_tag')
  ); 

  register_post_type( 'strains', $args );
}
add_action( 'init', 'strains_custom_init' );

/*
#
#   LEAFLY INTEGRATION
#
*/

function leafly_shortcode () {

    $output;

    query_posts('post_type=strains');
    if ( have_posts() ){
      while ( have_posts() ){
        the_post(); 
        $canna_leafly_strain_array[] = the_title('','',false);
      }
    }

    //$output .= var_dump($canna_leafly_strain_array);
    //$output .= "<br />\n";

    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
       CURLOPT_RETURNTRANSFER => 1,
       CURLOPT_URL => 'http://www.leafly.com/api/strains?action=query&Id',
       CURLOPT_USERAGENT => 'Canna Delivery cURL Request'
    ));
    // Send the request & save response to $resp
    $content = curl_exec($curl);
    // Close request to clear up some resources
    $leafly_strain_array = json_decode($content, true);

    //foreach different strain (leafly_strain_array)
    foreach ($leafly_strain_array as $leafly_strain_info) {
        //for each item (a title) in the $canna_leafly_strain_array process as $canna_strain_name
        foreach ($canna_leafly_strain_array as $canna_strain_name) {

            if ($leafly_strain_info["Name"] == $canna_strain_name) {
                $count=0;
                //if the leafly strain name is equal to the canna strain name we have a match
                $output .= "WE HAVE A MATCH"."<br />\n";
                $output .= $canna_strain_name."<br />\n";
                $output .= $nameofleaflystrain."<br />\n";
                //echo $leafly_strain_info["Name"][0]."<br />\n";

                foreach ($leafly_strain_info as $leafly_strain_value) {
                    //use switch statment to decide what the leafly value's name is by way of the counter
                    switch ($count) {
                      case 0:
                        $leafly_value_name='ID';
                        break;
                      case 1:
                        $leafly_value_name='Slug';
                        break;
                      case 2:
                        $leafly_value_name='Name';
                        break;
                      case 3:
                        $leafly_value_name='Category';
                        break;
                      case 4:
                        $leafly_value_name='Element Nickname';
                        break;
                      case 5:
                        $leafly_value_name='Description';
                        break;
                      case 6:
                        $leafly_value_name='URL';
                        break;
                      case 7:
                        $leafly_value_name='API URL';
                        break;
                      case 8:
                        $leafly_value_name='Leave Review URL';
                        break;
                      case 9:
                        $leafly_value_name='Rating';
                        break;
                      default:
                        $leafly_value_name='Value';
                    }
                    
                    if($leafly_strain_value == "n/a"){} else {$matching_strain_array_info[] = "$leafly_value_name: $leafly_strain_value<br />\n";   }
                    if ($count==12) {$matching_strain_array_info[] = "<br />\n<br />\n";}
                    $count++;
                }   
            }
        }
        // if ($wehaveamatch) {$FULL_canna_strain_value_array .= $TEMP_canna_strain_value_array[]}
    }

    $output .= "<br/><br/>\n\n\n";
    foreach ($matching_strain_array_info as $key => $strain_quality) {
        $output .= "$strain_quality";
    }
    curl_close($curl);
    return $output;

}
add_shortcode( 'leafly_matches', 'leafly_shortcode' );



/*
#
#   REGISTER JS
#
*/

function lowermedia_scripts() {
    // wp_enqueue_script(
    //     'continent-map',
    //     get_stylesheet_directory_uri() . '/continentmap.js',
    //     array( 'jquery' )
    // );
    //     wp_enqueue_script(
    //     'map-data',
    //     get_stylesheet_directory_uri() . '/mapdata.js',
    //     array( 'jquery' )
    // );
}

add_action( 'wp_enqueue_scripts', 'lowermedia_scripts' );

function lowermedia_enqueue_parent_style() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'lowermedia_enqueue_parent_style' );

function lowermedia_enqueue_child_style() {
    wp_enqueue_style( 'child-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'lowermedia_enqueue_child_style', 11 );

/*
#
#   Make Archives.php Include Custom Post Types
#   http://css-tricks.com/snippets/wordpress/make-archives-php-include-custom-post-types/
#
*/

function namespace_add_custom_types( $query ) {
  if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
    $query->set( 'post_type', array(
     'post', 'products'
        ));
      return $query;
    }
}
add_filter( 'pre_get_posts', 'namespace_add_custom_types' );

// Define what post types to search
function searchAll( $query ) {
    if ( $query->is_search ) {
        $query->set( 'post_type', array( 'post', 'page', 'feed', 'products', 'people'));
    }
    return $query;
}

// The hook needed to search ALL content
add_filter( 'the_search_query', 'searchAll' );

function format_phonenumber( $arg ) {
    $data = '+'.$arg;
    if(  preg_match( '/^\+\d(\d{3})(\d{3})(\d{4})$/', $data,  $matches ) )
    {
        $result = $matches[1] . '-' .$matches[2] . '-' . $matches[3];
        return $result;
    }

}

// Add [phonenumber] shortcode
function phonenumber_shortcode( $atts ){
    //retrieve phone number from database
    $lm_array = get_option('lowermedia_phone_number');

    //check if user is on mobile if so make the number a link
    if (wp_is_mobile())
    {
        return '<a href="tel:+'.$lm_array["id_number"].'">'.format_phonenumber($lm_array["id_number"]).'</a>';
    } else {
        return format_phonenumber($lm_array["id_number"]);
    }
}
add_shortcode( 'phonenumber', 'phonenumber_shortcode' );


class lowermedia_phonenumber_settings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Phone Number', 
            'manage_options', 
            'lowermedia-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'lowermedia_phone_number' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Canna Delivery Hotline</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'lowermedia_phone_options' );   
                do_settings_sections( 'lowermedia-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'lowermedia_phone_options', // Option group
            'lowermedia_phone_number', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'My Custom Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'lowermedia-setting-admin' // Page
        );  

        add_settings_field(
            'id_number', // ID
            'ID Number', // Title 
            array( $this, 'id_number_callback' ), // Callback
            'lowermedia-setting-admin', // Page
            'setting_section_id' // Section           
        );      
   
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="lowermedia_phone_number[id_number]" value="%s" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }

}

if( is_admin() )
    $lowermedia_phonenumber_settings = new lowermedia_phonenumber_settings();