<?php
/**
 * Plugin Name: Custom Checkout
 * Description: A custom checkout shortcode for WooCommerce.
 * Version: 1.0
 * Author: Pankaj Sharma 
 * Text Domain: custom-checkout-plugin
 *  License: GPLv2 or later
 */

defined( 'ABSPATH' ) || exit;

// Function to enqueue custom styles
function my_custom_plugin_enqueue_styles() {
    // Check if we're on the admin page or front-end page where the form is displayed
    if (is_admin()) {
        // Enqueue the custom CSS for the admin area
        wp_enqueue_style('my-custom-plugin-styles', plugin_dir_url(__FILE__) . 'css/style.css');
    } else {
        // Enqueue the custom CSS for the front-end
        wp_enqueue_style('my-custom-plugin-styles', plugin_dir_url(__FILE__) . 'css/style.css');
    }
}
// Hook the function into WordPress
add_action('wp_enqueue_scripts', 'my_custom_plugin_enqueue_styles'); // For front-end
add_action('admin_enqueue_scripts', 'my_custom_plugin_enqueue_styles'); // For admin area

// Register shortcode
function custom_checkout_shortcode() {
    ob_start();
    ?>
    <div class="custom-checkout">
        <h2>Checkout</h2>
        
        <?php
        // Display cart details
        $cart = WC()->cart->get_cart();
        
        if ( ! empty( $cart ) ) : ?>
            <h3>Cart Details</h3>
            <table class="shop_table shop_table_responsive">
                <thead>
                    <tr>
                        <th>Product Name </th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $cart as $cart_item_key => $cart_item ) :
                        $product = $cart_item['data'];
                        $product_id = $cart_item['product_id'];
                        $product_name = $product->get_name();
                        $quantity = $cart_item['quantity'];
                        $price = $product->get_price();
                        $product_total = $quantity * $price;
                    ?>
                        <tr>
                            <td><?php echo esc_html( $product_name ); ?></td>
                            <td><?php echo esc_html( $quantity ); ?></td>
                            <td><?php echo wp_kses_post( wc_price( $product_total ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p class="order-total">Total Amount: <?php echo WC()->cart->get_cart_total(); ?></p>
        <?php else : ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>

        <!-- Add custom fields for user details -->
        <form id="custom-checkout-form" method="post">
            <h2> Shipping & Billing Details </h2> 
            <p>
                <label for="billing_name">Name</label>
                <input type="text" id="billing_name" name="billing_name" required>
            </p>
            <p>
                <label for="billing_email">Email</label>
                <input type="email" id="billing_email" name="billing_email" required>
            </p>
            <p>
                <label for="billing_address">Address</label>
                <textarea id="billing_address" name="billing_address" required></textarea>
            </p>
            <p>
                <button type="submit" name="place_order">Place Order</button>
            </p>
        </form>
        
        <?php
        // Handle form submission
        if ( isset( $_POST['place_order'] ) ) {
            // Sanitize and validate input
            $billing_name = sanitize_text_field( $_POST['billing_name'] );
            $billing_email = sanitize_email( $_POST['billing_email'] );
            $billing_address = sanitize_textarea_field( $_POST['billing_address'] );

            // Create a new order
            $order = wc_create_order();
            
            // Add cart items to order
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $product_id = $cart_item['product_id'];
                $quantity = $cart_item['quantity'];
                $order->add_product( wc_get_product( $product_id ), $quantity );
            }
            
            // Set billing details
            $order->set_billing_first_name( $billing_name );
            $order->set_billing_email( $billing_email );
            $order->set_billing_address_1( $billing_address );
            
            // Calculate totals and save order
            $order->calculate_totals();
            $order->save();
            
            // Empty the cart
            WC()->cart->empty_cart();

              // Define the Page ID
             $thanks_page_id = 7;

             // Get the URL of the thank you page based on the page ID 
              $thanks_page_url = get_permalink($thanks_page_id);

             // Redirect to a thank you page or show a success message
             wp_redirect($thanks_page_url);
             exit;
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'custom_checkout', 'custom_checkout_shortcode' );

