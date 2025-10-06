<?php
$pageTitle = "About Us - KBMO Center";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section about-hero">
    <div id="particles-js"></div>
    <div class="container">
        <div class="row align-items-center min-vh-80">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4 animate-fade-in">About KBMO Center</h1>
                <p class="lead mb-4 animate-slide-up">Excellence in Translational Research Support</p>
                <div class="animate-bounce-in">
                    <a href="#our-story" class="btn btn-light btn-lg me-3 scroll-to">Our Story</a>
                    <a href="#team" class="btn btn-outline-light btn-lg scroll-to">Meet Our Team</a>
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

<!-- Mission & Vision Section -->
<section class="py-5 bg-light" id="our-story">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <div class="position-relative">
                    <img src="images/about-us.jpg" alt="KBMO Center Team" class="img-fluid rounded-3 shadow-lg main-about-image">
                    <div class="floating-stats">
                        <div class="stat-card">
                            <h3 class="text-primary mb-1" data-count="500">500+</h3>
                            <p class="mb-0">Projects</p>
                        </div>
                        <div class="stat-card">
                            <h3 class="text-primary mb-1" data-count="50">50+</h3>
                            <p class="mb-0">Experts</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="badge bg-primary mb-3">Our Story</span>
                <h2 class="fw-bold mb-4">Transforming Research Ideas into Impactful Outcomes</h2>
                <p class="lead text-muted mb-4">
                    KBMO Center for Translational Research Limited is dedicated to supporting researchers,
                    academics, and professionals in transforming their ideas into impactful research outcomes.
                </p>

                <div class="mission-vision-tabs">
                    <ul class="nav nav-pills mb-4" id="missionTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="mission-tab" data-bs-toggle="tab" data-bs-target="#mission" type="button" role="tab">
                                <i class="fas fa-bullseye me-2"></i>Our Mission
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="vision-tab" data-bs-toggle="tab" data-bs-target="#vision" type="button" role="tab">
                                <i class="fas fa-eye me-2"></i>Our Vision
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="values-tab" data-bs-toggle="tab" data-bs-target="#values" type="button" role="tab">
                                <i class="fas fa-heart me-2"></i>Our Values
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="missionTabContent">
                        <div class="tab-pane fade show active" id="mission" role="tabpanel">
                            <p>To bridge the gap between conceptualization and publication, ensuring your research meets the highest academic standards while accelerating the translation of innovative ideas into practical solutions.</p>
                        </div>
                        <div class="tab-pane fade" id="vision" role="tabpanel">
                            <p>To become Africa's leading translational research hub, empowering researchers and institutions to create knowledge that drives sustainable development and global impact.</p>
                        </div>
                        <div class="tab-pane fade" id="values" role="tabpanel">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Excellence in every deliverable</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Integrity in all interactions</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Innovation in research approaches</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Collaboration with stakeholders</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- What We Offer Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary mb-3">Services</span>
            <h2 class="fw-bold mb-3">Comprehensive Research Support</h2>
            <p class="text-muted lead">End-to-end research assistance tailored to your needs</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="service-feature-card text-center p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-lightbulb fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Concept Development</h5>
                    <p class="text-muted">Transform your research ideas into well-structured concepts and proposals with clear objectives and methodologies.</p>
                    <div class="feature-badge">From $99</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="service-feature-card text-center p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-file-alt fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Proposal Writing</h5>
                    <p class="text-muted">Comprehensive research proposal development with literature review, methodology, and budget planning.</p>
                    <div class="feature-badge">From $199</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="service-feature-card text-center p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-chart-bar fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Data Analysis</h5>
                    <p class="text-muted">Advanced statistical analysis using SPSS, R, Python, and other tools with comprehensive interpretation.</p>
                    <div class="feature-badge">From $149</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="400">
                <div class="service-feature-card text-center p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Thesis Writing</h5>
                    <p class="text-muted">Complete thesis and dissertation writing support from introduction to conclusion and references.</p>
                    <div class="feature-badge">From $299</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="500">
                <div class="service-feature-card text-center p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-file-medical fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Manuscript Preparation</h5>
                    <p class="text-muted">Journal-ready manuscript preparation following specific journal guidelines and formatting requirements.</p>
                    <div class="feature-badge">From $249</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="zoom-in" data-aos-delay="600">
                <div class="service-feature-card text-center p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-paper-plane fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Journal Submission</h5>
                    <p class="text-muted">Complete journal submission support including cover letters, reviewer responses, and submission management.</p>
                    <div class="feature-badge">From $179</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <span class="badge bg-primary mb-3">Why Choose Us</span>
                <h2 class="fw-bold mb-4">Excellence in Every Research Project</h2>
                <p class="text-muted mb-4">We combine academic expertise with practical research experience to deliver outstanding results.</p>

                <div class="advantages-list">
                    <div class="advantage-item d-flex mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="advantage-icon flex-shrink-0">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="advantage-content">
                            <h5 class="fw-bold">Expert Research Team</h5>
                            <p class="text-muted mb-0">PhD and Master's level researchers with extensive publication experience across various disciplines.</p>
                        </div>
                    </div>
                    <div class="advantage-item d-flex mb-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="advantage-icon flex-shrink-0">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="advantage-content">
                            <h5 class="fw-bold">Timely Delivery</h5>
                            <p class="text-muted mb-0">We respect your deadlines with 95% on-time delivery rate and proactive progress updates.</p>
                        </div>
                    </div>
                    <div class="advantage-item d-flex mb-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="advantage-icon flex-shrink-0">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="advantage-content">
                            <h5 class="fw-bold">Quality Assurance</h5>
                            <p class="text-muted mb-0">Rigorous quality checks, plagiarism-free work, and multiple review cycles ensure excellence.</p>
                        </div>
                    </div>
                    <div class="advantage-item d-flex" data-aos="fade-up" data-aos-delay="400">
                        <div class="advantage-icon flex-shrink-0">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="advantage-content">
                            <h5 class="fw-bold">Complete Confidentiality</h5>
                            <p class="text-muted mb-0">Your research data and personal information are protected with enterprise-grade security measures.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="progress-stats">
                    <h4 class="fw-bold mb-4">Our Success Metrics</h4>

                    <div class="progress-item mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Client Satisfaction</span>
                            <span class="text-primary fw-bold">98%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 98%" data-width="98"></div>
                        </div>
                    </div>

                    <div class="progress-item mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">On-Time Delivery</span>
                            <span class="text-primary fw-bold">95%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 95%" data-width="95"></div>
                        </div>
                    </div>

                    <div class="progress-item mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Publication Success</span>
                            <span class="text-primary fw-bold">92%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 92%" data-width="92"></div>
                        </div>
                    </div>

                    <div class="progress-item mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Repeat Clients</span>
                            <span class="text-primary fw-bold">85%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 85%" data-width="85"></div>
                        </div>
                    </div>
                </div>

                <div class="achievement-cards mt-5">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="achievement-card text-center p-3">
                                <i class="fas fa-award fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold mb-1">Quality Certified</h6>
                                <small class="text-muted">ISO Standards</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="achievement-card text-center p-3">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold mb-1">50+ Experts</h6>
                                <small class="text-muted">Research Team</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="achievement-card text-center p-3">
                                <i class="fas fa-globe fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold mb-1">Global Reach</h6>
                                <small class="text-muted">10+ Countries</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="achievement-card text-center p-3">
                                <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold mb-1">5+ Years</h6>
                                <small class="text-muted">Experience</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 bg-white" id="team">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary mb-3">Our Team</span>
            <h2 class="fw-bold mb-3">Meet Our Expert Researchers</h2>
            <p class="text-muted lead">Qualified professionals dedicated to your research success</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="100">
                <div class="team-card text-center">
                    <div class="team-image mb-3">
                        <img src="images/team-1.jpg" alt="Senior Researcher" class="img-fluid rounded-circle" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgZmlsbD0iIzk5OSI+UmVzZWFyY2hlcjwvdGV4dD48L3N2Zz4='">
                    </div>
                    <h5 class="fw-bold mb-1">Dr. Florence Akello</h5>
                    <p class="text-primary mb-2">Senior Research Consultant</p>
                    <p class="text-muted small">PhD in Public Health with 10+ years research experience</p>
                    <div class="team-social">
                        <a href="#" class="text-muted me-2"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-muted"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="200">
                <div class="team-card text-center">
                    <div class="team-image mb-3">
                        <img src="images/team-2.jpg" alt="Data Analyst" class="img-fluid rounded-circle" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgZmlsbD0iIzk5OSI+QW5hbHlzdDwvdGV4dD48L3N2Zz4='">
                    </div>
                    <h5 class="fw-bold mb-1">Ocheng Derick</h5>
                    <p class="text-primary mb-2">Lead Data Analyst</p>
                    <p class="text-muted small">MSc Statistics, Expert in SPSS, R, and Python</p>
                    <div class="team-social">
                        <a href="#" class="text-muted me-2"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-muted"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="400">
                <div class="team-card text-center">
                    <div class="team-image mb-3">
                        <img src="images/team-4.jpg" alt="Research Methodologist" class="img-fluid rounded-circle" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgZmlsbD0iIzk5OSI+TWV0aG9kb2xvZ2lzdDwvdGV4dD48L3N2Zz4='">
                    </div>
                    <h5 class="fw-bold mb-1">Dr. Emily Rodriguez</h5>
                    <p class="text-primary mb-2">Research Methodologist</p>
                    <p class="text-muted small">PhD in Social Sciences, Qualitative & Quantitative Expert</p>
                    <div class="team-social">
                        <a href="#" class="text-muted me-2"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-muted"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="bg-light rounded-3 p-5">
                        <h4 class="fw-bold mb-3">Join Our Team</h4>
                        <p class="text-muted mb-4">We're always looking for talented researchers and academic writers to join our growing team.</p>
                        <a href="careers.php" class="btn btn-primary btn-lg">View Career Opportunities</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary mb-3">Our Journey</span>
            <h2 class="fw-bold mb-3">Milestones & Achievements</h2>
            <p class="text-muted lead">From humble beginnings to research excellence</p>
        </div>

        <div class="timeline" data-aos="fade-up">
            <div class="timeline-item">
                <div class="timeline-date">2018</div>
                <div class="timeline-content">
                    <h5 class="fw-bold">Foundation</h5>
                    <p class="text-muted mb-0">KBMO Center established with a vision to support local researchers in Northern Uganda</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2019</div>
                <div class="timeline-content">
                    <h5 class="fw-bold">First 100 Projects</h5>
                    <p class="text-muted mb-0">Successfully completed 100+ research projects for universities and organizations</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2020</div>
                <div class="timeline-content">
                    <h5 class="fw-bold">Digital Transformation</h5>
                    <p class="text-muted mb-0">Launched online platform to serve clients across East Africa remotely</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2021</div>
                <div class="timeline-content">
                    <h5 class="fw-bold">Team Expansion</h5>
                    <p class="text-muted mb-0">Grew to 25+ expert researchers and expanded service offerings</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2022</div>
                <div class="timeline-content">
                    <h5 class="fw-bold">International Reach</h5>
                    <p class="text-muted mb-0">Started serving clients from 10+ countries across Africa and beyond</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2023</div>
                <div class="timeline-content">
                    <h5 class="fw-bold">500+ Projects</h5>
                    <p class="text-muted mb-0">Celebrated milestone of 500+ completed research projects with 98% satisfaction rate</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Interactive Stats Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up">
                <div class="stat-circle">
                    <h2 class="fw-bold mb-2" data-count="500">0</h2>
                    <p>Projects Completed</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-circle">
                    <h2 class="fw-bold mb-2" data-count="50">0</h2>
                    <p>Expert Researchers</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-circle">
                    <h2 class="fw-bold mb-2" data-count="98">0</h2>
                    <p>Client Satisfaction</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-circle">
                    <h2 class="fw-bold mb-2" data-count="15">0</h2>
                    <p>Countries Served</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary mb-3">Testimonials</span>
            <h2 class="fw-bold mb-3">What Researchers Say About Us</h2>
            <p class="text-muted lead">Success stories from our satisfied clients</p>
        </div>

        <div class="row" data-aos="fade-up">
            <div class="col-lg-10 mx-auto">
                <div class="testimonial-carousel owl-carousel owl-theme">
                    <div class="testimonial-item text-center">
                        <div class="testimonial-content bg-light rounded-3 p-4 mb-4">
                            <i class="fas fa-quote-left fa-2x text-primary mb-3"></i>
                            <p class="lead">"KBMO Center transformed my research proposal into a winning grant application. Their expertise in public health research is exceptional."</p>
                        </div>
                        <div class="testimonial-author">
                            <h6 class="fw-bold mb-1">Dr. Sarah Mukasa</h6>
                            <p class="text-muted mb-0">Public Health Researcher, Makerere University</p>
                            <div class="stars text-warning mt-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-item text-center">
                        <div class="testimonial-content bg-light rounded-3 p-4 mb-4">
                            <i class="fas fa-quote-left fa-2x text-primary mb-3"></i>
                            <p class="lead">"The data analysis support I received was outstanding. They not only analyzed my data but provided deep insights that strengthened my dissertation."</p>
                        </div>
                        <div class="testimonial-author">
                            <h6 class="fw-bold mb-1">John Omondi</h6>
                            <p class="text-muted mb-0">PhD Candidate, University of Nairobi</p>
                            <div class="stars text-warning mt-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-item text-center">
                        <div class="testimonial-content bg-light rounded-3 p-4 mb-4">
                            <i class="fas fa-quote-left fa-2x text-primary mb-3"></i>
                            <p class="lead">"As a busy professional, I needed help with my master's thesis. KBMO Center delivered quality work on time and helped me graduate with distinction."</p>
                        </div>
                        <div class="testimonial-author">
                            <h6 class="fw-bold mb-1">Grace Akello</h6>
                            <p class="text-muted mb-0">MBA Graduate, Gulu University</p>
                            <div class="stars text-warning mt-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0" data-aos="fade-right">
                <h3 class="fw-bold mb-3">Ready to Start Your Research Project?</h3>
                <p class="mb-0 lead">Join hundreds of successful researchers who have trusted KBMO Center with their academic work.</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="register.php" class="btn btn-light btn-lg me-3">Get Started</a>
                <a href="contact.php" class="btn btn-outline-light btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Add these CDN links to your header -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

<style>
    .about-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/about.png') center/cover;
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

    /* Main About Image */
    .main-about-image {
        transition: transform 0.3s ease;
    }

    .main-about-image:hover {
        transform: scale(1.02);
    }

    .floating-stats {
        position: absolute;
        bottom: -30px;
        right: -30px;
        display: flex;
        gap: 15px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
        min-width: 100px;
    }

    /* Tabs */
    .mission-vision-tabs .nav-pills .nav-link {
        border-radius: 50px;
        padding: 12px 24px;
        margin-right: 10px;
        margin-bottom: 10px;
        border: 2px solid #e9ecef;
        background: white;
        color: #495057;
        transition: all 0.3s ease;
    }

    .mission-vision-tabs .nav-pills .nav-link.active {
        background: #0d6efd;
        border-color: #0d6efd;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }

    /* Service Features */
    .service-feature-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid #f8f9fa;
    }

    .service-feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .feature-icon {
        transition: transform 0.3s ease;
    }

    .service-feature-card:hover .feature-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .feature-badge {
        background: #0d6efd;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
        margin-top: 15px;
    }

    /* Advantages List */
    .advantage-item {
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease;
    }

    .advantage-item:hover {
        transform: translateX(10px);
    }

    .advantage-icon {
        width: 60px;
        height: 60px;
        background: #0d6efd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        color: white;
        font-size: 1.5rem;
    }

    /* Progress Bars */
    .progress {
        height: 8px;
        border-radius: 10px;
        background: #e9ecef;
    }

    .progress-bar {
        border-radius: 10px;
        transition: width 2s ease-in-out;
    }

    /* Achievement Cards */
    .achievement-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .achievement-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    /* Team Cards */
    .team-card {
        padding: 30px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .team-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .team-image {
        width: 120px;
        height: 120px;
        margin: 0 auto;
        border: 5px solid #f8f9fa;
        border-radius: 50%;
        overflow: hidden;
    }

    .team-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .team-social a {
        transition: all 0.3s ease;
    }

    .team-social a:hover {
        color: #0d6efd !important;
        transform: scale(1.2);
    }

    /* Timeline */
    .timeline {
        position: relative;
        max-width: 800px;
        margin: 0 auto;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #0d6efd;
        transform: translateX(-50%);
    }

    .timeline-item {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 50px;
        position: relative;
    }

    .timeline-date {
        background: #0d6efd;
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: bold;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        top: -15px;
        z-index: 2;
    }

    .timeline-content {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        width: 45%;
        margin-top: 20px;
    }

    .timeline-item:nth-child(odd) .timeline-content {
        margin-left: auto;
        text-align: right;
    }

    .timeline-item:nth-child(even) .timeline-content {
        margin-right: auto;
    }

    /* Stats Circles */
    .stat-circle {
        padding: 30px;
    }

    .stat-circle h2 {
        font-size: 3.5rem;
        margin-bottom: 10px;
    }

    /* Testimonial Carousel */
    .testimonial-carousel .owl-stage-outer {
        padding: 20px 0;
    }

    .testimonial-item {
        padding: 20px;
    }

    .testimonial-content {
        position: relative;
    }

    .testimonial-content::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 10px solid transparent;
        border-right: 10px solid transparent;
        border-top: 10px solid #f8f9fa;
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

    /* Gradient Background */
    .bg-gradient-primary {
        background: linear-gradient(rgba(0, 120, 0, 0.89), rgba(0, 50, 0, 0.7));
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 80px 0;
            text-align: center;
        }

        .display-4 {
            font-size: 2.5rem;
        }

        .floating-stats {
            position: relative;
            bottom: auto;
            right: auto;
            justify-content: center;
            margin-top: 30px;
        }

        .timeline::before {
            left: 30px;
        }

        .timeline-content {
            width: calc(100% - 80px);
            margin-left: 80px !important;
            text-align: left !important;
        }

        .timeline-date {
            left: 30px;
            transform: none;
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

        // Initialize Owl Carousel
        $('.testimonial-carousel').owlCarousel({
            loop: true,
            margin: 20,
            nav: true,
            dots: false,
            autoplay: true,
            autoplayTimeout: 5000,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 1
                },
                992: {
                    items: 1
                }
            }
        });

        // Animated counters
        const counters = document.querySelectorAll('[data-count]');

        counters.forEach(counter => {
            const target = +counter.getAttribute('data-count');
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const updateCounter = () => {
                current += step;
                if (current < target) {
                    counter.textContent = Math.ceil(current) + (counter.textContent.includes('%') ? '%' : '');
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target + (counter.textContent.includes('%') ? '%' : '');
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

        // Animate progress bars
        const progressBars = document.querySelectorAll('.progress-bar');

        progressBars.forEach(bar => {
            const width = bar.getAttribute('data-width') + '%';

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            bar.style.width = width;
                        }, 500);
                        observer.unobserve(entry.target);
                    }
                });
            });

            observer.observe(bar);
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

        // Add hover effects to team cards
        document.querySelectorAll('.team-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    });
</script>