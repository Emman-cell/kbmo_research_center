<?php
include 'config/database.php';
$pageTitle = "KBMO Center for Translational Research";
include 'includes/header.php';

// Handle review submission
$submit_review = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    $user_name = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    $service_used = trim($_POST['service_used']);

    if (!empty($user_name) && !empty($rating) && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, user_name, user_email, rating, comment, service_used) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_name, $user_email, $rating, $comment, $service_used]);

        $success_message = "Thank you for your review! It will be published after approval.";
    } else {
        $error_message = "Please fill all required fields.";
    }
}

// Get approved reviews for display
$reviews_stmt = $pdo->query("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC LIMIT 6");
$reviews = $reviews_stmt->fetchAll();

// Get services for dropdown
$services_stmt = $pdo->query("SELECT name FROM services");
$services_list = $services_stmt->fetchAll();
?>

<!-- Hero Section with Particles Background -->
<section class="hero-section">
    <div id="particles-js"></div>
    <div class="container">
        <div class="row align-items-center min-vh-80" style="justify-content: center;">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4 animate-fade-in">Center of Expertise in Clinical and Public Health Research, Epidemiology and Biostatistic</h1>
                <p class="lead mb-4 animate-slide-up">We provide comprehensive research assistance from concept development to publication, helping researchers and academics achieve excellence in their scholarly pursuits.</p>
                <div class="d-flex flex-wrap gap-3 animate-bounce-in" style="justify-content: center;">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-light btn-lg px-4 py-3 glow-on-hover">Get Started</a>
                        <a href="services.php" class="btn btn-outline-light btn-lg px-4 py-3">Our Services</a>
                    <?php else: ?>
                        <a href="<?php echo $_SESSION['user_type'] == 'admin' ? 'admin_dashboard.php' : 'dashboard.php'; ?>" class="btn btn-light btn-lg px-4 py-3 glow-on-hover">
                            Go to Dashboard
                        </a>
                        <a href="upload_document.php" class="btn btn-outline-light btn-lg px-4 py-3">Upload Document</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Animated Image Carousel Section - ADDED -->
            <div class="col-lg-4 mt-5 mt-lg-0">
                <div class="research-carousel" data-aos="fade-left" data-aos-delay="500">
                    <div class="carousel-container">
                        <div class="carousel-slide active">
                            <img src="images/research1.jpg" alt="Academic Research" class="carousel-image">
                            <div class="carousel-caption">
                                <h6>Academic Excellence</h6>
                                <small>Quality Research Support</small>
                            </div>
                        </div>
                        <div class="carousel-slide">
                            <img src="images/research2.jpg" alt="Data Analysis" class="carousel-image">
                            <div class="carousel-caption">
                                <h6>Data Analysis</h6>
                                <small>Statistical Expertise</small>
                            </div>
                        </div>
                        <div class="carousel-slide">
                            <img src="images/research3.jpg" alt="Thesis Writing" class="carousel-image">
                            <div class="carousel-caption">
                                <h6>Thesis Writing</h6>
                                <small>PhD & Masters Support</small>
                            </div>
                        </div>
                        <div class="carousel-slide">
                            <img src="images/research4.jpg" alt="Publication Support" class="carousel-image">
                            <div class="carousel-caption">
                                <h6>Journal Publication</h6>
                                <small>High-Impact Journals</small>
                            </div>
                        </div>
                        <div class="carousel-slide">
                            <img src="images/research5.jpg" alt="Research Collaboration" class="carousel-image">
                            <div class="carousel-caption">
                                <h6>Expert Collaboration</h6>
                                <small>Field Specialists</small>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-indicators">
                        <span class="indicator active" data-slide="0"></span>
                        <span class="indicator" data-slide="1"></span>
                        <span class="indicator" data-slide="2"></span>
                        <span class="indicator" data-slide="3"></span>
                        <span class="indicator" data-slide="4"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <div class="mouse">
            <div class="wheel"></div>
        </div>
        <div class="arrow"></div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item" data-aos="fade-up">
                    <h2 class="fw-bold text-primary mb-2" data-count="500">0</h2>
                    <p class="text-muted">Projects Completed</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                    <h2 class="fw-bold text-primary mb-2" data-count="50">0</h2>
                    <p class="text-muted">Expert Researchers</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                    <h2 class="fw-bold text-primary mb-2" data-count="95">0</h2>
                    <p class="text-muted">Success Rate</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                    <h2 class="fw-bold text-primary mb-2" data-count="24">24/7</h2>
                    <p class="text-muted">Support Available</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section with Appealing Images - ADDED -->
<section class="py-5 bg-white" id="why-choose-us">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary mb-3">WHY CHOOSE US</span>
            <h2 class="fw-bold mb-3">Excellence in Research Support</h2>
            <p class="text-muted lead">Trusted by researchers worldwide for quality, reliability, and expertise</p>
        </div>

        <div class="row align-items-center">
            <!-- Left Content -->
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="row g-4">
                    <!-- Feature 1 -->
                    <div class="col-md-6" data-aos="fade-right" data-aos-delay="100">
                        <div class="feature-card text-center p-4 h-100">
                            <div class="feature-icon mb-3">
                                <img src="https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80"
                                    alt="Expert Researchers" class="feature-image rounded-circle">
                            </div>
                            <h5 class="fw-bold mb-3">Expert Researchers</h5>
                            <p class="text-muted">PhD-level specialists in various fields with proven track records in academic research and publication.</p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="col-md-6" data-aos="fade-right" data-aos-delay="200">
                        <div class="feature-card text-center p-4 h-100">
                            <div class="feature-icon mb-3">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80"
                                    alt="Quality Assurance" class="feature-image rounded-circle">
                            </div>
                            <h5 class="fw-bold mb-3">Quality Assurance</h5>
                            <p class="text-muted">Rigorous quality checks, plagiarism scanning, and multiple review cycles ensure exceptional work.</p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="col-md-6" data-aos="fade-right" data-aos-delay="300">
                        <div class="feature-card text-center p-4 h-100">
                            <div class="feature-icon mb-3">
                                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80"
                                    alt="On-Time Delivery" class="feature-image rounded-circle">
                            </div>
                            <h5 class="fw-bold mb-3">On-Time Delivery</h5>
                            <p class="text-muted">We understand academic deadlines and guarantee timely delivery without compromising quality.</p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="col-md-6" data-aos="fade-right" data-aos-delay="400">
                        <div class="feature-card text-center p-4 h-100">
                            <div class="feature-icon mb-3">
                                <img src="https://images.unsplash.com/photo-1551836026-d5c0889dd6d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80"
                                    alt="24/7 Support" class="feature-image rounded-circle">
                            </div>
                            <h5 class="fw-bold mb-3">24/7 Support</h5>
                            <p class="text-muted">Round-the-clock customer support to address your queries and provide project updates.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Main Appealing Image -->
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="500">
                <div class="main-feature-image position-relative">
                    <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                        alt="Research Excellence" class="img-fluid rounded-3 shadow-lg">
                    <div class="image-overlay-content">
                        <div class="stats-overlay">
                            <div class="stat-item">
                                <h3 class="fw-bold text-white mb-1">98%</h3>
                                <small class="text-white-50">Client Satisfaction</small>
                            </div>
                            <div class="stat-item">
                                <h3 class="fw-bold text-white mb-1">500+</h3>
                                <small class="text-white-50">Projects Completed</small>
                            </div>
                            <div class="stat-item">
                                <h3 class="fw-bold text-white mb-1">50+</h3>
                                <small class="text-white-50">Expert Researchers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Features Row -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="row g-4">
                    <!-- Additional Feature 1 -->
                    <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="icon-wrapper bg-primary text-white rounded-3 p-3 me-3">
                                    <i class="fas fa-shield-alt fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-2">100% Confidential</h5>
                                <p class="text-muted mb-0">Your work and personal information are completely secure and private.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Feature 2 -->
                    <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="icon-wrapper bg-primary text-white rounded-3 p-3 me-3">
                                    <i class="fas fa-sync-alt fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-2">Free Revisions</h5>
                                <p class="text-muted mb-0">Unlimited revisions within 30 days to ensure complete satisfaction.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Feature 3 -->
                    <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="icon-wrapper bg-primary text-white rounded-3 p-3 me-3">
                                    <i class="fas fa-graduation-cap fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-2">Academic Excellence</h5>
                                <p class="text-muted mb-0">Work that meets international academic standards and guidelines.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5 bg-light" id="services">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary mb-3">Our Services</span>
            <h2 class="fw-bold mb-3">Comprehensive Research Support</h2>
            <p class="text-muted lead">End-to-end research assistance for academics and professionals</p>
        </div>

        <div class="row g-4">
            <?php
            $stmt = $pdo->query("SELECT * FROM services LIMIT 4");
            $services = $stmt->fetchAll();

            foreach ($services as $service):
            ?>
                <div class="col-md-6 col-lg-3" data-aos="flip-left" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="card service-card h-100 border-0 shadow-hover">
                        <div class="card-body text-center p-4">
                            <div class="service-icon mb-4">
                                <?php
                                $icons = [
                                    'Concept Note Drafting' => 'fa-lightbulb',
                                    'Proposal Writing' => 'fa-file-alt',
                                    'Thesis Writing' => 'fa-graduation-cap',
                                    'Data Analysis' => 'fa-chart-bar',
                                    'Manuscript Writing' => 'fa-file-medical',
                                    'Journal Submission' => 'fa-paper-plane',
                                    'Post-Submission Review' => 'fa-comments'
                                ];
                                $icon = $icons[$service['name']] ?? 'fa-cog';
                                ?>
                                <i class="fas <?php echo $icon; ?> fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($service['name']); ?></h5>
                            <p class="card-text text-muted mb-4"><?php echo htmlspecialchars($service['description']); ?></p>

                            <!-- Rating Display -->
                            <div class="rating-display mb-3">
                                <?php
                                $avg_rating = $service['average_rating'] ?? 0;
                                $review_count = $service['review_count'] ?? 0;
                                ?>
                                <div class="stars mb-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $avg_rating ? 'text-warning' : 'text-light'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted">(<?php echo $review_count; ?> reviews)</small>
                            </div>

                            <p class="fw-bold text-primary h5 mb-4">$<?php echo number_format($service['price'], 2); ?></p>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="login.php" class="btn btn-primary w-100 pulse-on-hover">Login to Order</a>
                            <?php else: ?>
                                <a href="upload_document.php?service_id=<?php echo $service['id']; ?>" class="btn btn-primary w-100 pulse-on-hover">Get Service</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="services.php" class="btn btn-outline-primary btn-lg px-5">View All Services</a>
            <?php else: ?>
                <a href="upload_document.php" class="btn btn-outline-primary btn-lg px-5">Upload New Document</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Interactive Review Section -->
<section class="py-5 bg-white" id="reviews">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary mb-3">Client Reviews</span>
            <h2 class="fw-bold mb-3">What Our Clients Say</h2>
            <p class="text-muted lead">Join thousands of satisfied researchers</p>
        </div>

        <!-- Review Cards -->
        <div class="row mb-5">
            <?php foreach ($reviews as $review): ?>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="zoom-in">
                    <div class="card review-card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                    <?php echo strtoupper(substr($review['user_name'], 0, 2)); ?>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($review['user_name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($review['service_used']); ?></small>
                                </div>
                            </div>
                            <div class="stars mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-light'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted mb-0">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                            <small class="text-muted d-block mt-2">
                                <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Review Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg" data-aos="fade-up">
                    <div class="card-header bg-primary text-white py-4">
                        <h4 class="mb-0 text-center"><i class="fas fa-star me-2"></i>Share Your Experience</h4>
                    </div>
                    <div class="card-body p-5">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form id="reviewForm" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="user_name" class="form-label fw-semibold">Your Name *</label>
                                    <input type="text" class="form-control form-control-lg" id="user_name" name="user_name"
                                        value="<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="user_email" class="form-label fw-semibold">Your Email</label>
                                    <input type="email" class="form-control form-control-lg" id="user_email" name="user_email"
                                        value="<?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="service_used" class="form-label fw-semibold">Service Used</label>
                                <select class="form-select form-control-lg" id="service_used" name="service_used">
                                    <option value="">Select a service...</option>
                                    <?php foreach ($services_list as $service): ?>
                                        <option value="<?php echo htmlspecialchars($service['name']); ?>">
                                            <?php echo htmlspecialchars($service['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Your Rating *</label>
                                <div class="rating-input">
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="far fa-star" data-rating="<?php echo $i; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="rating" id="rating" required>
                                    <small class="text-muted d-block mt-1">Click on stars to rate</small>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="comment" class="form-label fw-semibold">Your Review *</label>
                                <textarea class="form-control form-control-lg" id="comment" name="comment" rows="4"
                                    placeholder="Share your experience with our services..." required></textarea>
                            </div>

                            <button type="submit" name="submit_review" value="1" class="btn btn-primary btn-lg w-100 py-3">
                                <i class="fas fa-paper-plane me-2"></i>Submit Review
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary mb-3">Process</span>
            <h2 class="fw-bold mb-3">How It Works</h2>
            <p class="text-muted lead">Simple steps to get your research project completed</p>
        </div>

        <div class="row text-center">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="position-relative">
                    <div class="step-number">1</div>
                    <div class="p-4">
                        <div class="step-icon rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-user-plus fa-3x"></i>
                        </div>
                        <h4 class="fw-bold">Register & Login</h4>
                        <p class="text-muted">Create an account and login to access our comprehensive services</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="position-relative">
                    <div class="step-number">2</div>
                    <div class="p-4">
                        <div class="step-icon rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-upload fa-3x"></i>
                        </div>
                        <h4 class="fw-bold">Upload Documents</h4>
                        <p class="text-muted">Submit your research materials, requirements, and specific instructions</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="position-relative">
                    <div class="step-number">3</div>
                    <div class="p-4">
                        <div class="step-icon rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-file-invoice-dollar fa-3x"></i>
                        </div>
                        <h4 class="fw-bold">Make Payment</h4>
                        <p class="text-muted">Secure payment processing for your selected service package</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section
<section class="py-5 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <span class="badge bg-primary mb-3">Contact Us</span>
                <h2 class="fw-bold mb-4">Get In Touch</h2>
                <p class="text-muted mb-4">Have questions? We're here to help with your research needs.</p>

                <div class="contact-info">
                    <div class="d-flex align-items-start mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-primary fa-lg me-3 mt-1"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Location</h5>
                            <p class="text-muted mb-0">Pece-Laroo, Gulu City, Uganda</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-envelope text-primary fa-lg me-3 mt-1"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Email</h5>
                            <p class="text-muted mb-0">Kbmocenter@gmail.com</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-phone text-primary fa-lg me-3 mt-1"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Phone</h5>
                            <p class="text-muted mb-0">0771200234</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-left">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <form id="contactForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-semibold">Your Name</label>
                                    <input type="text" class="form-control form-control-lg" id="name" placeholder="Enter your name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-semibold">Your Email</label>
                                    <input type="email" class="form-control form-control-lg" id="email" placeholder="Enter your email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label fw-semibold">Subject</label>
                                <input type="text" class="form-control form-control-lg" id="subject" placeholder="Enter subject" required>
                            </div>
                            <div class="mb-4">
                                <label for="message" class="form-label fw-semibold">Your Message</label>
                                <textarea class="form-control form-control-lg" id="message" rows="5" placeholder="Enter your message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<?php include 'includes/footer.php'; ?>

<style>
    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/background.png') center/cover;
        color: white;
        padding: 120px 0;
        position: relative;
        overflow: hidden;
    }

    /* Why Choose Us Section Styles - ADDED */
    .feature-card {
        border: 1px solid #e9ecef;
        border-radius: 15px;
        transition: all 0.3s ease;
        background: #fff;
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        border-color: #0d6efd;
    }

    .feature-image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .feature-card:hover .feature-image {
        transform: scale(1.1);
        border-color: #0d6efd;
    }

    .main-feature-image {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
    }

    .main-feature-image img {
        transition: transform 0.5s ease;
        width: 100%;
        height: 500px;
        object-fit: cover;
    }

    .main-feature-image:hover img {
        transform: scale(1.05);
    }

    .image-overlay-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        padding: 30px;
        color: white;
    }

    .stats-overlay {
        display: flex;
        justify-content: space-around;
        text-align: center;
    }

    .stat-item {
        padding: 0 15px;
    }

    .stat-item h3 {
        font-size: 2.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .icon-wrapper {
        transition: all 0.3s ease;
    }

    .d-flex:hover .icon-wrapper {
        transform: rotate(10deg) scale(1.1);
    }

    /* Animation for feature cards */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .feature-card {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .main-feature-image img {
            height: 400px;
        }

        .stats-overlay {
            flex-direction: column;
            gap: 20px;
        }

        .stat-item {
            padding: 10px 0;
        }
    }

    @media (max-width: 767.98px) {
        .feature-image {
            width: 100px;
            height: 100px;
        }

        .main-feature-image img {
            height: 300px;
        }

        .stat-item h3 {
            font-size: 2rem;
        }
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

    /* Interactive Elements */
    .glow-on-hover:hover {
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    .pulse-on-hover:hover {
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .service-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .service-card:hover {
        transform: translateY(-10px) rotate(2deg);
    }

    .shadow-hover:hover {
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
    }

    .review-card {
        transition: transform 0.3s ease;
    }

    .review-card:hover {
        transform: translateY(-5px) scale(1.02);
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

    /* Rating Stars */
    .rating-input .stars {
        cursor: pointer;
        font-size: 1.5rem;
    }

    .rating-input .stars i {
        transition: all 0.2s ease;
    }

    .rating-input .stars i:hover,
    .rating-input .stars i.active {
        color: #ffc107 !important;
        transform: scale(1.2);
    }

    /* Statistics */
    .stat-item h2 {
        font-size: 3rem;
        transition: all 0.3s ease;
    }

    /* Steps */
    .step-number {
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: #0d6efd;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        z-index: 1;
    }

    .step-icon {
        width: 100px;
        height: 100px;
        transition: transform 0.3s ease;
    }

    .step-icon:hover {
        transform: scale(1.1) rotate(10deg);
    }

    /* Avatar */
    .avatar {
        width: 50px;
        height: 50px;
        font-weight: bold;
        font-size: 1.1rem;
    }

    @media (max-width: 768px) {
        .hero-section {
            padding: 80px 0;
            text-align: center;
        }

        .display-4 {
            font-size: 2.5rem;
        }

        .step-icon {
            width: 80px;
            height: 80px;
        }

        .step-icon i {
            font-size: 2rem !important;
        }
    }

    /* Animated Research Carousel Styles - ADDED */
    .research-carousel {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .carousel-container {
        position: relative;
        width: 100%;
        height: 400px;
        overflow: hidden;
        border-radius: 15px;
    }

    .carousel-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transform: scale(0.9);
        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .carousel-slide.active {
        opacity: 1;
        transform: scale(1);
    }

    .carousel-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s ease;
    }

    .carousel-slide.active .carousel-image {
        transform: scale(1.05);
    }

    .carousel-caption {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        color: white;
        padding: 30px 20px 20px;
        transform: translateY(100%);
        transition: transform 0.6s ease 0.3s;
    }

    .carousel-slide.active .carousel-caption {
        transform: translateY(0);
    }

    .carousel-caption h6 {
        font-weight: 700;
        margin-bottom: 5px;
        font-size: 1.1rem;
    }

    .carousel-caption small {
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .carousel-indicators {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }

    .indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.4);
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .indicator.active {
        background: #fff;
        transform: scale(1.2);
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    }

    .indicator:hover {
        background: rgba(255, 255, 255, 0.7);
        transform: scale(1.1);
    }

    /* Floating animation for carousel */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .research-carousel {
        animation: float 6s ease-in-out infinite;
    }

    /* Responsive adjustments for carousel */
    @media (max-width: 991.98px) {
        .research-carousel {
            margin-top: 40px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .carousel-container {
            height: 300px;
        }
    }

    @media (max-width: 575.98px) {
        .carousel-container {
            height: 250px;
        }

        .carousel-caption {
            padding: 20px 15px 15px;
        }

        .carousel-caption h6 {
            font-size: 1rem;
        }

        .carousel-caption small {
            font-size: 0.8rem;
        }
    }
</style>

<script>
    // Initialize AOS
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Initialize Particles.js
        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 80,
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

        // Rating System
        const stars = document.querySelectorAll('.rating-input .stars i');
        const ratingInput = document.getElementById('rating');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                ratingInput.value = rating;

                stars.forEach(s => {
                    if (s.getAttribute('data-rating') <= rating) {
                        s.classList.remove('far');
                        s.classList.add('fas', 'active');
                    } else {
                        s.classList.remove('fas', 'active');
                        s.classList.add('far');
                    }
                });
            });

            star.addEventListener('mouseover', function() {
                const rating = this.getAttribute('data-rating');
                stars.forEach(s => {
                    if (s.getAttribute('data-rating') <= rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            });

            star.addEventListener('mouseout', function() {
                const currentRating = ratingInput.value;
                stars.forEach(s => {
                    if (s.getAttribute('data-rating') <= currentRating) {
                        s.classList.remove('far');
                        s.classList.add('fas', 'active');
                    } else {
                        s.classList.remove('fas', 'active');
                        s.classList.add('far');
                    }
                });
            });
        });

        // Animated Counter
        const counters = document.querySelectorAll('[data-count]');

        counters.forEach(counter => {
            const target = +counter.getAttribute('data-count');
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const updateCounter = () => {
                current += step;
                if (current < target) {
                    counter.textContent = Math.ceil(current) + (counter.textContent.includes('%') ? '%' : '+');
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target + (counter.textContent.includes('%') ? '%' : '+');
                }
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCounter();
                        observer.unobserve(entry.target);
                    }
                });
            });

            observer.observe(counter);
        });

        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
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
    });
    // Research Carousel Functionality - ADDED
    function initResearchCarousel() {
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.indicator');
        let currentSlide = 0;
        let slideInterval;

        function showSlide(index) {
            // Remove active class from all slides and indicators
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));

            // Add active class to current slide and indicator
            slides[index].classList.add('active');
            indicators[index].classList.add('active');

            currentSlide = index;
        }

        function nextSlide() {
            let nextIndex = (currentSlide + 1) % slides.length;
            showSlide(nextIndex);
        }

        // Auto-advance slides every 4 seconds
        function startSlideShow() {
            slideInterval = setInterval(nextSlide, 4000);
        }

        // Pause slideshow on hover
        const carousel = document.querySelector('.research-carousel');
        carousel.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });

        carousel.addEventListener('mouseleave', () => {
            startSlideShow();
        });

        // Indicator click events
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                clearInterval(slideInterval);
                showSlide(index);
                startSlideShow();
            });
        });

        // Touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;

        carousel.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
            clearInterval(slideInterval);
        });

        carousel.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startSlideShow();
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next slide
                    nextSlide();
                } else {
                    // Swipe right - previous slide
                    let prevIndex = (currentSlide - 1 + slides.length) % slides.length;
                    showSlide(prevIndex);
                }
            }
        }

        // Initialize the carousel
        startSlideShow();
    }

    // Initialize carousel when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Your existing initialization code...

        // Initialize research carousel - ADDED
        initResearchCarousel();

        // Rest of your existing JavaScript...
    });
</script>