<?php
if (!class_exists('Woo_Nehabi_Appointment')) {
    class Woo_Nehabi_Appointment
    {
        public function __construct()
        {
            add_action('admin_menu', array($this, 'add_menu_page'));
            add_action('wp_ajax_generate_qr', array($this, 'generate_qr_code'));
            add_action('wp_ajax_send_qr_code', array($this, 'send_qr_code'));
            add_action('wp_ajax_download_qr_code', array($this, 'download_qr_code'));
            add_action('wp_ajax_update_appointment', array($this, 'update_appointment'));
        }

             public function add_menu_page()
            {
                add_submenu_page(
                    'woocommerce',
                    'Appointments',
                    'Appointments',
                    'manage_options',
                    'woo-nehabi-appointments',
                    array($this, 'render_admin_page')
                );
            }

    
            public function render_admin_page()
            {
                global $wpdb;
            
                // Pagination
                $appointments_per_page = 5; // Adjust the number of appointments per page
                $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
                $offset = ($current_page - 1) * $appointments_per_page;
            
                // Fetch appointments with pagination
                $appointments = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}appointments LIMIT %d OFFSET %d", $appointments_per_page, $offset)
                );
            
                // Get the total number of appointments
                $total_appointments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}appointments");
            
                // Calculate total pages
                $total_pages = ceil($total_appointments / $appointments_per_page);
            
                wp_enqueue_style('bootstrap-css');
                wp_enqueue_script('bootstrap-js');
                wp_enqueue_style('font-awesome');
                
                $completed_orders = wc_get_orders(array(
                    'status' => 'completed',
                    'limit' => -1, 
                ));
            
                ?>
                <div class="container-fluid">
                    <br>
                    <header>
                        <h2 class="bg-white">Nehabi Order - Appointments</h2>
                    </header>
                    <form id="appointment-form">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="customer">Select Customer</label>
                                <select id="customer" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    <?php foreach ($completed_orders as $order) : ?>
                                        <option value="<?php echo esc_attr($order->get_id()); ?>" data-name="<?php echo esc_attr($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?>" data-email="<?php echo esc_attr($order->get_billing_email()); ?>">
                                            <?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?> - <?php echo esc_html($order->get_billing_email()); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="appointment_date">Appointment Date</label>
                                <input type="date" id="appointment_date" class="form-control" required />
                            </div>
            
                            <div class="col-md-4 text-center">
                                <button type="submit" class="btn btn-primary" style="margin-top: 30px;">
                                    <i class="fas fa-qrcode"></i> Generate QR
                                </button>
                            </div>
                        </div>
                    </form>
            
                    <!-- Appointment Table -->
                    <div id="appointment-table" class="table-responsive mt-4">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer Name</th>
                                    <th>Email</th>
                                    <th>Appointment Date</th>
                                    <th>QR Code</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment) : ?>
                                    <tr id="appointment_<?php echo $appointment->customer_id; ?>">
                                        <td><?php echo $appointment->customer_id; ?></td>
                                        <td><?php echo esc_html($appointment->customer_name); ?></td>
                                        <td><?php echo esc_html($appointment->customer_email); ?></td>
                                        <td id="appointment-date-<?php echo $appointment->customer_id; ?>"><?php 
                                            echo esc_html(date_i18n('l, F j, Y', strtotime($appointment->appointment_date))); 
                                            ?></td>
                                        <td><img src="<?php echo esc_url($appointment->qr_code_url); ?>" alt="QR Code" style="width: 50px;"></td>
                                        <td>
                                            <button class="btn btn-success send-qr" data-id="<?php echo $appointment->customer_id; ?>" data-name="<?php echo esc_attr($appointment->customer_name); ?>" data-email="<?php echo esc_attr($appointment->customer_email); ?>" data-qr="<?php echo esc_url($appointment->qr_code_url); ?>">
                                                <i class="fas fa-paper-plane"></i>  
                                            </button>
            
                                            <button class="btn btn-info download-qr" data-id="<?php echo $appointment->customer_id; ?>" data-qr="<?php echo esc_url($appointment->qr_code_url); ?>">
                                                <i class="fas fa-download"></i>  
                                            </button>
            
                                            <button class="btn btn-warning update-appointment" data-id="<?php echo $appointment->customer_id; ?>" data-name="<?php echo esc_attr($appointment->customer_name); ?>" data-email="<?php echo esc_attr($appointment->customer_email); ?>" data-qr="<?php echo esc_url($appointment->qr_code_url); ?>" data-date="<?php echo esc_attr($appointment->appointment_date); ?>">
                                                <i class="fas fa-edit"></i>  
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
            
                    <!-- Pagination -->
                    <div class="pagination-container text-center">
                        <ul class="pagination">
                            <?php if ($current_page > 1) : ?>
                                <li class="page-item"><a class="page-link" href="?page=1&paged=1">&laquo; First</a></li>
                                <li class="page-item"><a class="page-link" href="?paged=<?php echo $current_page - 1; ?>">Previous</a></li>
                            <?php endif; ?>
            
                            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?paged=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
            
                            <?php if ($current_page < $total_pages) : ?>
                                <li class="page-item"><a class="page-link" href="?paged=<?php echo $current_page + 1; ?>">Next</a></li>
                                <li class="page-item"><a class="page-link" href="?paged=<?php echo $total_pages; ?>">Last &raquo;</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

            <script>
                jQuery(document).ready(function($) {
                    $('#appointment-form').on('submit', function(e) {
                        e.preventDefault();

                        var customerId = $('#customer').val();
                        var customerName = $('#customer option:selected').data('name');
                        var customerEmail = $('#customer option:selected').data('email');
                        var appointmentDate = $('#appointment_date').val();

                        if (!customerId || !appointmentDate) {
                            alert("Please select a customer and appointment date.");
                            return;
                        }

                        $.post(ajaxurl, {
                            action: "generate_qr",
                            customer_id: customerId,
                            customer_name: customerName,
                            customer_email: customerEmail,
                            appointment_date: appointmentDate
                        }, function(response) {
                            try {
                                var data = JSON.parse(response);
                                if (data.success) {
                                    var newRow = `
                                        <tr id="appointment_${customerId}">
                                            <td>${customerId}</td>
                                            <td>${customerName}</td>
                                            <td>${customerEmail}</td>
                                            <td id="appointment-date-${customerId}">${appointmentDate}</td>
                                            <td><img src="${data.qr_code}" alt="QR Code" style="width: 60px;"></td>
                                            <td>
                                                <button class="btn btn-success send-qr" data-id="${customerId}" data-name="${customerName}" data-email="${customerEmail}" data-qr="${data.qr_code}">Send QR</button>
                                                <button class="btn btn-info download-qr" data-id="${customerId}" data-qr="${data.qr_code}">Download QR</button>
                                                <button class="btn btn-warning update-appointment" data-id="${customerId}" data-name="${customerName}" data-email="${customerEmail}" data-qr="${data.qr_code}" data-date="${appointmentDate}">Update</button>
                                            </td>
                                        </tr>
                                    `;
                                    $('#appointment-table tbody').append(newRow);
                                } else {
                                    alert("Error: " + data.message);
                                }
                            } catch (error) {
                                alert("Invalid response from server.");
                                console.error("Error:", error);
                            }
                        });
                    });

                    // Send QR Button Click
                    $(document).on('click', '.send-qr', function() {
                        var id = $(this).data("id");
                        var name = $(this).data("name");
                        var email = $(this).data("email");
                        var qr_code_url = $(this).data("qr");

                        $.post(ajaxurl, {
                            action: "send_qr_code",
                            customer_id: id,
                            customer_name: name,
                            customer_email: email,
                            qr_code_url: qr_code_url
                        }, function(response) {
                            try {
                                var data = JSON.parse(response);
                                if (data.success) {
                                    alert("QR Code sent successfully!");
                                } else {
                                    alert("Error: " + data.message);
                                }
                            } catch (error) {
                                alert("Invalid response from server.");
                                console.error("Error:", error);
                            }
                        });
                    });

                    // Download QR Button Click
                    $(document).on('click', '.download-qr', function() {
                        var qr_code_url = $(this).data("qr");
                        var a = document.createElement('a');
                        a.href = qr_code_url;
                        a.download = 'qr_code.png';
                        a.click();
                    });

                    // Update Appointment Button Click
                    $(document).on('click', '.update-appointment', function() {
                        var customerId = $(this).data("id");
                        var customerName = $(this).data("name");
                        var customerEmail = $(this).data("email");
                        var currentDate = $(this).data("date");
                        var qrCodeUrl = $(this).data("qr");

                        // Populate the update form with the current details
                        $('#customer').val(customerId).trigger('change');
                        $('#appointment_date').val(currentDate);

                        // Show a button for updating the appointment
                        $('#appointment-form').append(`
                            <button type="button" id="update-appointment-btn" class="btn btn-warning">Update Appointment</button>
                        `);

                        // When the Update Appointment button is clicked
                        $('#update-appointment-btn').on('click', function(e) {
                            e.preventDefault();

                            var updatedDate = $('#appointment_date').val();

                            if (!updatedDate) {
                                alert("Please select an appointment date.");
                                return;
                            }

                            $.post(ajaxurl, {
                                action: "update_appointment",
                                customer_id: customerId,
                                customer_name: customerName,
                                customer_email: customerEmail,
                                appointment_date: updatedDate,
                                qr_code_url: qrCodeUrl
                            }, function(response) {
                                try {
                                    var data = JSON.parse(response);
                                    if (data.success) {
                                        // Update the table row with new appointment date and QR code
                                        $('#appointment-date-' + customerId).text(updatedDate);
                                        var updatedQRUrl = data.qr_code;
                                        $('#appointment_' + customerId + ' img').attr('src', updatedQRUrl);
                                        alert("Appointment updated successfully!");
                                        $('#update-appointment-btn').remove();
                                    } else {
                                        alert("Error: " + data.message);
                                    }
                                } catch (error) {
                                    alert("Invalid response from server.");
                                    console.error("Error:", error);
                                }
                            });
                        });
                    });
                });
            </script>

            <?php
        }

        // Generate QR Code
        public function generate_qr_code()
        {
            if (!isset($_POST['customer_id'], $_POST['customer_name'], $_POST['customer_email'], $_POST['appointment_date'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                wp_die();
            }

            global $wpdb;
            $customer_id = intval($_POST['customer_id']);
            $name = sanitize_text_field($_POST['customer_name']);
            $email = sanitize_email($_POST['customer_email']);
            $date = sanitize_text_field($_POST['appointment_date']);
            $formatted_date = DateTime::createFromFormat('Y-m-d', $date)->format('l, F j, Y');

            $qr_data = "Dear, $name\nYour Appointment Date is $formatted_date\nThank You For Working With us.";

            // Include QR Code Library
            require_once WOO_NEHABI_PATH . 'libs/phpqrcode/qrlib.php';

            // Ensure QR code directory exists
            $qr_code_dir = WOO_NEHABI_PATH . "qrcodes/";
            if (!file_exists($qr_code_dir)) {
                mkdir($qr_code_dir, 0755, true);
            }

            // Format file name with customer name (remove spaces & special characters)
            $safe_name = preg_replace('/[^A-Za-z0-9]/', '_', $name); // Replace special characters with _
            $qr_filename = "qr_{$safe_name}_{$customer_id}.png";  // e.g., qr_John_Doe_123.png
            $qr_code_path = $qr_code_dir . $qr_filename;

            // Check if the QR code already exists and delete it if necessary
            if (file_exists($qr_code_path)) {
                unlink($qr_code_path);  // Delete the existing QR code
            }

            // Generate new QR code
            QRcode::png($qr_data, $qr_code_path, QR_ECLEVEL_L, 4);

            // Get the URL of the QR code
            $qr_code_url = content_url('plugins/woonehabi/qrcodes/' . $qr_filename);

            // Save appointment data into the database
            $table_name = $wpdb->prefix . 'appointments';
            $wpdb->insert(
                $table_name,
                [
                    'customer_id' => $customer_id,
                    'customer_name' => $name,
                    'customer_email' => $email,
                    'appointment_date' => $date,
                    'qr_code_url' => $qr_code_url
                ]
            );

            echo json_encode(['success' => true, 'qr_code' => $qr_code_url]);
            wp_die();
        }

        // Update Appointment
        public function update_appointment()
        {
            if (!isset($_POST['customer_id'], $_POST['customer_name'], $_POST['customer_email'], $_POST['appointment_date'], $_POST['qr_code_url'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                wp_die();
            }

            global $wpdb;
            $customer_id = intval($_POST['customer_id']);
            $name = sanitize_text_field($_POST['customer_name']);
            $email = sanitize_email($_POST['customer_email']);
            $date = sanitize_text_field($_POST['appointment_date']);
            $qr_code_url = sanitize_text_field($_POST['qr_code_url']);

            $formatted_date = DateTime::createFromFormat('Y-m-d', $date)->format('l, F j, Y');

            // Generate new QR code data
            $qr_data = "Dear, $name\nYour Appointment Date is $formatted_date\nThank You For Working With us.";

            // Include QR Code Library
            require_once WOO_NEHABI_PATH . 'libs/phpqrcode/qrlib.php';

            // Generate a new file name and QR code
            $safe_name = preg_replace('/[^A-Za-z0-9]/', '_', $name);
            $qr_filename = "qr_{$safe_name}_{$customer_id}.png";
            $qr_code_path = WOO_NEHABI_PATH . "qrcodes/" . $qr_filename;

            // Generate new QR code
            QRcode::png($qr_data, $qr_code_path, QR_ECLEVEL_L, 4);

            // Get the URL of the new QR code
            $new_qr_code_url = content_url('plugins/woonehabi/qrcodes/' . $qr_filename);

            // Update appointment record in the database
            $table_name = $wpdb->prefix . 'appointments';
            $wpdb->update(
                $table_name,
                [
                    'appointment_date' => $date,
                    'qr_code_url' => $new_qr_code_url
                ],
                ['customer_id' => $customer_id],
                ['%s', '%s'],
                ['%d']
            );

            echo json_encode(['success' => true, 'qr_code' => $new_qr_code_url]);
            wp_die();
        }

        public function send_qr_code()
        {
            if (!isset($_POST['customer_id'], $_POST['customer_name'], $_POST['customer_email'], $_POST['qr_code_url'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                wp_die();
            }

            $customer_id = intval($_POST['customer_id']);
            $name = sanitize_text_field($_POST['customer_name']);
            $email = sanitize_email($_POST['customer_email']);
            $qr_code_url = sanitize_text_field($_POST['qr_code_url']);

            // Check if the QR code file exists on the server
            if (!file_exists(ABSPATH . str_replace(home_url(), '', $qr_code_url))) {
                echo json_encode(['success' => false, 'message' => 'QR code image not found.']);
                wp_die();
            }

            // Send Email with QR code
            $subject = "Your Appointment QR Code";
            $message = "Dear $name,<br><br>Here is your appointment QR code:<br><br>";
            $message .= "<img src='$qr_code_url' alt='QR Code'><br><br>";
            $message .= "<a href='$qr_code_url' target='_blank'>View QR Code</a> <br><br> Thank You For Working With Us.";
            $headers = array('Content-Type: text/html; charset=UTF-8');

            if (wp_mail($email, $subject, $message, $headers)) {
                echo json_encode(['success' => true, 'message' => 'QR Code sent successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
            }

            wp_die();
        }

        public function download_qr_code()
        {
            if (!isset($_POST['qr_code_url'])) {
                echo json_encode(['success' => false, 'message' => 'QR code URL not provided']);
                wp_die();
            }

            $qr_code_url = sanitize_text_field($_POST['qr_code_url']);

            if (file_exists(ABSPATH . str_replace(home_url(), '', $qr_code_url))) {
                $qr_code_path = ABSPATH . str_replace(home_url(), '', $qr_code_url);
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($qr_code_path) . '"');
                header('Content-Length: ' . filesize($qr_code_path));
                readfile($qr_code_path);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'QR code file not found']);
                wp_die();
            }
        }
    }

    //new Woo_Nehabi_Appointment();
}
?>
