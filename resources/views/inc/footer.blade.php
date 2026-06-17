@php
    $Social_icons = [
        ["name" => "Linkedin", "img" => "img/linkedin.jpg", "webpage" => "http://linkedin.com/rib"],
        ["name" => "Facebook", "img" => "img/facebook.jpg", "webpage" => "http://facebook.com/rib"],
        ["name" => "Youtube", "img" => "img/youtube.jpg", "webpage" => "http://youtube.com/rib"],
        ["name" => "Twitter", "img" => "img/twitter.jpg", "webpage" => "http://twitter.com/rib"],
    ];
@endphp

<style type="text/css">
    .main-footer {
        background-color: #0f172a;
        color: #f8fafc;
        padding: 80px 0 30px 0;
        font-family: 'Inter', sans-serif;
    }
    .footer-heading {
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 25px;
        border-left: 3px solid #3b82f6;
        padding-left: 10px;
    }
    .footer-link-list { list-style: none; padding: 0; }
    .footer-link-list li { margin-bottom: 12px; }
    .footer-link-list a {
        color: #94a3b8;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s ease;
    }
    .footer-link-list a:hover { color: #3b82f6; }
    .newsletter-box {
        background: rgba(255, 255, 255, 0.03);
        padding: 25px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .form-control-custom {
        background: #1e293b;
        border: 1px solid #334155;
        color: #fff;
        border-radius: 6px;
        padding: 12px;
    }
    .btn-subscribe {
        background: #3b82f6;
        border: none;
        padding: 12px 25px;
        font-weight: 600;
        border-radius: 6px;
        color: white;
    }
    .social-icon-circle {
        width: 35px;
        height: 35px;
        filter: grayscale(1) brightness(2);
        transition: 0.3s;
        margin-right: 10px;
    }
    .social-icon-circle:hover {
        filter: grayscale(0) brightness(1);
        transform: translateY(-3px);
    }
    .back-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #3b82f6;
        color: white;
        padding: 10px 15px;
        border-radius: 50px;
        text-decoration: none;
        font-size: 12px;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 1000;
    }

    html {
    scroll-behavior: smooth;
}
</style>

<footer class="main-footer">
    <div class="container">
        <div class="row">
            <!-- Company Info -->
            <div class="col-md-3 mb-4">
                <h5 class="footer-heading">Revolution Insurance Brokers Ltd</h5>
                <ul class="footer-link-list">
                    <li><a href="#">About Our Mission</a></li>
                    <li><a href="#">The Insurance Blog</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Contact Support</a></li>
                    <li><a href="#">Support FAQs</a></li>
                </ul>
            </div>

            <!-- Solutions -->
            <div class="col-md-4 mb-4">
                <h5 class="footer-heading">Insurance Solutions</h5>
                <div class="row">
                    <div class="col-6">
                        <ul class="footer-link-list">
                            <li><a href="#">Personal Auto</a></li>
                            <li><a href="#">Business Auto</a></li>
                            <li><a href="#">Liability</a></li>
                            <li><a href="#">Agriculture</a></li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="footer-link-list">
                            <li><a href="#">Engineering</a></li>
                            <li><a href="#">Marine & Aviation</a></li>
                            <li><a href="#">Bonds & Surety</a></li>
                            <li><a href="#">Takaful (Muslim)</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="col-md-5 mb-4">
                <div class="newsletter-box">
                    <h5 class="footer-heading" style="border:none; padding:0;">Stay Protected</h5>
                    <p class="small text-muted">Join 5,000+ others receiving our monthly insurance insights and safety tips.</p>
                    <form class="d-flex gap-2">
                        @csrf
                        <input type="email" class="form-control form-control-custom" placeholder="Email address">
                        <button class="btn btn-subscribe" type="button">Join</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row mt-5 pt-4 border-top border-secondary">
            <div class="col-md-6 text-center text-md-start">
                <p class="small text-muted">&copy; {{ date("Y") }} Revolution Insurance Brokers. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="social-links">
                    @foreach ($Social_icons as $icon)
                        <a href="{{ $icon['webpage'] }}" target="_blank">
                            <img src="{{ global_asset($icon['img']) }}" alt="{{ $icon['name'] }}" class="social-icon-circle">
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</footer>

<a href="#top" class="back-to-top">↑ TOP</a>
