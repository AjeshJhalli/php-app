<nav>
  <ul>
    <li>
      <a href="/">
        Home
      </a>
    </li>
    <li>
      <a href="/customers">
        Customers
      </a>
    </li>
    <li>
      <form action="/auth/signout.php">
        <button>Sign out</button>
      </form>
    </li>
    <li>
      Logged in user: <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES) ?>
    </li>
  </ul>
</nav>