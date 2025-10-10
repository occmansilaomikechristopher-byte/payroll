<?php include 'db_connect.php'; ?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>jQuery Pane Slider Example</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <style>
        html,
        body {
            height: 100%;
            font-size: 16px;
        }

        body {
            font-family: 'Roboto Condensed';
            margin: 0;
            padding: 0;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        .content {
            /* padding: 2rem; */
            background-color: #555;
            height: 100%;
            overflow-x: hidden;
            min-width: 450px;
        }

        header {
            min-height: 50px;
            background-color: #000;
            color: #fff;
            padding: 15px;
        }

        footer {
            min-height: 50px;
            background-color: #000;
            color: #fff;
            padding: 15px;
        }

        #panelContainer {
            display: flex;
            flex-direction: row;
            background-color: #ebebeb;
            color: #fff;
            flex: 1;
            overflow: hidden;
        }

        .panel {
            position: relative;
            width: 50%;
        }

        .slider {
            z-index: 999999;
            display: block;
            position: absolute;
            width: 5px;
            background-color: #1ABC9C;
            left: 0;
            top: 0;
            bottom: 0;
            overflow: visible;
            user-select: none;
        }

        .slider::before {
            content: "";
            position: absolute;
            left: 0.25rem;
            top: 50%;
            height: 1.5rem;
            width: 0.5rem;
            background-color: #1ABC9C;
            cursor: col-resize;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <header>Header</header>
        <div id="panelContainer">
            <div class="panel one">
                <div class="content">
                    <div style="min-height: 1000px; background-color: grey"></div>
                   
                </div>
            </div>
            <div class="panel two">
                <div class="content">
                    Panel Two
                </div>
                <span class="slider"></span>
            </div>
        </div>
        <footer>Footer</footer>
    </div>
</body>
<script type="text/javascript" src="asse/js/freeze-table.js"></script>
<script>
    (function($) {
        $(function() {
            var isMouseDown = false,
                $panelOne = $(".panel.one"),
                $panelTwo = $(".panel.two"),
                $panelContainer = $panelOne.parent(),
                getParentWidth = function() {
                    return $panelContainer.width();
                },
                mouseMoveHandler = function(e) {
                    if (!isMouseDown) return;

                    var clientX = e.clientX || (e.touches && e.touches[0].clientX);
                    if (isNaN(clientX))
                        return;


                    var width = (clientX / getParentWidth()) * 100;

                    // don't allow a value that's smaller than zero;
                    width = width < 0 ? 0 : width;

                    // apply size to panel 1
                    $panelOne.css({
                        width: width + "%"
                    });

                    // apply size to panel 2
                    $panelTwo.css({
                        width: 100 - width + "%"
                    });
                };

            // mouseDown event
            $(".slider").on("mousedown touchstart", function() {
                // only bind a the mouseMove handler on the first cycle
                !isMouseDown && $panelContainer.on("mousemove touchmove", mouseMoveHandler);
                isMouseDown = true;
            });

            $(window).on("mouseup touchend", function() {
                isMouseDown = false;
                // detach then mouseMove handler
                $panelContainer.off("mousemove touchmove");
            });
        });
    })(jQuery);
</script>

</html>