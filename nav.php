<?php
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', $uri_path);
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm d-print-none">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="<?php if (isset($_SESSION['logged_in'])) {
                                                          echo "/home.php";
                                                        } else {
                                                          echo "/";
                                                        } ?>">
      <i class="bi bi-house-door-fill"></i> Code Cost
    </a>
    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent"
      aria-expanded="false"
      aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <?php if (isset($_SESSION['logged_in'])) { ?>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center <?php if ($uri_segments[1] === "") {
                                                            echo "active";
                                                          } ?>" href="/home.php">
              <i class="bi bi-house"></i> <span class="ms-1">Home</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center <?php if ($uri_segments[1] === "customers.php" || $uri_segments[1] === "customers") echo "active"; ?>" aria-current="page" href="/customers.php">
              <i class="bi bi-people"></i><span class="ms-1">Customers</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center <?php if ($uri_segments[1] === "projects.php" || $uri_segments[1] === "projects") {
                                                            echo "active";
                                                          } ?>" aria-current="page" href="/projects.php">
              <i class="bi bi-list-task"></i><span class="ms-1">Projects</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center <?php if ($uri_segments[1] === "invoices.php" || $uri_segments[1] === "invoices") {
                                                            echo "active";
                                                          } ?>" aria-current="page" href="/invoices.php">
              <i class="bi bi-bank"></i><span class="ms-1">Invoices</span>
            </a>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item d-flex align-items-center me-3">
            <span class="text-secondary">
              <i class="bi bi-person-circle"></i> Logged in as:
              <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES) ?></strong>
            </span>
          </li>
          <li class="nav-item">
            <form action="/auth/signout.php" class="d-inline">
              <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-box-arrow-right"></i> Sign out
              </button>
            </form>
          </li>
        </ul>
      </div>
    <?php } else { ?>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a href="/auth/signup.php" class="btn btn-outline-primary me-2">
              <i class="bi bi-person-plus"></i> Sign Up
            </a>
          </li>
          <li class="nav-item">
            <a href="/auth/signin.php" class="btn btn-primary">
              <i class="bi bi-box-arrow-in-right"></i> Sign In
            </a>
          </li>
        </ul>
      </div>
    <?php } ?>
  </div>
</nav>