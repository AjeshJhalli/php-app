<?php
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', $uri_path);
?>
<nav class="navbar navbar-expand-lg bg-light border-bottom shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-primary" href="/">
      <i class="bi bi-house-door-fill"></i>Code Cost
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

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <!-- Left-aligned navigation links -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center <?php if ($uri_segments[1] === "") {
                                                          echo "active";
                                                        } ?>" href="/">
            <i class="bi bi-house"></i> <span class="ms-1">Home</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center <?php if ($uri_segments[1] === "customers") {
                                                          echo "active";
                                                        } ?>" aria-current="page" href="/customers">
            <i class="bi bi-people"></i><span class="ms-1">Customers</span>
          </a>
        </li>
      </ul>

      <!-- Right-aligned user info and sign-out button -->
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
  </div>
</nav>