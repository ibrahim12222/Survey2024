<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Club Mahindra</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/slider.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <!-- Side-Nav -->
    <div class="side-navbar active-nav d-flex justify-content-between flex-wrap flex-column" id="sidebar">
        <ul class="nav flex-column text-white w-100">
            <a href="#" class="nav-link h3 text-white my-2 font">
                Club Mahindra
            </a>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <img src="header/img/hamburgs.png" alt="Forms">
                    <span class="mx-2">Forms</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="formsDropdown">
                    <li><a class="dropdown-item" href="buffet_temperature_record.php">Buffet Temperature</a></li>
                    <li><a class="dropdown-item" href="#">Cooking & Reheating Temper</a></li>
                    <li><a class="dropdown-item" href="#">Daily Worksheet</a></li>
                    <li><a class="dropdown-item" href="#">Pest Incidence</a></li>
                    <li><a class="dropdown-item" href="#">Store Cleaning</a></li>
                    <li><a class="dropdown-item" href="#">Temperature and Humidity</a></li>
                    <li><a class="dropdown-item" href="temperature_record.php">Temperature</a></li>
                    <li><a class="dropdown-item" href="#">Thawing</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="header/img/report.svg" alt="Reports">
                    <span class="mx-2">Reports</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                    <li><a class="dropdown-item" href="">Buffet Temperature</a></li>
                    <li><a class="dropdown-item" href="#">Cooking & Reheating Temper</a></li>
                    <li><a class="dropdown-item" href="#">Daily Worksheet</a></li>
                    <li><a class="dropdown-item" href="#">Pest Incidence</a></li>
                    <li><a class="dropdown-item" href="#">Store Cleaning</a></li>
                    <li><a class="dropdown-item" href="#">Temperature and Humidity</a></li>
                    <li><a class="dropdown-item" href="">Temperature</a></li>
                    <li><a class="dropdown-item" href="#">Thawing</a></li>
                </ul>
            </li>

        </ul>
    </div>
    <!-- main wrapper -->
    <div class="p-1 my-container active-cont">
        <!-- hamburg start -->
        <nav class="navbar top-navbar navbar-light px-5">
            <a class="btn border-0" id="menu-btn">
                <img src="header/img/hamburgs.png" alt="" height="20px" width="20px">
            </a>
        </nav>
        <!-- hamburg end -->



    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        var menu_btn = document.querySelector("#menu-btn");
        var sidebar = document.querySelector("#sidebar");
        var container = document.querySelector(".my-container");
        menu_btn.addEventListener("click", () => {
            sidebar.classList.toggle("active-nav");
            container.classList.toggle("active-cont");
        });
    </script>
</body>

</html>