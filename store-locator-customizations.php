<?php 
/*
Plugin Name: Store Locator Customization
Plugin URI: https://github.com/kunal1400/store-locator-customizations.git
Description: All customizations related to store locator plugin
Version: 1.0.0
Author: Kunal Malviya
Author URI: mailto:kunal.malviya351@gmail.com
Text Domain: store-locator-customization
*/

/***** STORE LOCATOR: SUPPORT FOR CATEGORIES IN MORE INFO BUTTON *****/

// /**
// * Enabling support for shortcode because store locator doesn't provide that
// **/
// add_filter( 'wpsl_strip_content_shortcode', '__return_false' );

// /**
// * This is my custom shortcode which will take one parameter i.e post_id
// * It return a html of all wpsl_store_category
// **/
// add_shortcode( 'sl_show_all_categories', 'sl_show_all_categories_cb' );
// function sl_show_all_categories_cb( $atts ) {
//  extract( shortcode_atts( array(
//         'post_id' => '',
//     ), $atts ) );

//  $stringToReturn = "";
//  if ( !empty($post_id) ) {
//      $assignedCategories = wp_get_post_terms( $post_id, "wpsl_store_category");
//      if ( is_array($assignedCategories) && count($assignedCategories) > 0 ) {
//        foreach ($assignedCategories as $i => $assignedCategory) {
//          $stringToReturn .= '<div>'.$assignedCategory->name.'</div>';
//        }
//      }
//  }

//  return $stringToReturn;
// }

/**
* https://wpstorelocator.co/document/category-names-in-search-results/
*
* If a location is assigned to multiple categories, then the category names are separated by a comma
**/
add_filter( 'wpsl_store_meta', 'custom_store_meta', 10, 2 );
function custom_store_meta( $store_meta, $store_id ) {
  $terms = get_the_terms( $store_id, 'wpsl_store_category' );
  $store_meta['terms'] = '';
  if ( $terms ) {
    if ( ! is_wp_error( $terms ) ) {
      if ( count( $terms ) > 1 ) {
        $location_terms = array();

        foreach ( $terms as $term ) {
          $location_terms[] = '<span style="display:block">'.$term->name.'</span>';
        }

        $store_meta['terms'] = implode( '', $location_terms );
      } else {
        $store_meta['terms'] = $terms[0]->name;
      }
    }
  }
  return $store_meta;
}

/**
* https://wpstorelocator.co/document/category-names-in-search-results/
*
* The code below shows the search results template with the category names placed directly below the address details
**/
add_filter( 'wpsl_listing_template', 'custom_listing_template' );
function custom_listing_template() {

  global $wpsl, $wpsl_settings;
  
  $listing_template = '<li data-store-id="<%= id %>">' . "\r\n";
  $listing_template .= "\t\t" . '<div class="wpsl-store-location">' . "\r\n";
  $listing_template .= "\t\t\t" . '<p><%= thumb %>' . "\r\n";
  $listing_template .= "\t\t\t\t" . wpsl_store_header_template( 'listing' ) . "\r\n"; // Check which header format we use
  $listing_template .= "\t\t\t\t" . '<span class="wpsl-street"><%= address %></span>' . "\r\n";
  $listing_template .= "\t\t\t\t" . '<% if ( address2 ) { %>' . "\r\n";
  $listing_template .= "\t\t\t\t" . '<span class="wpsl-street"><%= address2 %></span>' . "\r\n";
  $listing_template .= "\t\t\t\t" . '<% } %>' . "\r\n";
  $listing_template .= "\t\t\t\t" . '<span>' . wpsl_address_format_placeholders() . '</span>' . "\r\n"; // Use the correct address format

  if ( !$wpsl_settings['hide_country'] ) {
      $listing_template .= "\t\t\t\t" . '<span class="wpsl-country"><%= country %></span>' . "\r\n";
  }

  $listing_template .= "\t\t\t" . '</p>' . "\r\n";
  
   // Include the category names.
  $listing_template .= "\t\t\t" . '<% if ( terms ) { %>' . "\r\n";
  $listing_template .= "\t\t\t" . '<p><span><strong>' . __( 'Categories:', 'wpsl' ) . '</span></strong> <%= terms %></p>' . "\r\n";
  $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";

  // Show the phone, fax or email data if they exist.
  if ( $wpsl_settings['show_contact_details'] ) {
      $listing_template .= "\t\t\t" . '<p class="wpsl-contact-details">' . "\r\n";
      $listing_template .= "\t\t\t" . '<% if ( phone ) { %>' . "\r\n";
      $listing_template .= "\t\t\t" . '<span><strong>' . esc_html( $wpsl->i18n->get_translation( 'phone_label', __( 'Phone', 'wpsl' ) ) ) . '</strong>: <%= formatPhoneNumber( phone ) %></span>' . "\r\n";
      $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
      $listing_template .= "\t\t\t" . '<% if ( fax ) { %>' . "\r\n";
      $listing_template .= "\t\t\t" . '<span><strong>' . esc_html( $wpsl->i18n->get_translation( 'fax_label', __( 'Fax', 'wpsl' ) ) ) . '</strong>: <%= fax %></span>' . "\r\n";
      $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
      $listing_template .= "\t\t\t" . '<% if ( email ) { %>' . "\r\n";
      $listing_template .= "\t\t\t" . '<span><strong>' . esc_html( $wpsl->i18n->get_translation( 'email_label', __( 'Email', 'wpsl' ) ) ) . '</strong>: <%= email %></span>' . "\r\n";
      $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
      $listing_template .= "\t\t\t" . '</p>' . "\r\n";
  }

  $listing_template .= "\t\t\t" . wpsl_more_info_template() . "\r\n"; // Check if we need to show the 'More Info' link and info
  $listing_template .= "\t\t" . '</div>' . "\r\n";
  $listing_template .= "\t\t" . '<div class="wpsl-direction-wrap">' . "\r\n";

  if ( !$wpsl_settings['hide_distance'] ) {
      $listing_template .= "\t\t\t" . '<%= distance %> ' . esc_html( $wpsl_settings['distance_unit'] ) . '' . "\r\n";
  }

  $listing_template .= "\t\t\t" . '<%= createDirectionUrl() %>' . "\r\n"; 
  $listing_template .= "\t\t" . '</div>' . "\r\n";
  $listing_template .= "\t" . '</li>';

  return $listing_template;
}

/**
* https://wpstorelocator.co/document/category-names-in-search-results/
*
* If you also want to show the used categories in the marker info window, then you can use the code example below.
**/
/*add_filter( 'wpsl_info_window_template', 'custom_info_window_template' );
function custom_info_window_template() {   
  $info_window_template = '<div data-store-id="<%= id %>" class="wpsl-info-window">' . "\r\n";
  $info_window_template .= "\t\t" . '<p>' . "\r\n";
  $info_window_template .= "\t\t\t" .  wpsl_store_header_template() . "\r\n";  
  $info_window_template .= "\t\t\t" . '<span><%= address %></span>' . "\r\n";
  $info_window_template .= "\t\t\t" . '<% if ( address2 ) { %>' . "\r\n";
  $info_window_template .= "\t\t\t" . '<span><%= address2 %></span>' . "\r\n";
  $info_window_template .= "\t\t\t" . '<% } %>' . "\r\n";
  $info_window_template .= "\t\t\t" . '<span>' . wpsl_address_format_placeholders() . '</span>' . "\r\n";
  $info_window_template .= "\t\t" . '</p>' . "\r\n";
  
  // Include the category names.
  $info_window_template .= "\t\t" . '<% if ( terms ) { %>' . "\r\n";
  $info_window_template .= "\t\t" . '<p>' . __( 'Categories:', 'wpsl' ) . ' <%= terms %></p>' . "\r\n";
  $info_window_template .= "\t\t" . '<% } %>' . "\r\n";
  
  $info_window_template .= "\t\t" . '<%= createInfoWindowActions( id ) %>' . "\r\n";
  $info_window_template .= "\t" . '</div>' . "\r\n";
  
  return $info_window_template;
}*/
/***** END *****/