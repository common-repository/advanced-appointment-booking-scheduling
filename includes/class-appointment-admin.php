<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
class ABP_Admin
{
    // Constructor
    public function __construct()
    {
        add_action('admin_menu', [$this, 'abp_register_admin_pages']);
        add_action('init', [$this, 'abp_register_shortcodes']);
        add_action('admin_post_submit_appointment_booking', [$this, 'abp_handle_appointment_booking']);
        add_action('admin_post_nopriv_submit_appointment_booking', [$this, 'abp_handle_appointment_booking']);
        add_action('admin_head', [$this, 'abp_hide_notices_on_plugin_pages']); // Use admin_head for more control

        add_action('admin_menu', function() {
            remove_submenu_page('appointment-booking-admin', 'appointment-booking-admin');
        });
    }


    // Hide notices and ads via JavaScript on specific plugin pages
    public function abp_hide_notices_on_plugin_pages()
    {
        // Check the 'page' query parameter instead of the screen ID
        if (isset($_GET['page']) && in_array($_GET['page'], ['appointment-booking-admin', 'appointment-bookings'])) {
           
        }
    }

    // Register admin pages and submenus
    public function abp_register_admin_pages()
    {
        // Main menu
        add_menu_page(
            'Advanced Appointment Booking',
            'Appointments',  // Main menu name
            'manage_options',
            'appointment-booking-admin',
            [$this, 'abp_render_admin_page'], // Main page callback
            'dashicons-calendar-alt',
            6
        );

        // Remove the automatic first submenu created by WordPress
        add_submenu_page(
            'appointment-booking-admin',  // Parent slug (main menu slug)
            'Our Themes',  // Page title
            'Our Themes',  // Submenu name
            'manage_options',
            'appointment-booking-themes',
            [$this, 'abp_render_admin_page'] // Renders the same page
        );     

        // Submenu for Bookings
        add_submenu_page(
            'appointment-booking-admin',  
            'Bookings',                   
            'Bookings',                   
            'manage_options',             
            'appointment-bookings',       
            [$this, 'abp_render_bookings_page'] // Callback function
        );
    }

    // Register shortcodes
    public function abp_register_shortcodes()
    {
        add_shortcode('appointment_login_form', [$this, 'abp_appointment_login_form_shortcode']);
        add_shortcode('appointment_register_form', [$this, 'abp_appointment_register_form_shortcode']);
        add_shortcode('abp_bookings_page', [$this, 'abp_bookings_page_shortcode']);
        add_shortcode('book_appointment_form', [$this, 'abp_book_appointment_form_shortcode']);
    }


    //menue and submenue code 

    public function abp_render_admin_page()
    {
        ?>
        <div class="wrap abp-page-wrapper">
            <h1>Templates</h1>
            <div>
                <?php
                include_once plugin_dir_path(__FILE__) . 'abp-themes.php';
                ?>
            </div>
        </div>
        <?php
    }

    // submenu page
    public function abp_render_bookings_page()
    {

        // Add nonce field
        $nonce_action = 'appointment_booking_admin_action';
        $nonce_name = 'appointment_booking_admin_nonce';

        if (!isset($_POST[$nonce_name]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_name])), $nonce_action)) {
            // die('Security check failed!');
        }

        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'dashboard';

        ?>
        <div class="wrap">
            <h1>Appointment Booking</h1>
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>" id="nav-dashboard-tab"
                        data-bs-toggle="tab" data-bs-target="#nav-dashboard" type="button" role="tab"
                        aria-controls="nav-dashboard"
                        aria-selected="<?php echo $active_tab == 'dashboard' ? 'true' : 'false'; ?>">Dashboard</button>
                    <button class="nav-link <?php echo $active_tab == 'appointments' ? 'active' : ''; ?>"
                        id="nav-appointments-tab" data-bs-toggle="tab" data-bs-target="#nav-appointments" type="button"
                        role="tab" aria-controls="nav-appointments"
                        aria-selected="<?php echo $active_tab == 'appointments' ? 'true' : 'false'; ?>">Appointments</button>
                    <button class="nav-link <?php echo $active_tab == 'services' ? 'active' : ''; ?>" id="nav-services-tab"
                        data-bs-toggle="tab" data-bs-target="#nav-services" type="button" role="tab"
                        aria-controls="nav-services"
                        aria-selected="<?php echo $active_tab == 'services' ? 'true' : 'false'; ?>">Services</button>
                    <button class="nav-link <?php echo $active_tab == 'customers' ? 'active' : ''; ?>" id="nav-customers-tab"
                        data-bs-toggle="tab" data-bs-target="#nav-customers" type="button" role="tab"
                        aria-controls="nav-customers"
                        aria-selected="<?php echo $active_tab == 'customers' ? 'true' : 'false'; ?>">Customers</button>

                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade <?php echo $active_tab == 'dashboard' ? 'show active' : ''; ?>" id="nav-dashboard"
                    role="tabpanel" aria-labelledby="nav-dashboard-tab">
                    <?php $this->abp_render_dashboard(); ?>
                </div>
                <div class="tab-pane fade <?php echo $active_tab == 'appointments' ? 'show active' : ''; ?>"
                    id="nav-appointments" role="tabpanel" aria-labelledby="nav-appointments-tab">
                    <?php $this->abp_render_appointments(); ?>
                </div>
                <div class="tab-pane fade <?php echo $active_tab == 'services' ? 'show active' : ''; ?>" id="nav-services"
                    role="tabpanel" aria-labelledby="nav-services-tab">
                    <?php include_once plugin_dir_path(__FILE__) . 'services.php'; ?>
                </div>
                <div class="tab-pane fade <?php echo $active_tab == 'customers' ? 'show active' : ''; ?>" id="nav-customers"
                    role="tabpanel" aria-labelledby="nav-customers-tab">
                    <?php $this->abp_render_customers(); ?>
                </div>

            </div>
        </div>
        <?php

    }


    // END 

    // Handle booking
    public function abp_handle_appointment_booking()
    {
        // Check nonce for security
        if (!isset($_POST['book_appointment_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['book_appointment_nonce'])), 'book_appointment_action')) {
            wp_die('Invalid request.');
        }

        // Process form data
        global $wpdb;
        $service_id = isset($_POST['service_id']) ? sanitize_text_field(wp_unslash($_POST['service_id'])) : '';
        $booking_date = isset($_POST['booking_date']) ? sanitize_text_field(wp_unslash($_POST['booking_date'])) : '';
        $booking_time = isset($_POST['booking_time']) ? sanitize_text_field(wp_unslash($_POST['booking_time'])) : '';
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';

        // Fetch service price
        $table_name = $wpdb->prefix . 'appointment_services'; // Hardcoded table name
        $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $service_id));

        if ($service) {
            $price = $service->price;

            // Insert appointment booking data
            $wpdb->insert(
                $wpdb->prefix . 'appointment_booking',
                [
                    'user_id' => get_current_user_id(),
                    'service_id' => $service_id,
                    'booking_date' => $booking_date,
                    'booking_time' => $booking_time,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'price' => $price,
                    'status' => 'pending'
                ],
                [
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%f',
                    '%s'
                ]
            );

            // Check if insert was successful
            if ($wpdb->insert_id) {
                wp_redirect(home_url('')); // Redirect to home or another success page
                exit;
            } else {
                wp_die('Failed to book the appointment.');
            }
        } else {
            wp_die('Invalid service selected.');
        }
    }

    // Login form shortcode
    public function abp_appointment_login_form_shortcode()
    {
        if (is_user_logged_in()) {
            return '<p>You are already logged in.</p>';
        }

        ob_start();
        ?>
        <form action="" method="post">
            <label for="email">Email:</label>
            <input type="email" name="log" id="email" required />
            <label for="password">Password:</label>
            <input type="password" name="pwd" id="password" required />
            <!-- Add nonce field -->
            <?php wp_nonce_field('appointment_login_action', 'appointment_login_nonce'); ?>
            <input type="submit" name="appointment_login" value="Login" />
        </form>
        <?php

        if (isset($_POST['appointment_login'])) {

            $log = isset($_POST['log']) ? sanitize_text_field(wp_unslash($_POST['log'])) : '';
            $pwd = isset($_POST['pwd']) ? sanitize_text_field(wp_unslash($_POST['pwd'])) : '';

            $creds = array(
                'user_login' => $log,
                'user_password' => $pwd,
                'remember' => true,
            );

            $user = wp_signon($creds, false);
            if (is_wp_error($user)) {
                echo '<p>Login failed. Please check your credentials.</p>';
            } else {
                wp_redirect(home_url(''));
                exit;
            }
        }

        return ob_get_clean();
    }




    // Register form shortcode
    public function abp_appointment_register_form_shortcode()
    {
        if (is_user_logged_in()) {
            return '<p>You are already logged in.</p>';
        }

        ob_start();
        ?>
        <form action="" method="post">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" id="full_name" required />
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required />
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required />
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required />
            <?php wp_nonce_field('appointment_register_action', 'appointment_register_nonce'); ?>
            <input type="submit" name="appointment_register" value="Register" />
        </form>
        <?php

        if (isset($_POST['appointment_register'])) {
            if (!isset($_POST['appointment_register_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['appointment_register_nonce'])), 'appointment_register_action')) {
                wp_die('Invalid request.');
            }

            $full_name = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
            $username = isset($_POST['username']) ? sanitize_text_field(wp_unslash($_POST['username'])) : '';
            $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
            $password = isset($_POST['password']) ? sanitize_text_field(wp_unslash($_POST['password'])) : '';

            $userdata = array(
                'user_login' => $username,
                'user_email' => $email,
                'user_pass' => $password,
                'display_name' => $full_name,
            );

            $user_id = wp_insert_user($userdata);

            if (is_wp_error($user_id)) {
                echo '<p>Registration failed. Please try again.</p>';
            } else {
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                wp_redirect(home_url(''));
                exit;
            }
        }

        return ob_get_clean();
    }


    // Shortcode for Bookings page
    public function abp_bookings_page_shortcode()
    {

        // If form is submitted, verify nonce first
        if (isset($_POST['cancel_booking_id'])) {
            // Verify nonce in form handler
            if (!isset($_POST['book_appointment_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['book_appointment_nonce'])), 'book_appointment_action')) {
                wp_die('Invalid request.');
            }

            if (is_user_logged_in()) {
                $current_user_id = get_current_user_id();
                global $wpdb;
                $table_name = $wpdb->prefix . 'appointment_booking';

                $booking_id = intval($_POST['cancel_booking_id']);

                $wpdb->update(
                    $table_name,
                    array('status' => 'Canceled'),
                    array('id' => $booking_id, 'user_id' => $current_user_id)
                );

                echo '<div class="notice notice-success"><p>Your booking has been canceled.</p></div>';
            }
        }

        // Display the bookings if user is logged in
        if (!is_user_logged_in()) {
            return '<p>Please login to view your bookings.</p>';
        }

        $current_user_id = get_current_user_id();
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointment_booking';


        $bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}appointment_booking WHERE user_id = %d", $current_user_id));

        ob_start();

        if ($bookings) {
            echo '<ul class="meeting-lists">';
            foreach ($bookings as $booking) {
                // Sanitize and escape the fetched service name
                $service_name = sanitize_text_field($wpdb->get_var($wpdb->prepare("SELECT service_name FROM {$wpdb->prefix}appointment_services WHERE id = %d", $booking->service_id)));

                echo '<li>';
                echo '<div class="schedule-data" style="display: inline;">';
                echo '. ' . esc_html($booking->service_id);
                echo ' ' . esc_html($service_name);
                echo ' | Date: ' . esc_html($booking->booking_date);
                echo ' | Time: ' . esc_html($booking->booking_time);
                echo ' | Status: ' . esc_html($booking->status);
                echo '</div>';

                // Show "Cancel" button if the booking is not already canceled
                if ($booking->status !== 'Canceled') {
                    echo ' | <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="cancel_booking_id" value="' . esc_attr($booking->id) . '">
                        ' . wp_nonce_field('book_appointment_action', 'book_appointment_nonce', true, false) . '
                        <input type="submit" value="' . esc_attr__('Cancel', 'text-domain') . '" class="button button-secondary" onclick="return confirm(\'' . esc_js(__('Are you sure you want to cancel this booking?', 'text-domain')) . '\');">
                    </form>';
                } else {
                    echo ' | <span style="color: red;">' . esc_html__('Canceled', 'text-domain') . '</span>';
                }

                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . esc_html__('No bookings found.', 'text-domain') . '</p>';
        }

        return ob_get_clean();
    }


    //end 

    function abp_book_appointment_form_shortcode()
    {
        ob_start();

        // Fetch available services
        global $wpdb;
        $table_name = $wpdb->prefix . 'appointment_services';
        $services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}appointment_services");

        ?>
        <form id="appointment-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('book_appointment_action', 'book_appointment_nonce'); ?>
            <input type="hidden" name="action" value="submit_appointment_booking"> <!-- Custom form action -->

            <h3>Select Service</h3>
            <select name="service_id" id="service-select" required>
                <option value="">Select a service</option>
                <?php foreach ($services as $service) { ?>
                    <option value="<?php echo esc_attr($service->id); ?>" data-price="<?php echo esc_attr($service->price); ?>"
                        data-duration="<?php echo esc_attr($service->duration); ?>">
                        <?php echo esc_html($service->service_name); ?> - $<?php echo esc_html($service->price); ?>
                        (<?php echo esc_html($service->duration); ?> mins)
                    </option>
                <?php } ?>
            </select>

            <h3>Select Date</h3>
            <input type="date" name="booking_date" required>

            <h3>Select Time</h3>
            <input type="time" name="booking_time" required>

            <h3>Enter Your Details</h3>
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" required />

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required />

            <label for="phone">Phone:</label>
            <input type="tel" name="phone" id="phone" required />

            <h3>Summary</h3>
            <div id="appointment-summary">
                <p><strong>Service:</strong> <span id="selected-service"></span></p>
                <p><strong>Price:</strong> $<span id="selected-price"></span></p>
                <p><strong>Date:</strong> <span id="selected-date"></span></p>
                <p><strong>Time:</strong> <span id="selected-time"></span></p>
            </div>

            <button type="submit" name="book_appointment">Book Appointment</button>
        </form>
        <?php

        return ob_get_clean();
    }




    //end 



    //for dashboard 
    public function abp_render_dashboard()
    {
        global $wpdb;

        // Fetch total appointments
        $total_appointments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}appointment_booking");

        // Fetch approved appointments
        $approved_appointments = $wpdb->get_var($wpdb->prepare(
            "
        SELECT COUNT(*) FROM {$wpdb->prefix}appointment_booking WHERE status = %s",
            'approved'
        ));

        // Fetch total customers
        $total_customers = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->users}");

        // Fetch total services
        $total_services = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}appointment_services");


        echo '<div class="dashboard-container" style="display: flex; justify-content: space-around; margin-top: 20px;">
        <div class="dashboard-item" style="background-color: #f1f1f1; padding: 20px; text-align: center;">
            <h3>Total Appointments</h3>
            <p id="total-appointments">' . esc_html($total_appointments ? $total_appointments : 0) . '</p>
        </div>

        <div class="dashboard-item" style="background-color: #f1f1f1; padding: 20px; text-align: center;">
            <h3>Total Services</h3>
            <p id="total-services">' . esc_html($total_services ? $total_services : 0) . '</p>
        </div>

        <div class="dashboard-item" style="background-color: #f1f1f1; padding: 20px; text-align: center;">
            <h3>Approved Appointments</h3>
            <p id="approved-appointments">' . esc_html($approved_appointments ? $approved_appointments : 0) . '</p>
        </div>

        <div class="dashboard-item" style="background-color: #f1f1f1; padding: 20px; text-align: center;">
            <h3>Total Customers</h3>
            <p id="total-customers">' . esc_html($total_customers ? $total_customers : 0) . '</p>
        </div>

    </div>';
    }



    //end 


    // Fetch appointments
    public function abp_render_appointments()
    {
        global $wpdb;

        $appointments_table = $wpdb->prefix . 'appointment_booking';

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $appointments_table)) != $appointments_table) {
            echo '<p>No appointments found or table missing.</p>';
            return;
        }

        if (isset($_POST['update_appointment_status']) && isset($_POST['appointment_id']) && isset($_POST['appointment_status'])) {

            // Verify nonce
            if (!isset($_POST['appointment_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['appointment_nonce'])), 'update_appointment_status_nonce')) {
                die('Nonce verification failed.');
            }

            $appointment_id = intval($_POST['appointment_id']);
            $new_status = sanitize_text_field(wp_unslash($_POST['appointment_status']));

            $wpdb->update(
                $appointments_table,
                ['status' => $new_status],
                ['id' => $appointment_id],
                ['%s'],
                ['%d']
            );

            echo '<div class="updated notice is-dismissible"><p>Appointment status updated.</p></div>';
        }

        if (isset($_POST['delete_appointment']) && isset($_POST['appointment_id'])) {
            $appointment_id = intval($_POST['appointment_id']);

            $wpdb->delete(
                $appointments_table,
                ['id' => $appointment_id],
                ['%d']
            );

            echo '<div class="updated notice is-dismissible"><p>Appointment deleted successfully.</p></div>';
        }

        $appointments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}appointment_booking");
        if (empty($appointments)) {
            echo '<p>No appointments found.</p>';
            return;
        }

        ?>
        <h2>Appointments</h2>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Service</th>
                    <th>Booking Date</th>
                    <th>Booking Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo esc_html($appointment->id); ?></td>
                        <td><?php echo esc_html($appointment->name); ?></td>
                        <td><?php echo esc_html($appointment->service_id); ?></td> <!-- Service ID for now -->
                        <td><?php echo esc_html($appointment->booking_date); ?></td>
                        <td><?php echo esc_html($appointment->booking_time); ?></td>
                        <td>
                            <form method="POST">
                                <?php wp_nonce_field('update_appointment_status_nonce', 'appointment_nonce'); ?>
                                <!-- Add nonce field -->
                                <select name="appointment_status">
                                    <option value="pending" <?php selected($appointment->status, 'pending'); ?>>Pending</option>
                                    <option value="approved" <?php selected($appointment->status, 'approved'); ?>>Approved</option>
                                    <option value="canceled" <?php selected($appointment->status, 'canceled'); ?>>Canceled</option>
                                </select>
                        </td>
                        <td>
                            <input type="hidden" name="appointment_id" value="<?php echo esc_html($appointment->id); ?>">
                            <button type="submit" name="update_appointment_status" class="button-primary">Update</button>
                            <button type="submit" name="delete_appointment" class="button-secondary"
                                onclick="return confirm('Are you sure you want to delete this appointment?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }


    public function abp_render_customers()
    {
        global $wpdb;

        // Fetch all users
        $users = $wpdb->get_results("
        SELECT u.ID, u.user_login, u.user_email, u.display_name, COUNT(a.id) as total_appointments
        FROM {$wpdb->users} u
        LEFT JOIN {$wpdb->prefix}appointment_booking a ON u.ID = a.user_id
        GROUP BY u.ID
    ");

        
        if ($users) {
            echo '<table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Display Name</th>
                    <th>Total Appointments</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($users as $user) {
                echo '<tr>
                <td>' . esc_html($user->ID) . '</td>
                <td>' . esc_html($user->user_login) . '</td>
                <td>' . esc_html($user->user_email) . '</td>
                <td>' . esc_html($user->display_name) . '</td>
                <td>' . esc_html($user->total_appointments) . '</td>
            </tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p>No customers found.</p>';
        }
    }

    //end

}

new ABP_Admin();
?>