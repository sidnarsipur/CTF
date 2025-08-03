<?php
session_name("HHN");
session_start();

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
  header("Location: ./?error=1");
  die();
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
                <a class="nav-link" href="./">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" href="./generator.php">Report Generator</a>
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
            <div class="col-md-6 offset-md-3">
              <div class="alert alert-success" style="display:none" role="alert" id="success"></div>
              <div class="alert alert-danger" style="display:none" role="alert" id="fail"></div>

              <h4>Patient Visit Statistics Report Generator</h4>
              <p>Each report can produce two types of columns. The "group by" columns will contain unique permutations of values in those columns and "aggregated" columns that will include a summary of the data for each of the "group by" combinations. Please make your selections below. You may pick anywhere from 1 to 8 columns total.</p>
              <div class="row">
                <form action="javascript:submit_report_request()">
                  <fieldset>
                    <div class="form-group">
                      <label class="form-label">Retrieve unique permutations of ("group by"):</label>
                      <select multiple="" class="form-select" name="group_by" id="group_by">
                        <option value="age">Patient Age</option>
                        <option value="state">Patient State/Province</option>
                        <option value="weight_lb">Patient Weight (lb)</option>
                        <option value="height_in">Patient Height (in)</option>
                        <option value="date_of_visit">Date of Visit</option>
                        <option value="WEEKDAY(date_of_visit) weekday_of_visit">Weekday of Visit</option>
                        <option value="diagnosis">Diagnosis</option>
                        <option value="outcome">Outcome</option>
                      </select>
                    </div>

                    <div class="form-group mt-3">
                      <label class="form-label">Aggregated columns:</label>
                      <select multiple="" class="form-select" name="aggregate" id="aggregate">
                        <option value="COUNT(state) state_count">Count of Unique States/Provinces</option>
                        <option value="COUNT(age) age_count">Count of Unique Ages</option>
                        <option value="COUNT(diagnosis) diagnosis_count">Count of Unique Diagnoses</option>
                        <option value="COUNT(outcome) outcome_count">Count of Unique Outcomes</option>
                        <option value="MAX(age) age_max">Maximum Age</option>
                        <option value="MIN(age) age_min">Minimum Age</option>
                        <option value="AVG(age) age_avg">Average Age</option>
                        <option value="MAX(weight_lb) weight_lb_max">Maximum Weight (lb)</option>
                        <option value="MIN(weight_lb) weight_lb_min">Minimum Weight (lb)</option>
                        <option value="AVG(weight_lb) weight_lb_avg">Average Weight (lb)</option>
                        <option value="MAX(height_in) height_in_max">Maximum Height (in)</option>
                        <option value="MIN(height_in) height_in_min">Minimum Height (in)</option>
                        <option value="AVG(height_in) height_in_avg">Average Height (in)</option>
                        <option value="WEEKDAY(date_of_visit) weekday_of_visit">Weekday of Visit</option>
                      </select>
                    </div>
                  </fieldset>

                  <button class="btn btn-success mt-3" name="submit" type="submit" value="submit">Submit Request</button>
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
    <script>
      function submit_report_request() {
        $("#success").slideUp();
        $("#fail").slideUp();
        $.ajax({
          dataType: "json",
          type: 'POST',
          url: "./submit_request.php",
          data: {
            selections: JSON.stringify({
              group_by: $('#group_by').val(),
              aggregate: $('#aggregate').val()
            }),
            filename: Math.floor(1000000 + Math.random() * 9000000),
            submit: "submit"
          },
          success: function (data, status, xhr) {
            if (data["error"]) {
              $("#fail").html(data["message"]);
              $("#fail").slideDown();
            } else {
              $("#success").html(data["message"]);
              $("#success").slideDown();
            }
          }
        });
      }
    </script>
  </body>
</html>