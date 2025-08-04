<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>TAXI Booking - Backend</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="/assets/backend/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="/assets/backend/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="/assets/backend/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="/assets/backend/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="/assets/backend/css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="<?php echo htmlspecialchars($appUrl); ?>" class="navbar-brand mx-4 mb-3" target="_blank">
                    <h3 class="text-primary">TAXI Booking</h3>
                </a>
                <div class="navbar-nav w-100">

                    <a href="<?php echo htmlspecialchars($appUrl) . '/admin'; ?>" class="nav-item nav-link active">
                        <i class="fa fa-tachometer-alt me-2"></i>Dashboard
                    </a>

                    <a href="javascript:void(0);" class="nav-item nav-link">
                        <i class="fa fa-cogs"></i>Common Settings
                    </a>

                    <a href="javascript:void(0);" class="nav-item nav-link">
                        <i class="fa fa-folder"></i>Show Bookings
                    </a>

                    <a href="javascript:void(0);" class="nav-item nav-link">
                        <i class="fa fa-calendar"></i>Service Calendar
                    </a>

                    <a href="javascript:void(0);" class="nav-item nav-link">
                        <i class="fa fa-car"></i>Car Features
                    </a>

                    <a href="javascript:void(0);" class="nav-item nav-link">
                        <i class="fa fa-plane"></i>Airport List
                    </a>

                    <a href="javascript:void(0);" class="nav-item nav-link">
                        <i class="fa fa-adjust"></i>Tunnel Charges
                    </a>

                    <a href="javascript:void(0);" class="nav-item nav-link">
                        <i class="fa fa-money-bill"></i>Extra Toll
                    </a>

                </div>
            </nav>
        </div>
        <!-- Sidebar End -->


        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="Search">
                </form>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="/assets/backend/img/admin_user.jpg" alt="" style="width: 40px; height: 40px;">
                            <span class="d-none d-lg-inline-flex"><?php echo htmlspecialchars($userName); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <!-- Updated Logout Link -->
                            <a href="javascript:void(0);" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->


            <!-- Statistical Data Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-money-bill fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Payments</p>
                                <h6 class="mb-0">$1234</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-folder fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Bookings</p>
                                <h6 class="mb-0">100</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-car fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Available Cars</p>
                                <h6 class="mb-0">10</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-plane fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Total Airports</p>
                                <h6 class="mb-0">1</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Statistical Data End -->
             
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-12" style="min-height: 400px;"></div>
                </div>
            </div>    


            <!-- Footer Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="<?php echo htmlspecialchars($appUrl); ?>">TAXI Booking</a>, All Right Reserved. 
                        </div>
                        <div class="col-12 col-sm-6 text-center text-sm-end">
                            <!--/*** This template is free as long as you keep the footer author’s credit link/attribution link/backlink. If you'd like to use the template without the footer author’s credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
                            Designed By <a href="https://htmlcodex.com">HTML Codex</a>
                        </br>
                        Distributed By <a class="border-bottom" href="https://themewagon.com" target="_blank">ThemeWagon</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/backend/lib/chart/chart.min.js"></script>
    <script src="/assets/backend/lib/easing/easing.min.js"></script>
    <script src="/assets/backend/lib/waypoints/waypoints.min.js"></script>
    <script src="/assets/backend/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="/assets/backend/lib/tempusdominus/js/moment.min.js"></script>
    <script src="/assets/backend/lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="/assets/backend/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="/assets/backend/js/main.js"></script>

    <!-- Logout Form -->
    <form id="logout-form" action="/admin/logout" method="POST" style="display: none;">
        <!-- CSRF token can be added here -->
    </form>
</body>

</html>