<?php
$pageTitle = "Contact Us - KBMO Center";
include 'includes/header.php';

// Database configuration
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $service_interest = isset($_POST['service_interest']) ? $_POST['service_interest'] : '';

    // Validate required fields
    $errors = [];

    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required";
    }

    if (empty($message)) {
        $errors[] = "Message is required";
    }

    if (empty($errors)) {
        try {
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, service_interest, ip_address, user_agent) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $name,
                $email,
                $phone,
                $subject,
                $message,
                $service_interest,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);

            // Send email notification (you'll need to configure this for your server)
            $to = "Kbmocenter@gmail.com";
            $email_subject = "New Contact Form Submission: " . $subject;
            $email_body = "
                New contact form submission from KBMO Center website:
                
                Name: $name
                Email: $email
                Phone: $phone
                Service Interest: $service_interest
                
                Message:
                $message
                
                ---
                Sent from: {$_SERVER['REMOTE_ADDR']}
                User Agent: {$_SERVER['HTTP_USER_AGENT']}
            ";

            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";

            // Uncomment the line below to actually send emails (configure your server first)
            // mail($to, $email_subject, $email_body, $headers);

            $success = "Thank you for your message, $name! We will get back to you within 24 hours.";

            // Clear form fields
            $name = $email = $phone = $subject = $message = $service_interest = '';
        } catch (PDOException $e) {
            $errors[] = "Sorry, there was an error sending your message. Please try again.";
        }
    }
}

// Get services for dropdown
$services_stmt = $pdo->query("SELECT name FROM services");
$services = $services_stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="contact-hero">
    <div id="particles-js"></div>
    <div class="container">
        <div class="row align-items-center min-vh-80">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4 animate-fade-in">Contact Us</h1>
                <p class="lead mb-4 animate-slide-up">Get in touch with our research support team. We're here to help with your research needs.</p>
                <div class="animate-bounce-in">
                    <a href="#contact-form" class="btn btn-light btn-lg me-3 scroll-to">Send Message</a>
                    <a href="#visit-us" class="btn btn-outline-light btn-lg scroll-to">Visit Us</a>
                </div>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <div class="mouse">
            <div class="wheel"></div>
        </div>
    </div>
</section>

<!-- Quick Contact Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up">
                <div class="quick-contact-item">
                    <i class="fas fa-clock fa-2x text-primary mb-3"></i>
                    <h5 class="fw-bold">Working Hours</h5>
                    <p class="text-muted mb-1">Mon - Fri: 8:00 AM - 6:00 PM</p>
                    <p class="text-muted mb-0">Sat: 9:00 AM - 2:00 PM</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="quick-contact-item">
                    <i class="fas fa-phone fa-2x text-primary mb-3"></i>
                    <h5 class="fw-bold">Call Us</h5>
                    <p class="text-muted mb-1">+256 771 200 234</p>
                    <p class="text-muted mb-0">+256 700 123 456</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="quick-contact-item">
                    <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                    <h5 class="fw-bold">Email Us</h5>
                    <p class="text-muted mb-1">Kbmocenter@gmail.com</p>
                    <p class="text-muted mb-0">info@kbmocenter.com</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="quick-contact-item">
                    <i class="fas fa-map-marker-alt fa-2x text-primary mb-3"></i>
                    <h5 class="fw-bold">Visit Us</h5>
                    <p class="text-muted mb-0">Pece-Laroo, Gulu City</p>
                    <p class="text-muted mb-0">Uganda</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Info Section -->
<section class="py-5" id="contact-form">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-5 mb-lg-0">
                <div class="card border-0 shadow-lg" data-aos="fade-right">
                    <div class="card-header bg-primary text-white py-4">
                        <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Send Us a Message</h4>
                    </div>
                    <div class="card-body p-5">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php foreach ($errors as $error): ?>
                                    <div><?php echo $error; ?></div>
                                <?php endforeach; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="contactForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-semibold">Full Name *</label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name"
                                        value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-semibold">Email Address *</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email"
                                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                    <input type="tel" class="form-control form-control-lg" id="phone" name="phone"
                                        value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="service_interest" class="form-label fw-semibold">Service Interest</label>
                                    <select class="form-select form-control-lg" id="service_interest" name="service_interest">
                                        <option value="">Select a service...</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?php echo htmlspecialchars($service['name']); ?>"
                                                <?php echo (isset($service_interest) && $service_interest == $service['name']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($service['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label fw-semibold">Subject *</label>
                                <input type="text" class="form-control form-control-lg" id="subject" name="subject"
                                    value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="message" class="form-label fw-semibold">Your Message *</label>
                                <textarea class="form-control form-control-lg" id="message" name="message" rows="6"
                                    placeholder="Please describe your research needs or inquiry in detail..." required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg py-3">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div data-aos="fade-left">
                    <!-- Contact Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4"><i class="fas fa-info-circle text-primary me-2"></i>Contact Information</h5>

                            <div class="contact-info-item d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-primary fa-lg"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold mb-1">Our Location</h6>
                                    <p class="text-muted mb-0">Pece-Laroo Division<br>Near Pece Prison<br>Gulu City, Uganda</p>
                                </div>
                            </div>

                            <div class="contact-info-item d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-phone text-primary fa-lg"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold mb-1">Call Us</h6>
                                    <p class="text-muted mb-1">+256 771 200 234</p>
                                    <p class="text-muted mb-0">+256 700 123 456</p>
                                </div>
                            </div>

                            <div class="contact-info-item d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-envelope text-primary fa-lg"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold mb-1">Email Us</h6>
                                    <p class="text-muted mb-1">Kbmocenter@gmail.com</p>
                                    <p class="text-muted mb-0">info@kbmocenter.com</p>
                                </div>
                            </div>

                            <div class="contact-info-item d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock text-primary fa-lg"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold mb-1">Working Hours</h6>
                                    <p class="text-muted mb-1">Monday - Friday: 8:00 AM - 6:00 PM</p>
                                    <p class="text-muted mb-0">Saturday: 9:00 AM - 2:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Support -->
                    <div class="card border-0 bg-warning bg-opacity-10">
                        <div class="card-body p-4 text-center">
                            <i class="fas fa-life-ring fa-2x text-warning mb-3"></i>
                            <h6 class="fw-bold mb-2">Urgent Research Support?</h6>
                            <p class="text-muted small mb-3">Need immediate assistance with your research project?</p>
                            <a href="tel:+256771200234" class="btn btn-warning btn-sm">
                                <i class="fas fa-phone me-1"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5 bg-light" id="visit-us">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary mb-3">Visit Us</span>
            <h2 class="fw-bold mb-3">Find Our Location</h2>
            <p class="text-muted lead">Visit our office in Gulu City for face-to-face consultations</p>
        </div>

        <div class="row">
            <div class="col-12" data-aos="zoom-in">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div class="map-container">
                            <!-- Google Maps Embed -->
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.123456789012!2d32.3456789!3d2.1234567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMsKwMDcnMjguNCJOIDMywrAyMCc0OC40IkU!5e0!3m2!1sen!2sug!4v1234567890"
                                width="100%"
                                height="450"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                            <div class="map-overlay">
                                <div class="map-info-card">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i>KBMO Center</h6>
                                    <p class="text-muted small mb-2">Pece-Laroo Division</p>
                                    <p class="text-muted small mb-0">Near Pece Prison, Gulu City</p>
                                    <p class="text-muted small mb-0">Northern Uganda</p>
                                    <div class="mt-3">
                                        <a href="https://maps.google.com/?q=Pece+Laroo+Gulu+City+Uganda" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="fas fa-directions me-1"></i>Get Directions
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Details -->
        <div class="row mt-5">
            <div class="col-md-4 mb-4" data-aos="fade-up">
                <div class="text-center">
                    <i class="fas fa-car fa-2x text-primary mb-3"></i>
                    <h5 class="fw-bold">By Road</h5>
                    <p class="text-muted">Located along Gulu-Kampala highway, easily accessible by public transport or private vehicle.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="text-center">
                    <i class="fas fa-bus fa-2x text-primary mb-3"></i>
                    <h5 class="fw-bold">Public Transport</h5>
                    <p class="text-muted">Take a boda-boda or taxi to Pece-Laroo division. We're near Pece Prison.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center">
                    <i class="fas fa-parking fa-2x text-primary mb-3"></i>
                    <h5 class="fw-bold">Parking</h5>
                    <p class="text-muted">Ample parking space available for visitors and clients.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5" data-aos="fade-up">
                    <span class="badge bg-primary mb-3">FAQ</span>
                    <h2 class="fw-bold mb-3">Frequently Asked Questions</h2>
                    <p class="text-muted lead">Quick answers to common questions</p>
                </div>

                <div class="accordion" id="contactFAQ" data-aos="fade-up">
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                What are your response times?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body text-muted">
                                We typically respond to all inquiries within 24 hours during business days. For urgent matters, please call us directly.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you offer free consultations?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body text-muted">
                                Yes! We offer free initial consultations to discuss your research needs and provide guidance on the best approach.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I visit your office without an appointment?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body text-muted">
                                While walk-ins are welcome, we recommend scheduling an appointment to ensure we can dedicate proper time to address your needs.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What information should I include in my inquiry?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body text-muted">
                                Please include your research topic, academic level, deadline, specific requirements, and any relevant documents to help us provide the best assistance.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h3 class="fw-bold mb-3">Ready to Start Your Research Project?</h3>
                <p class="mb-0 lead">Contact us today and let's discuss how we can help you achieve your research goals.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="tel:+256771200234" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-phone me-2"></i>Call Now
                </a>
                <a href="#contact-form" class="btn btn-outline-light btn-lg scroll-to">Send Message</a>
            </div>
        </div>
    </div>
</section>

<style>
    .contact-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/backg.jpg') center/cover;
        color: white;
        padding: 120px 0;
        position: relative;
        overflow: hidden;
    }

    #particles-js {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
    }

    .min-vh-80 {
        min-height: 80vh;
        position: relative;
        z-index: 2;
    }

    .quick-contact-item {
        padding: 30px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease;
    }

    .quick-contact-item:hover {
        transform: translateY(-5px);
    }

    .contact-info-item {
        padding: 15px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .contact-info-item:last-child {
        border-bottom: none;
    }

    .map-container {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
    }

    .map-overlay {
        position: absolute;
        top: 20px;
        left: 20px;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        max-width: 250px;
    }

    /* Animations */
    .animate-fade-in {
        animation: fadeIn 1s ease-in;
    }

    .animate-slide-up {
        animation: slideUp 1s ease-out 0.5s both;
    }

    .animate-bounce-in {
        animation: bounceIn 1s ease-out 1s both;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes bounceIn {
        0% {
            opacity: 0;
            transform: scale(0.3);
        }

        50% {
            opacity: 1;
            transform: scale(1.05);
        }

        70% {
            transform: scale(0.9);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Scroll Indicator */
    .scroll-indicator {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
    }

    .mouse {
        width: 24px;
        height: 40px;
        border: 2px solid white;
        border-radius: 12px;
        position: relative;
    }

    .wheel {
        width: 4px;
        height: 8px;
        background: white;
        border-radius: 2px;
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        animation: scroll 2s infinite;
    }

    @keyframes scroll {
        0% {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        100% {
            transform: translateX(-50%) translateY(16px);
            opacity: 0;
        }
    }

    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #0d6efd;
    }

    @media (max-width: 768px) {
        .contact-hero {
            padding: 80px 0;
            text-align: center;
        }

        .display-4 {
            font-size: 2.5rem;
        }

        .map-overlay {
            position: relative;
            top: auto;
            left: auto;
            max-width: none;
            margin: 20px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Initialize Particles.js
        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 60,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: "#ffffff"
                },
                shape: {
                    type: "circle"
                },
                opacity: {
                    value: 0.5,
                    random: true
                },
                size: {
                    value: 3,
                    random: true
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: "#ffffff",
                    opacity: 0.4,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: "none",
                    random: true,
                    straight: false,
                    out_mode: "out",
                    bounce: false
                }
            },
            interactivity: {
                detect_on: "canvas",
                events: {
                    onhover: {
                        enable: true,
                        mode: "repulse"
                    },
                    onclick: {
                        enable: true,
                        mode: "push"
                    },
                    resize: true
                }
            }
        });

        // Smooth scrolling
        document.querySelectorAll('.scroll-to').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Form validation enhancement
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                const requiredFields = contactForm.querySelectorAll('[required]');
                let valid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    // Scroll to first invalid field
                    const firstInvalid = contactForm.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstInvalid.focus();
                    }
                }
            });
        }

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }
            }, 5000);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>