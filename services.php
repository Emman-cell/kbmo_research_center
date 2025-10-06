<?php
include 'config/database.php';
$pageTitle = "Our Services - KBMO Center for Translational Research";
include 'includes/header.php';

// Get services with categories
$stmt = $pdo->query("
    SELECT * FROM services
");
$services = $stmt->fetchAll();

// Group services by category
$servicesByCategory = [];
foreach ($services as $service) {
    $category = $service['name'] ?: 'General Services';
    $servicesByCategory[$category][] = $service;
}

// Get service statistics from reviews
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        COUNT(DISTINCT user_id) as happy_clients
    FROM reviews 
    WHERE status = 'approved'
");
$stats = $statsStmt->fetch();

// Get approved reviews with user info
$reviewsStmt = $pdo->query("
    SELECT r.*, u.full_name, u.institution, u.profile_image as profile_picture,
           s.name as service_name
    FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.id 
    LEFT JOIN services s ON r.service_used = s.name
    WHERE r.status = 'approved' 
    ORDER BY r.created_at DESC 
    LIMIT 12
");
$reviews = $reviewsStmt->fetchAll();

// Get rating distribution
$ratingDistStmt = $pdo->query("
    SELECT 
        rating,
        COUNT(*) as count,
        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reviews WHERE status = 'approved')), 1) as percentage
    FROM reviews 
    WHERE status = 'approved'
    GROUP BY rating 
    ORDER BY rating DESC
");
$ratingDistribution = $ratingDistStmt->fetchAll();

// Calculate average rating
$averageRating = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 4.9;
$totalReviews = $stats['total_reviews'] ?: 500;
$happyClients = $stats['happy_clients'] ?: 150;
?>

<!-- Hero Section -->
<section class="hero-section" style="padding: 100px 0; background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/backg.jpg') center/cover;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold text-white mb-4">Transform Your Research Journey</h1>
                <p class="lead text-white mb-4">Comprehensive research support from concept development to publication in high-impact journals. Trusted by <?php echo $happyClients; ?>+ researchers worldwide.</p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <div class="text-white">
                        <i class="fas fa-check-circle text-warning me-2"></i>
                        <span>100% Original Work</span>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-check-circle text-warning me-2"></i>
                        <span>Expert Researchers</span>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-check-circle text-warning me-2"></i>
                        <span>On-Time Delivery</span>
                    </div>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="upload_document.php" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-rocket me-2"></i>Start New Project
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-user-plus me-2"></i>Get Started Free
                    </a>
                <?php endif; ?>
                <a href="#reviews" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-star me-2"></i>Read Reviews
                </a>
            </div>
            <div class="col-lg-4 text-center">
                <div class="bg-white rounded-3 p-4 shadow-lg">
                    <div class="text-center mb-3">
                        <div class="display-6 fw-bold text-primary"><?php echo $averageRating; ?></div>
                        <div class="rating mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= floor($averageRating) ? 'text-warning' : ($i == ceil($averageRating) && fmod($averageRating, 1) >= 0.5 ? 'fas fa-star-half-alt text-warning' : 'text-muted'); ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <small class="text-muted">Based on <?php echo $totalReviews; ?> reviews</small>
                    </div>
                    <hr>
                    <h6 class="text-primary mb-3">Why Choose KBMO?</h6>
                    <div class="text-start">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-graduation-cap text-primary me-3 fa-lg"></i>
                            <div>
                                <h6 class="mb-1">PhD-Level Experts</h6>
                                <small class="text-muted">Subject matter specialists</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-shield-alt text-primary me-3 fa-lg"></i>
                            <div>
                                <h6 class="mb-1">100% Confidential</h6>
                                <small class="text-muted">Secure & private</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-primary me-3 fa-lg"></i>
                            <div>
                                <h6 class="mb-1">24/7 Support</h6>
                                <small class="text-muted">Always available</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4">
                <div class="counter-box">
                    <h2 class="display-4 fw-bold mb-2"><?php echo $totalReviews; ?>+</h2>
                    <p class="mb-0">Verified Reviews</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="counter-box">
                    <h2 class="display-4 fw-bold mb-2"><?php echo $averageRating; ?></h2>
                    <p class="mb-0">Average Rating</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="counter-box">
                    <h2 class="display-4 fw-bold mb-2"><?php echo $happyClients; ?>+</h2>
                    <p class="mb-0">Happy Clients</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="counter-box">
                    <h2 class="display-4 fw-bold mb-2">98%</h2>
                    <p class="mb-0">Success Rate</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Grid Section - Simple 3 Column Layout -->
<section id="services" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-3">OUR SERVICES</span>
            <h2 class="fw-bold mb-3">Comprehensive Research Solutions</h2>
            <p class="text-muted lead">Professional research support services tailored to your academic needs</p>
        </div>

        <!-- Services Grid - Clean 3 Column Layout -->
        <div class="row g-4">
            <?php
            // Flatten all services into a single array
            $allServices = [];
            foreach ($servicesByCategory as $categoryServices) {
                $allServices = array_merge($allServices, $categoryServices);
            }

            foreach ($allServices as $service): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card service-card h-100 border-0 shadow-hover">
                        <div class="card-body p-4 d-flex flex-column">
                            <!-- Service Icon and Title -->
                            <div class="text-center mb-4">
                                <div class="service-icon bg-primary text-white rounded-3 p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                                    <?php
                                    $icons = [
                                        'Concept Note Drafting' => 'fa-lightbulb',
                                        'Proposal Writing' => 'fa-file-alt',
                                        'Thesis Writing (Master)' => 'fa-graduation-cap',
                                        'Thesis Writing (PhD)' => 'fa-graduation-cap',
                                        'Data Analysis' => 'fa-chart-bar',
                                        'Manuscript Writing' => 'fa-file-medical',
                                        'Journal Submission' => 'fa-paper-plane',
                                        'Post-Submission Review' => 'fa-comments',
                                        'Literature Review' => 'fa-book',
                                        'Research Design' => 'fa-clipboard-list',
                                        'Statistical Analysis' => 'fa-calculator',
                                        'Editing & Proofreading' => 'fa-edit'
                                    ];
                                    $icon = $icons[$service['name']] ?? 'fa-cog';
                                    ?>
                                    <i class="fas <?php echo $icon; ?> fa-2x"></i>
                                </div>
                                <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($service['name']); ?></h5>
                                <div class="d-flex justify-content-center align-items-center mb-2">
                                    <span class="badge bg-success me-2">Popular</span>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo $service['duration'] ?? '2-4 weeks'; ?>
                                    </small>
                                </div>
                            </div>

                            <!-- Service Description -->
                            <p class="card-text text-muted mb-4 text-center flex-grow-1">
                                <?php echo htmlspecialchars($service['description']); ?>
                            </p>

                            <!-- Service Features -->
                            <div class="service-features mb-4">
                                <div class="row text-center g-2">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-check text-success me-1"></i>
                                            Free Revisions
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-user-graduate text-success me-1"></i>
                                            Expert Assigned
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-shield-alt text-success me-1"></i>
                                            Quality Check
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-headset text-success me-1"></i>
                                            24/7 Support
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing and Rating -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="text-center flex-grow-1">
                                    <span class="h3 text-primary fw-bold">$<?php echo number_format($service['price'], 2); ?></span>
                                    <small class="text-muted d-block">Starting Price</small>
                                </div>
                                <div class="text-center flex-grow-1">
                                    <div class="rating mb-1">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star-half-alt text-warning"></i>
                                    </div>
                                    <small class="text-muted">4.8/5 Rating</small>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="upload_document.php?service_id=<?php echo $service['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-upload me-2"></i>Get This Service
                                </a>
                            <?php else: ?>
                                <div class="d-grid gap-2">
                                    <a href="register.php?service=<?php echo $service['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>Register to Order
                                    </a>
                                    <a href="login.php" class="btn btn-outline-primary">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login to Order
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-5">
            <div class="bg-light rounded-3 p-5">
                <h3 class="fw-bold mb-3">Need Custom Research Assistance?</h3>
                <p class="text-muted mb-4">We provide tailored solutions for unique research requirements</p>
                <a href="contact.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-headset me-2"></i>Get Custom Quote
                </a>
                <a href="upload_document.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-comments me-2"></i>Free Consultation
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Reviews & Testimonials Section -->
<section id="reviews" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-3">CLIENT REVIEWS</span>
            <h2 class="fw-bold mb-3">What Our Clients Say</h2>
            <p class="text-muted lead">Real feedback from researchers who have used our services</p>
        </div>

        <div class="row">
            <!-- Rating Summary -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-4">
                            <div class="display-3 fw-bold text-primary mb-2"><?php echo $averageRating; ?></div>
                            <div class="rating mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= floor($averageRating) ? 'text-warning' : ($i == ceil($averageRating) && fmod($averageRating, 1) >= 0.5 ? 'fas fa-star-half-alt text-warning' : 'text-muted'); ?> fa-lg"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted mb-0">Based on <?php echo $totalReviews; ?> verified reviews</p>
                        </div>

                        <hr>

                        <!-- Rating Distribution -->
                        <div class="text-start">
                            <h6 class="fw-bold mb-3">Rating Breakdown</h6>
                            <?php foreach ($ratingDistribution as $dist): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <small class="text-muted me-2" style="width: 20px;"><?php echo $dist['rating']; ?>★</small>
                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: <?php echo $dist['percentage']; ?>%"></div>
                                    </div>
                                    <small class="text-muted" style="width: 40px;"><?php echo $dist['percentage']; ?>%</small>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <hr>
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="fas fa-edit me-2"></i>Write a Review
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Reviews Grid -->
            <div class="col-lg-8">
                <div class="row">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <?php if (!empty($review['profile_picture'])): ?>
                                                <img src="<?php echo htmlspecialchars($review['profile_picture']); ?>" alt="<?php echo htmlspecialchars($review['user_name']); ?>" class="rounded-circle me-3" width="50" height="50">
                                            <?php else: ?>
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($review['user_name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php if (!empty($review['institution'])): ?>
                                                        <?php echo htmlspecialchars($review['institution']); ?> •
                                                    <?php endif; ?>
                                                    <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="rating mb-3">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>

                                        <p class="card-text fst-italic mb-3">"<?php echo htmlspecialchars($review['comment']); ?>"</p>

                                        <?php if (!empty($review['service_used'])): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-tag text-muted me-2"></i>
                                                <small class="text-muted">Service: <?php echo htmlspecialchars($review['service_used']); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Default reviews if none in database -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">Dr. Sarah Johnson</h6>
                                            <small class="text-muted">University of California • 2 weeks ago</small>
                                        </div>
                                    </div>
                                    <div class="rating mb-3">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <p class="card-text fst-italic mb-3">"KBMO Center helped me complete my PhD thesis on time. The quality of work exceeded my expectations and the support team was incredibly responsive!"</p>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-tag text-muted me-2"></i>
                                        <small class="text-muted">Service: Thesis Writing (PhD)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">Michael Chen</h6>
                                            <small class="text-muted">Research Scholar • 1 month ago</small>
                                        </div>
                                    </div>
                                    <div class="rating mb-3">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <p class="card-text fst-italic mb-3">"The statistical analysis service was exceptional. They explained complex results in a way I could understand and provided comprehensive documentation."</p>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-tag text-muted me-2"></i>
                                        <small class="text-muted">Service: Data Analysis</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Load More Reviews Button -->
                <div class="text-center mt-4">
                    <a href="reviews.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All Reviews
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Review Submission Modal -->
<?php if (isset($_SESSION['user_id'])): ?>
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Write a Review
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="reviewForm" action="submit_review.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service Used</label>
                                <select class="form-select" name="service_used" required>
                                    <option value="">Select a service...</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?php echo htmlspecialchars($service['name']); ?>">
                                            <?php echo htmlspecialchars($service['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Your Rating</label>
                                <div class="rating-input">
                                    <div class="d-flex gap-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating<?php echo $i; ?>" class="d-none">
                                            <label for="rating<?php echo $i; ?>" class="star-label">
                                                <i class="fas fa-star fa-2x"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted mt-1 d-block">Click stars to rate</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Review</label>
                            <textarea class="form-control" name="comment" rows="5" placeholder="Share your experience with our service..." required maxlength="1000"></textarea>
                            <small class="text-muted">Maximum 1000 characters</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Process Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-3">OUR PROCESS</span>
            <h2 class="fw-bold mb-3">How We Deliver Excellence</h2>
            <p class="text-muted lead">A systematic approach to ensure quality and satisfaction</p>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-6 text-center mb-4">
                <div class="process-step">
                    <div class="step-number bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-clipboard-check fa-2x"></i>
                    </div>
                    <h4 class="h5 fw-bold">1. Requirement Analysis</h4>
                    <p class="text-muted">We conduct in-depth analysis of your research requirements, objectives, and academic guidelines</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 text-center mb-4">
                <div class="process-step">
                    <div class="step-number bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h4 class="h5 fw-bold">2. Expert Assignment</h4>
                    <p class="text-muted">Your project is matched with a qualified expert in your specific field of study</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 text-center mb-4">
                <div class="process-step">
                    <div class="step-number bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-tasks fa-2x"></i>
                    </div>
                    <h4 class="h5 fw-bold">3. Quality Execution</h4>
                    <p class="text-muted">Systematic research and writing process with regular updates and quality checks</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 text-center mb-4">
                <div class="process-step">
                    <div class="step-number bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-file-check fa-2x"></i>
                    </div>
                    <h4 class="h5 fw-bold">4. Final Delivery</h4>
                    <p class="text-muted">Comprehensive delivery with formatting, citations, and revision support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .shadow-hover {
        transition: all 0.3s ease;
    }

    .shadow-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .service-icon {
        transition: all 0.3s ease;
    }

    .service-card:hover .service-icon {
        transform: scale(1.1);
    }

    .counter-box {
        padding: 20px;
    }

    .process-step {
        padding: 20px;
    }

    .step-number {
        transition: all 0.3s ease;
    }

    .process-step:hover .step-number {
        transform: scale(1.1);
    }

    .star-label {
        cursor: pointer;
        color: #ddd;
        transition: color 0.2s ease;
    }

    .star-label:hover,
    input[name="rating"]:checked~.star-label {
        color: #ffc107;
    }

    input[name="rating"]:checked~.star-label~.star-label {
        color: #ddd;
    }

    .rating-input .fa-star {
        font-size: 1.5rem;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        margin: 2rem 0;
    }

    .service-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
    }

    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .service-icon {
        transition: all 0.3s ease;
    }

    .service-card:hover .service-icon {
        transform: scale(1.1);
    }

    .shadow-hover {
        transition: all 0.3s ease;
    }

    .shadow-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    }

    /* Ensure equal height cards */
    .service-card .card-body {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .service-card .flex-grow-1 {
        flex-grow: 1;
    }

    .service-card .mt-auto {
        margin-top: auto;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .services-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .service-card {
            margin-bottom: 1rem;
        }
    }

    /* Rating stars */
    .rating {
        color: #ffc107;
    }

    /* Feature icons */
    .service-features .fa-check {
        color: #28a745;
    }
</style>

<script>
    // Service category filtering
    document.addEventListener('DOMContentLoaded', function() {
        const categoryButtons = document.querySelectorAll('#serviceCategories .btn');
        const serviceCategories = document.querySelectorAll('.service-category');

        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');

                const category = this.getAttribute('data-category');

                // Show/hide service categories
                serviceCategories.forEach(categoryDiv => {
                    if (category === 'all' || categoryDiv.getAttribute('data-category') === category) {
                        categoryDiv.style.display = 'block';
                    } else {
                        categoryDiv.style.display = 'none';
                    }
                });
            });
        });

        // Star rating interaction
        const starInputs = document.querySelectorAll('input[name="rating"]');
        starInputs.forEach(input => {
            input.addEventListener('change', function() {
                const labels = document.querySelectorAll('.star-label');
                labels.forEach(label => {
                    label.querySelector('i').style.color = '#ddd';
                });

                for (let i = 1; i <= this.value; i++) {
                    document.querySelector(`label[for="rating${i}"] i`).style.color = '#ffc107';
                }
            });
        });

        // Review form submission
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                const rating = document.querySelector('input[name="rating"]:checked');
                if (!rating) {
                    e.preventDefault();
                    alert('Please select a rating before submitting your review.');
                }
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>