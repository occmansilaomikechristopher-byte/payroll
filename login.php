<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>GV Aluminum and Glass Supply - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/libs/sweetalert2/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
    <script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <style>
        :root {
            --brand-dark-teal: #01544c;
            --brand-darker-teal: #01433d;
            --brand-accent-teal: #00695c;
            --brand-gold: #f4b42e;
            --brand-gold-dark: #d89b25;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #d1d5db;
            --card-radius: 24px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            min-height: 100vh;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
            padding: 20px 16px;
        }

        /* Diagonal geometric corner accents - exact replica of reference image */
        .corner-accent {
            position: fixed;
            z-index: 1;
            pointer-events: none;
            width: clamp(210px, 28vw, 300px);
            height: clamp(95px, 12vw, 132px);
        }

        .corner-top-left {
            top: 0;
            left: 0;
        }

        .corner-bottom-right {
            bottom: 0;
            right: 0;
            transform: rotate(180deg);
        }

        .corner-accent svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Login Card Container */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 400px;
            margin: auto;
        }

        .login-card {
            width: 100%;
            background-color: #ffffff;
            border-radius: var(--card-radius);
            box-shadow: 0 18px 45px -8px rgba(0, 0, 0, 0.12), 0 3px 12px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        /* Top Header (Dark Teal) */
        .card-header-brand {
            background-color: var(--brand-dark-teal);
            padding: 20px 20px 16px 20px;
            text-align: center;
        }

        .logo-circle {
            width: 98px;
            height: 98px;
            background-color: #ffffff;
            border-radius: 50%;
            margin: 0 auto 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.14);
            overflow: hidden;
            padding: 6px;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .brand-title {
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 3px;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.82rem;
            font-weight: 400;
            line-height: 1.3;
        }

        /* Gold Accent Stripe */
        .gold-stripe {
            height: 4px;
            background: linear-gradient(90deg, #d89b25 0%, #f4b42e 50%, #d89b25 100%);
            width: 100%;
        }

        /* Card Form Body */
        .card-body-form {
            padding: 22px 26px 16px 26px;
            background-color: #ffffff;
        }

        .form-alert {
            color: #dc2626;
            font-size: 0.84rem;
            font-weight: 500;
            text-align: center;
            margin-bottom: 12px;
            min-height: 0;
            transition: all 0.2s ease;
        }

        .form-alert:empty {
            display: none;
            margin-bottom: 0;
        }

        /* Input field container */
        .input-box {
            position: relative;
            display: flex;
            align-items: center;
            border: 1.2px solid var(--border-color);
            border-radius: 10px;
            background-color: #ffffff;
            height: 44px;
            padding: 0 14px;
            margin-bottom: 12px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-box:focus-within {
            border-color: var(--brand-dark-teal);
            box-shadow: 0 0 0 3px rgba(1, 84, 76, 0.12);
        }

        .input-icon-left {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .form-input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-family: 'Poppins', sans-serif;
            font-size: 0.92rem;
            color: var(--text-primary);
            height: 100%;
            padding: 0;
        }

        .form-input::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        .toggle-password {
            background: none;
            border: none;
            padding: 0;
            margin-left: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: color 0.15s ease;
        }

        .toggle-password:hover {
            color: var(--brand-dark-teal);
        }

        /* Options row: Remember Me & Forgot Password */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 2px;
            margin-bottom: 16px;
        }

        .checkbox-container {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            position: relative;
        }

        .checkbox-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkbox-custom {
            width: 17px;
            height: 17px;
            border: 1.5px solid var(--border-color);
            border-radius: 4px;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.18s ease;
        }

        .checkbox-container input:checked ~ .checkbox-custom {
            background-color: var(--brand-dark-teal);
            border-color: var(--brand-dark-teal);
        }

        .checkbox-custom svg {
            width: 10px;
            height: 10px;
            opacity: 0;
            transform: scale(0.6);
            transition: all 0.15s ease;
        }

        .checkbox-container input:checked ~ .checkbox-custom svg {
            opacity: 1;
            transform: scale(1);
        }

        .checkbox-label {
            font-size: 0.85rem;
            color: #374151;
            font-weight: 500;
        }

        .forgot-link {
            font-size: 0.85rem;
            color: var(--brand-dark-teal);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .forgot-link:hover {
            color: var(--brand-darker-teal);
            text-decoration: underline;
        }

        /* Sign In Button */
        .btn-sign-in {
            width: 100%;
            height: 44px;
            background-color: var(--brand-dark-teal);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.96rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(1, 84, 76, 0.2);
        }

        .btn-sign-in:hover {
            background-color: var(--brand-darker-teal);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(1, 84, 76, 0.28);
        }

        .btn-sign-in:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(1, 84, 76, 0.2);
        }

        /* Divider & Footer */
        .card-divider {
            border: 0;
            border-top: 1px solid #e5e7eb;
            margin: 18px 0 12px 0;
        }

        .version-footer {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.8rem;
            font-weight: 500;
        }

        @media (max-width: 575.98px) {
            body {
                padding: 14px 10px;
            }
            .corner-accent {
                width: clamp(140px, 35vw, 180px);
                height: clamp(65px, 15vh, 85px);
            }
            .card-header-brand {
                padding: 16px 16px 14px 16px;
            }
            .card-body-form {
                padding: 18px 18px 14px 18px;
            }
            .logo-circle {
                width: 86px;
                height: 86px;
            }
            .brand-title {
                font-size: 1.02rem;
            }
            .brand-subtitle {
                font-size: 0.76rem;
            }
        }
    </style>
</head>
<body>

    <!-- Diagonal Geometric Corner Accents -->
    <div class="corner-accent corner-top-left" aria-hidden="true">
        <svg viewBox="0 0 286 120" preserveAspectRatio="none">
            <!-- Dark Teal Base Polygon -->
            <polygon points="0,0 248,0 0,114" fill="#004844" />
            <!-- Emerald Accent Wedge -->
            <polygon points="175,0 248,0 0,114 0,50" fill="#00695c" />
            <!-- Gold Accent Stripe -->
            <polygon points="248,0 286,0 0,120 0,114" fill="#f4b42e" />
        </svg>
    </div>

    <div class="corner-accent corner-bottom-right" aria-hidden="true">
        <svg viewBox="0 0 286 120" preserveAspectRatio="none">
            <!-- Dark Teal Base Polygon -->
            <polygon points="0,0 248,0 0,114" fill="#004844" />
            <!-- Emerald Accent Wedge -->
            <polygon points="175,0 248,0 0,114 0,50" fill="#00695c" />
            <!-- Gold Accent Stripe -->
            <polygon points="248,0 286,0 0,120 0,114" fill="#f4b42e" />
        </svg>
    </div>

    <!-- Login Card -->
    <main class="login-wrapper">
        <div class="login-card">
            
            <!-- Dark Teal Header with Logo -->
            <div class="card-header-brand">
                <div class="logo-circle">
                    <img src="assets/images/gv-logo.png" alt="GV Aluminum &amp; Glass Works Logo">
                </div>
                <h1 class="brand-title">GV Aluminum and Glass Supply</h1>
                <p class="brand-subtitle">Inventory Sales &amp; Payroll Management System</p>
            </div>

            <!-- Gold Accent Line -->
            <div class="gold-stripe"></div>

            <!-- White Form Section -->
            <div class="card-body-form">
                <form id="form-login" novalidate>
                    <div id="message-show" class="form-alert" role="alert"></div>

                    <!-- Username Field -->
                    <div class="input-box">
                        <span class="input-icon-left" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input type="text" class="form-input" name="username" id="username" placeholder="Username" autocomplete="username" required>
                    </div>

                    <!-- Password Field -->
                    <div class="input-box">
                        <span class="input-icon-left" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17 9V7c0-2.76-2.24-5-5-5S7 4.24 7 7v2H6c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-9c0-1.1-.9-2-2-2zm-5 8c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm3.1-8H8.9V7c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                            </svg>
                        </span>
                        <input type="password" class="form-input" name="password" id="password-input" placeholder="Password" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" id="toggle-password-btn" aria-label="Toggle password visibility">
                            <!-- Eye Slash (Default: password hidden) -->
                            <svg class="icon-eye-slash" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                            <!-- Eye (Shown when password visible) -->
                            <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" value="1" id="remember-me" name="remember">
                            <span class="checkbox-custom">
                                <svg viewBox="0 0 12 10" fill="none">
                                    <path d="M1.5 5.2L4.3 8L10.5 1.8" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="checkbox-label">Remember Me</span>
                        </label>
                        <a href="javascript:void(0)" class="forgot-link" id="forgot-password-link">Forgot Password?</a>
                    </div>

                    <!-- Sign In Button -->
                    <button type="submit" class="btn-sign-in" id="btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        <span>Sign In</span>
                    </button>
                </form>

                <!-- Divider & Footer -->
                <hr class="card-divider">
            </div>

        </div>
    </main>

    <script>
        window.Swal = window.Swal || window.swal;
        if (!window.Swal || typeof window.Swal.fire !== 'function') {
            window.Swal = {
                fire: function (options) {
                    if (options && (options.title || options.text)) {
                        alert((options.title || '') + (options.text ? '\n' + options.text : ''));
                    }
                    return Promise.resolve({});
                },
                showLoading: function () {},
                close: function () {}
            };
        }

        $(document).ready(function () {
            // Password visibility toggle
            $('#toggle-password-btn').on('click', function () {
                var passwordInput = $('#password-input');
                var isPassword = passwordInput.attr('type') === 'password';
                passwordInput.attr('type', isPassword ? 'text' : 'password');
                
                if (isPassword) {
                    $(this).find('.icon-eye-slash').hide();
                    $(this).find('.icon-eye').show();
                    $(this).attr('aria-label', 'Hide password');
                } else {
                    $(this).find('.icon-eye').hide();
                    $(this).find('.icon-eye-slash').show();
                    $(this).attr('aria-label', 'Show password');
                }
            });

            // Forgot Password helper
            $('#forgot-password-link').on('click', function () {
                Swal.fire({
                    icon: 'info',
                    title: 'Forgot Password?',
                    text: 'Please contact your system administrator to reset or retrieve your account credentials.',
                    confirmButtonColor: '#01544c'
                });
            });

            // Existing Login Form AJAX Submission (Preserved exact functionality)
            $('#form-login').on('submit', async function (e) {
                e.preventDefault();
                var username = $('#username').val().trim();
                var password = $('#password-input').val().trim();
                if (!username || !password) {
                    $('#message-show').text('Username and password are required.').show();
                    return;
                }
                $('#message-show').text('').hide();

                Swal.fire({ 
                    title: 'Authenticating...', 
                    allowOutsideClick: false, 
                    didOpen: () => Swal.showLoading() 
                });

                await new Promise(function (r) { setTimeout(r, 400); });

                $.ajax({
                    url: 'ajax.php?action=login',
                    method: 'POST',
                    dataType: 'JSON',
                    data: $(this).serialize(),
                    error: function (xhr) {
                        Swal.close();
                        $('#message-show').text(xhr.responseText || 'Login failed. Please check your network connection.').show();
                    },
                    success: function (res) {
                        Swal.close();
                        if (res && res.result) {
                            location.href = 'index.php?page=home';
                        } else {
                            $('#message-show').text((res && res.message) ? res.message : 'Invalid credentials.').show();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
