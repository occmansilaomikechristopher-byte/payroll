<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>GV Aluminum and Glass Supply - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Lg0I2B4WJbKAkPndK5H3Om2R0mjMNka4eS+2xeJcx3F6j0bFvC4C4Qp2Ddj3L4hY" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
    <link rel="stylesheet" href="assets/libs/sweetalert2/sweetalert2.min.css">
    <script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <style>
        :root {
            color-scheme: dark;
            font-family: 'Poppins', Arial, sans-serif;
            background: #ffffff;
            color: #E2E8F0;
        }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            padding: 0;
            background:
                radial-gradient(circle at top left, rgba(9, 120, 94, 0.18), transparent 32%),
                radial-gradient(circle at top right, rgba(15, 118, 255, 0.12), transparent 28%),
                linear-gradient(135deg, #eef8f5 0%, #f7fbff 42%, #eef5ff 100%);
            color: #E2E8F0;
            font-family: 'Poppins', Arial, sans-serif;
            overflow: hidden;
        }
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
            isolation: isolate;
        }
        .parallax-scene {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .parallax-scene::before,
        .parallax-scene::after {
            content: '';
            position: absolute;
            inset: -12%;
            background-repeat: no-repeat;
            transition: transform 120ms linear;
        }
        .parallax-scene::before {
            background-image:
                radial-gradient(circle at 18% 20%, rgba(10, 145, 112, 0.22) 0 7%, transparent 8%),
                radial-gradient(circle at 78% 18%, rgba(33, 150, 243, 0.18) 0 8%, transparent 9%),
                radial-gradient(circle at 70% 80%, rgba(255,255,255,0.72) 0 10%, transparent 11%);
            filter: blur(2px);
        }
        .parallax-scene::after {
            background-image:
                linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.18) 46%, transparent 58%),
                linear-gradient(250deg, transparent 60%, rgba(8, 120, 94, 0.08) 72%, transparent 84%);
            mix-blend-mode: screen;
        }
        .glass-orb {
            position: absolute;
            display: block;
            border-radius: 999px;
            backdrop-filter: blur(12px);
            background: linear-gradient(135deg, rgba(255,255,255,0.34), rgba(255,255,255,0.08));
            border: 1px solid rgba(255,255,255,0.38);
            box-shadow: 0 20px 50px rgba(18, 90, 78, 0.12);
        }
        .orb-1 { width: 220px; height: 220px; top: 8%; left: 8%; }
        .orb-2 { width: 140px; height: 140px; bottom: 14%; left: 12%; }
        .orb-3 { width: 180px; height: 180px; top: 12%; right: 10%; }
        .orb-4 { width: 110px; height: 110px; bottom: 18%; right: 16%; }
        .orb-5 { width: 70px; height: 70px; top: 55%; left: 6%; }
        .orb-6 { width: 90px; height: 90px; top: 44%; right: 8%; }
        .shine-line {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(115deg, transparent 0 36%, rgba(255,255,255,0.18) 40%, transparent 44% 100%),
                linear-gradient(240deg, transparent 0 68%, rgba(7, 121, 192, 0.08) 72%, transparent 76% 100%);
            opacity: 0.8;
        }
        .login-shell {
            position: relative;
            z-index: 1;
            width: min(100%, 460px);
            padding: 14px;
            border-radius: 32px;
            background: linear-gradient(135deg, rgba(255,255,255,0.32), rgba(255,255,255,0.12));
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 30px 80px rgba(7, 87, 77, 0.12);
            backdrop-filter: blur(16px);
        }
        .login-shell::before {
            content: '';
            position: absolute;
            inset: 10px;
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,0.18);
            pointer-events: none;
        }
        .login-card {
            width: 100%;
            background: linear-gradient(180deg, rgba(11, 80, 61, 0.88), rgba(9, 68, 53, 0.92));
            border-radius: 24px;
            padding: 34px 28px;
            border: 1px solid rgba(255, 255, 255, 0.28);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.12), 0 18px 48px rgba(10, 50, 40, 0.22);
            backdrop-filter: blur(22px);
        }
        .brand-block {
            text-align: center;
            margin-bottom: 26px;
        }
        .brand-logo {
            width: 96px;
            height: 96px;
            margin: 0 auto 18px;
            display: block;
            object-fit: contain;
            border-radius: 18px;
            background: rgba(255,255,255,0.08);
            padding: 16px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06);
        }
        .brand-title {
            font-size: 1rem;
            font-weight: 700;
            color: #F8FAFC;
            margin-bottom: 6px;
        }
        .brand-subtitle {
            font-size: 0.92rem;
            color: rgba(226, 232, 240, 0.76);
            margin-top: 4px;
            line-height: 1.5;
        }
        .login-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 8px;
        }
        .login-subtitle {
            color: rgba(226, 232, 240, 0.72);
            font-size: 0.96rem;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .form-label {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        .form-control {
            width: 100%;
            min-height: 52px;
            border-radius: 14px;
            border: 1px solid rgba(4, 141, 73, 0.18);
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.95);
            color: #E2E8F0;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            font-size: 0.96rem;
        }
        .form-control::placeholder {
            color: rgba(148, 163, 184, 0.75);
        }
        .form-control:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.24);
            outline: none;
            background: rgba(15, 23, 42, 1);
        }
        .input-group {
            width: 100%;
            margin-bottom: 18px;
        }
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-check-input {
            width: 18px;
            height: 18px;
            border-radius: 6px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: rgba(15, 23, 42, 0.95);
        }
        .form-check-input:checked {
            background-color: #2563EB;
            border-color: #2563EB;
        }
        .form-check-label {
            color: rgba(226, 232, 240, 0.78);
            font-size: 0.92rem;
        }
        .btn-primary {
            width: 100%;
            border-radius: 14px;
            padding: 14px 16px;
            background: linear-gradient(135deg, #054248 0%, #0779c0 100%);
            border: none;
            font-size: 0.96rem;
            font-weight: 700;
            color: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 18px 28px rgba(2, 196, 164, 0.24);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 34px rgba(37, 99, 235, 0.28);
        }
        .glass-rim {
            position: absolute;
            inset: 0;
            border-radius: 32px;
            pointer-events: none;
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.28),
                inset 0 -1px 0 rgba(255,255,255,0.08);
        }
        .form-alert {
            min-height: 18px;
            margin-bottom: 14px;
            color: #f87171;
            font-size: 0.9rem;
        }
        @media (max-width: 575.98px) {
            body { overflow: auto; }
            .login-shell {
                padding: 10px;
            }
            .login-card {
                padding: 24px 18px;
            }
            .brand-logo {
                width: 64px;
                height: 64px;
            }
        }
    </style>
</head>
<body>
    <main class="login-page">
        <div class="parallax-scene" aria-hidden="true">
            <div class="shine-line"></div>
            <span class="glass-orb orb-1" data-depth="0.10"></span>
            <span class="glass-orb orb-2" data-depth="0.18"></span>
            <span class="glass-orb orb-3" data-depth="0.08"></span>
            <span class="glass-orb orb-4" data-depth="0.14"></span>
            <span class="glass-orb orb-5" data-depth="0.22"></span>
            <span class="glass-orb orb-6" data-depth="0.16"></span>
        </div>
        <section class="login-shell" aria-label="Login form">
            <div class="glass-rim"></div>
            <div class="login-card">
            <div class="brand-block">
                <img src="assets/images/gv-logo.png" alt="GV Aluminum and Glass Supply logo" class="brand-logo" width="76" height="76">
                <div class="brand-title">GV Aluminum and Glass Supply</div>
                <div class="brand-subtitle">Inventory Sales &amp; Payroll Management System</div>
            </div>
            <p class="login-subtitle"></p>
            <form id="form-login" novalidate>
                <div id="message-show" class="form-alert"></div>
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password-input">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="password" id="password-input" placeholder="Password" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="remember-me" name="remember">
                        <label class="form-check-label" for="remember-me">Remember Me</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>
            </div>
        </section>
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
            const scene = document.querySelector('.parallax-scene');
            const orbs = document.querySelectorAll('.glass-orb');

            function moveScene(clientX, clientY) {
                const x = (clientX / window.innerWidth - 0.5) * 18;
                const y = (clientY / window.innerHeight - 0.5) * 18;
                if (scene) {
                    scene.style.transform = `translate3d(${x * 0.12}px, ${y * 0.12}px, 0)`;
                }
                orbs.forEach(function (orb) {
                    const depth = parseFloat(orb.dataset.depth || '0.1');
                    orb.style.transform = `translate3d(${x * depth}px, ${y * depth}px, 0)`;
                });
            }

            window.addEventListener('mousemove', function (e) {
                moveScene(e.clientX, e.clientY);
            });
            window.addEventListener('deviceorientation', function (e) {
                if (e && e.gamma != null && e.beta != null) {
                    moveScene((window.innerWidth / 2) + (e.gamma * 8), (window.innerHeight / 2) + (e.beta * 4));
                }
            }, { passive: true });

            $('#form-login').on('submit', async function (e) {
                e.preventDefault();
                var username = $('#username').val().trim();
                var password = $('#password-input').val().trim();
                if (!username || !password) {
                    $('#message-show').text('Username and password are required.');
                    return;
                }
                Swal.fire({ title: 'Authenticating...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                await new Promise(function (r) { setTimeout(r, 500); });
                $.ajax({
                    url: 'ajax.php?action=login',
                    method: 'POST',
                    dataType: 'JSON',
                    data: $(this).serialize(),
                    error: function (xhr) {
                        Swal.close();
                        $('#message-show').text(xhr.responseText || 'Login failed.');
                    },
                    success: function (res) {
                        Swal.close();
                        if (res && res.result) {
                            location.href = 'index.php?page=home';
                        } else {
                            $('#message-show').text((res && res.message) ? res.message : 'Invalid credentials.');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>