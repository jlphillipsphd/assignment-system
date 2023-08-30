<!doctype html>
<?php
require_once 'auth.php';
date_default_timezone_set(getenv("TZ"));
?>
<html lang="en">
  <head>
    <title><?php echo $course_number; ?> - Assignment Submission System</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootswatch Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/materia/bootstrap.min.css" integrity="sha384-B4morbeopVCSpzeC1c4nyV0d0cqvlSAfyXVfrPJa25im5p+yEN/YmhlgQP/OyMZD" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/simplex/bootstrap.min.css" integrity="sha384-FYrl2Nk72fpV6+l3Bymt1zZhnQFK75ipDqPXK0sOR0f/zeOSZ45/tKlsKucQyjSp" crossorigin="anonymous"> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/slate/bootstrap.min.css" integrity="sha384-8iuq0iaMHpnH2vSyvZMSIqQuUnQA7QM+f6srIdlgBrTSEyd//AWNMyEaSF2yPzNQ" crossorigin="anonymous"> -->
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.0/sandstone/bootstrap.min.css" integrity="sha384-ztQCCdmKhYHBDMV3AyR4QGZ2/z6veowJBbsmvDJW/sTuMpB9lpoubJuD0ODGSbjh" crossorigin="anonymous"> -->
    <!-- <link rel="stylesheet" type="text/css" href="css/page.css">     -->
    <meta http-equiv="Content-language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
    <meta name="robots" content="all">
  </head>
  <body>
    <main role="main">
      <div class="jumbotron">
	<div class="container text-center">	  
	  <h1><?php echo $course_number; ?></h1>
	  <h2><?php echo $course_name; ?></h2>
	  <h3>Assignment Submission System</h3>
	</div>
      </div>
      <div class="container" id="content">
	  <?php
	  function humanFileSize($size,$unit="") {
              if( (!$unit && $size >= 1<<30) || $unit == "GB")
		  return number_format($size/(1<<30),2)." GB";
              if( (!$unit && $size >= 1<<20) || $unit == " MB")
		  return number_format($size/(1<<20),2)." MB";
              if( (!$unit && $size >= 1<<10) || $unit == "KB")
		  return number_format($size/(1<<10),2)." KB";
              return number_format($size)." bytes";
	  }
	  echo "<div class='card text-white bg-primary'><div class='card-header text-center'>Assignment submission management for user: " . strtolower($user_name) . "</div></div><hr>";
	  $output = <<<EOF
					       <form action="process_file.php" method="post" enctype="multipart/form-data" id="assignment-form">
EOF;
	  echo $output;
	  $file = fopen('../storage/assignments', 'r')
	  or exit("<p style='color:red'>No assignment IDs found.</p>");
	  
	  $count = 0;
	  echo "<div class='form-group'>";
	  echo "<label for='assignment'>Assignment:</label> ";
	  echo "<select class='form-control' name='assignment' id='assignment'><option selected disabled>Select an assignment...</option>";
	  while (!feof($file)) {
	      $line = trim(fgets($file, 1024));
	      $delimiter="\t";
	      $split_line=explode($delimiter,$line);
	      $ext=explode(".",$split_line[1])[1];
	      if ($line) {
		  echo "<option>" . $split_line[0] . " (." . $ext . ")</option>";
		  $count++;
	      }
	  }
	  echo "</select></div>";
	  fclose($file);

	  $output = <<<EOF
						 <div class="form-group">
						   <label for="file">Filename:</label>
						   <input class="form-control-file" type="file" name="file" id="file"></div>
			<br/>
			<input class="btn btn-primary" type="submit" name="submit" value="Submit">
			<input class="btn btn-secondary" type="submit" name="download" value="Download">
			<input class="btn btn-secondary" type="submit" name="feedback" value="Feedback">
			<input class="btn btn-warning float-right" type="submit" name="logout" value="Logout">
		</form>
		<hr>
EOF;
	  echo $output;
	  echo "<h3 class='text-center'>Submitted Assignments</h3>";
	  echo "<table class='table'>";
          echo "<thead><tr>";
          echo "<th scope='col'>Size</th>";
          echo "<th scope='col'>Time</th>";
          echo "<th scope='col'>File</th>";
          echo "</thead><tbody>";
	  
	  $file = fopen('../storage/assignments', 'r')	or exit("<p style='color:red'>No assignment IDs found.</p>");
	  while (!feof($file)) {
	      $line = trim(fgets($file, 1024));
	      if ($line) {
		  $delimiter="\t";
		  $split_line=explode($delimiter,$line);
		  $ext=explode(".",$split_line[1])[1];
		  $output = shell_exec("cd ../storage/$split_line[0]/; ls ". strtolower($user_name) . "*." . $ext . " | cut -f3- -d' ' | grep -v 'returned'");
		  $returned = shell_exec("cd ../storage/$split_line[0]/; ls ". strtolower($user_name) . "*-returned.pdf | cut -f3- -d' '");
		  if ($output . $returned) {
		      echo "<tr class='table-dark'><td colspan='3'><b>$split_line[0]</b></td></tr>";
		      if( $output ){
			  foreach (explode(PHP_EOL,trim($output)) as &$temp) {
			      $filesize=humanFileSize(filesize("../storage/$split_line[0]/$temp"));
			      $filemodtime=date("h:i:s\&\\n\b\s\pA m-d-Y", filemtime("../storage/$split_line[0]/$temp"));
			      echo "<tr><td>$filesize</td><td>$filemodtime</td><td>$temp</td></tr>";
			  }
		      }
		      if( $returned ){
                          foreach (explode(PHP_EOL,trim($returned)) as &$temp) {
			      $filesize=humanFileSize(filesize("../storage/$split_line[0]/$temp"));
			      $filemodtime=date("h:i:s\&\\n\b\s\pA m-d-Y", filemtime("../storage/$split_line[0]/$temp"));
			      echo "<tr class='table-success'><td>$filesize</td><td>$filemodtime</td><td>$temp<b>&nbsp(Feedback)</b></td></tr>";
			  }
		      }
		  }
	      }
	  }
	  fclose($file);
	  ?>
      </tbody>
      </table>
      </div>
      <div id="footer"></div>
	  
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script>$(window).bind("pageshow", function() {
         $('#assignment-form')[0].reset();
      });</script>
    <script src="common.js"></script>
  </body>
</html>
