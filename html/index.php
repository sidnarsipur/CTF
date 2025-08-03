<?php
session_name("HHN");
session_start();
$login_failure = false;

if (isset($_POST["username"]) && isset($_POST["password"])) {
  $users = file_get_contents('lib/users.json');
  $users = json_decode($users, true);

  foreach ($users as $user) {
    if ($user["username"] == $_POST["username"] && $user["hash"] == md5($_POST["password"])) {
      $_SESSION["logged_in"] = true;
      $_SESSION["first"] = $user["first"];
      $_SESSION["last"] = $user["last"];
      header("Location: ./generator.php");
    }
  }
  $login_failure = true;
}

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.0/spacelab/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <title>Patient Statistics | Horizon Health Network</title>
  </head>
  <body>
    <header>
      <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
          <a class="navbar-brand" href="#"><i class="fa-solid fa-leaf"></i> HHN</a>
          <div class="navbar-collapse">
            <ul class="navbar-nav me-auto">
              <li class="nav-item">
                <a class="nav-link active" href="./">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="./generator.php">Report Generator</a>
              </li>
            </ul>
            <?php
              if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]) {
                echo '<span class="navbar-text">Welcome, '.$_SESSION["first"].'!</span>';
                echo '<ul class="navbar-nav">
                        <li class="nav-item active">
                          <a href="./logout.php" class="nav-link">Logout</a>
                        </li>
                      </ul>';
              }
            ?>

          </div>
        </div>
      </nav>
    </header>
    <main role="main">
      <div class="album py-5">
        <div class="container">
          <div class="row">
            <div class="col-md-12 text-center">
              <h2>Patient Health Statistics Report Generator</h2>
            </div>
          </div>
          <br><br>
          <div class="row">
            <div class="col-md-6 offset-md-3">
              <?php
              if ($login_failure) {
                  echo '<div class="row"><div class="col-md-12"><div class="alert alert-danger" role="alert">Login failed. Incorrect username or password.</div></div></div><br>';
              } else if (isset($_GET["error"]) && $_GET["error"] == "1") {
                  echo '<div class="row"><div class="col-md-12"><div class="alert alert-warning" role="alert">You must be logged in to generate reports.</div></div></div><br>';
              }

              ?>
              <h4 class="mb-0">Researcher login</h4>
              <div class="row">
                <form method="POST" action="./">
                  <fieldset>
                    <div class="form-group">
                      <label for="username" class="form-label mt-4">Username</label>
                      <input type="text" class="form-control" id="username" placeholder="Username" name="username" required>
                    </div>
                    <div class="form-group">
                      <label for="password" class="form-label mt-4">Password</label>
                      <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
                    </div>
                  </fieldset>

                  <button class="btn btn-success mt-3" type="submit">Login</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <hr>
    <footer class="text-muted">
      <div class="container">
        <p>Copyright &copy; <?=date("Y")?> Horizon Health Network</p>
      </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  </body>
</html>