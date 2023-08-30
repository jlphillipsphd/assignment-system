<?php
require_once 'auth.php';

if (isset($_POST["logout"])) {
    header("Location: " . $server_url . $base_url . "hub/home");
    exit();
}

function humanFileSize($size,$unit="") {
    if( (!$unit && $size >= 1<<30) || $unit == "GB")
        return number_format($size/(1<<30),2)." GB";
    if( (!$unit && $size >= 1<<20) || $unit == " MB")
        return number_format($size/(1<<20),2)." MB";
    if( (!$unit && $size >= 1<<10) || $unit == "KB")
        return number_format($size/(1<<10),2)." KB";
    return number_format($size)." bytes";
}

$output_header = <<<EOF
<!doctype html>
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
    <!-- <link rel="stylesheet" type="text/css" href="css/page.css"> -->
    <meta http-equiv="Content-language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
    <meta name="robots" content="all">
  </head>
  <body>
    <main role="main">
      <div class="jumbotron">
	<div class="container text-center">	  
	  <h1>$course_number</h1>
	  <h2>$course_name</h2>
	  <h3>Assignment Submission System</h3>
	</div>
      </div>
      <div class="container" id="content">
      <div class='row'><div class='col-sm-3'></div><div class='col-sm-6'>
EOF;

$output_footer = <<<EOF
      </div>
      <div class='col-sm-3'></div>
      </div>
      </div>
      <br/>
      <div id="footer"></div>
	  
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="common.js"></script>
  </body>
</html>
EOF;

if (!isset($_POST["assignment"])) {
    echo $output_header;
    echo "<table class='table'><tbody><tr class='table-danger'><td colspan='2'>Submission Failed</td></tr></tbody></table>";
    echo "<br/><b> Your request failed because you did not select an appropriate assignment.</b><br/><br/>";
    echo "Please select an assignment from the dropdown menu to complete your request.<br/>";
    echo "<br/>";
    echo "<a class='btn btn-primary' href=\"index.php\">Return to Submission System</a><br/>";
    echo $output_footer;
    exit();
}

$file = fopen('../storage/assignments', 'r') 
or exit("<p style='color:red'>No assignments available for turnin.</p>");
$count = 0;
while (!feof($file)) {
    $line = trim(fgets($file, 1024));
    if ($line) {
	$delimiter="\t";
	$split_line=explode($delimiter,$line);
	$assgn=explode(" ",$_POST["assignment"]);
	if ($split_line[0] == $assgn[0]) {
	    break;
	}
	$count++;
    }
}
fclose($file);

if (isset($_POST["submit"])) {
    if (!($_FILES["file"]["name"])) {
	echo $output_header;
	echo "<table class='table'><tbody><tr class='table-danger'><td colspan='2'>Submission Failed</td></tr></tbody></table>";
	echo "<br/><b> Your request failed because you did not select a file for uploading.</b><br/><br/>";
	echo "Please select your file using the file selection dialog to complete your request.<br/>";
	echo "<br/>";
	echo "<a class='btn btn-primary' href=\"index.php\">Return to Submission System</a><br/>";
	echo $output_footer;
	exit();
    }

    echo $output_header;
    echo "<h1>Assignment: " . $split_line[0] . "</h1>";
    echo "<table class='table'><tbody>";
    $split_fn=explode(".",$split_line[1]);
    $assignment=$split_fn[0];
    $allowedExts=$split_fn[1];
    echo "<tr><th scope='row'>Required Extension</th><td>" . $split_fn[1] . "</td><tr>";
    $temp = explode(".", $_FILES["file"]["name"]);
    $filename = $_FILES["file"]["tmp_name"];
    $extension = strtolower(end($temp));
    $filetype = explode(",",shell_exec("file -b $filename"))[0];
    if ($extension == $allowedExts) {
	if ($_FILES["file"]["error"] > 0) {
	    echo "<tr class='table-danger'><td colspan='2'>Submission Failed</td></tr></tbody></table><br/><b>Your submission failed with return code: " . $_FILES["file"]["error"] . "<br/>This is probably because your file was too large.<br/></b>";
	}
	else {
	    if (($filetype == "Zip archive data" && $allowedExts == "zip") || ($filetype == "PDF document" && $allowedExts == "pdf")) {
		$long_name= "../storage/" . $split_line[0] . "/" . strtolower($user_name) . "-" . $assignment . "-" . $_SERVER["REQUEST_TIME"] . "." . $allowedExts;
		echo "<tr><th scope='row>User</th><td>" . strtolower($user_name) . "</td></tr>";
		echo "<tr><th scope='row'>Upload</th><td>" . $_FILES["file"]["name"] . "</td></tr>";
		echo "<tr><th scope='row'>Type</th><td>" . $_FILES["file"]["type"] . "</td></tr>";
		echo "<tr><th scpoe='row'>Size</th><td>" . humanFileSize($_FILES["file"]["size"]) . "</td></tr>";
		// echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br/>";
                
		if (file_exists($long_name)) {
		    echo "<tr><th scope='row'>File</th><td>". $long_name . " already exists!</td></tr><tr class='table-danger'><td colspan='2'>Submission Failed</td></tr></tbody></table>";
		}
		else {
		    move_uploaded_file($_FILES["file"]["tmp_name"],$long_name);
		    $sum=md5_file($long_name);
		    if (file_exists($long_name)) {
			echo "<tr><th scope='row'>Stored as</th><td>" . basename($long_name) . "</td></tr>";
			echo "<tr><th scope='row'>MD5 Sum</th><td>" . $sum . "</td></tr>";
                        echo "<tr class='table-success'><td colspan='2'>Submission Successful</td></tr>";
                        echo "</tbody></table>";
		    }
		    else {
			echo "<tr class='table-danger'><td colspan='2'>Submission Failed</td></tr></tbody></table><br/>";
			echo "<b>Your submission failed for an unknown reason.<br/>Please contact the course instructor.</b><br/>";
		    }
		}
	    }
	    else {
		switch($allowedExts) {
		    case "zip":
			echo "<tr class='table-danger'><td colspan='2'>Submission Failed</td></tr></tbody></table><br/><b> Your submission failed because you did not upload the correct file type: ZIP archive (.zip)</b><br/><br/>";
			echo "Please prepare your assignment using the correct format for upload.<br/>" ;
			echo "Note: You <b>cannot</b> simply rename your file using a .zip extention for this to work!<br/>";	
			break;
		    case "pdf":
			echo "<tr class='table-danger'><td colspan='2'>Submission Failed</td></tr></tbody></table><br/><b> Your submission failed because you did not upload the correct file type: PDF document (.pdf)</b><br/><br/>";
			echo "Please prepare your assignment using the correct format for upload.<br/>";
			echo "Note: You <b>cannot</b> simply rename your file using a .pdf extention for this to work!<br/>";	
			break;
		}
		
	    }
	}
    }
    else {
	echo "<tr class='table-danger'><td colspan='2'>Submission Failed</td></tr></tbody></table><br/>The submitted file does have a valid extension (" . $extension . ").  Please revise and resubmit a ." . $allowedExts . " file.<br/>";
    }
    echo "<br/>";
    echo "<a class='btn btn-primary' href=\"index.php\">Return to Submission System</a><br/>";

    echo $output_footer;
}
else if (isset($_POST["download"])) {
    $split_fn=explode(".",$split_line[1]);
    $assn=$split_fn[0];
    $allowedExts=$split_fn[1];
    
    // $file = trim(shell_exec("ls ../storage/$split_line[0]/". strtolower($user_name) . "*-??????????." . $allowedExts . " | tail -1"));

    $file = "../storage/" . strtolower($user_name) . "-" . $assn . ".zip";
    $archive = new ZipArchive();
    $archive->open($file, ZipArchive::CREATE);
    $gotstuff = $archive->addGlob("../storage/$split_line[0]/". strtolower($user_name) . "*-??????????." . $allowedExts,0,array('add_path' => $assn . "/",'remove_all_path' => TRUE));
    $archive->close();

    if (file_exists($file) && $gotstuff) {
	header('Content-Description: File Transfer');
	/* header('Content-Type: application/x-'.$allowedExts); */
	header('Content-Type: application/x-zip');
	header('Content-Disposition: attachment; filename='.basename($file));
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file));
	readfile($file);
        unlink($file);
    }
    else {
	echo $output_header;
	echo "<h1>Assignment: " . $split_line[0] . "</h1>";
	echo "<br/><b>You have not uploaded any submissions for this assignment!</b><br/>";
    }
    echo "<br/>";
    echo "<a class='btn btn-primary' href=\"index.php\">Return to Submission System</a><br/>";
    echo $output_footer;
}
else if (isset($_POST["feedback"])) {
    $file = shell_exec("ls ../storage/$split_line[0]/". strtolower($user_name) . "*-returned.pdf | tail -1");
    if (!is_null($file) && file_exists(trim($file))) {
	$file = trim($file);
	header('Content-Description: File Transfer');
	header('Content-Type: application/x-pdf');
	header('Content-Disposition: attachment; filename='.basename($file));
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file));
	readfile($file);
    }
    else {
	echo $output_header;
	echo "<h1>Assignment: " . $split_line[0] . "</h1>";
	echo "<br/><b>This assignment has not yet been graded/returned!</b><br/>";
    }
    echo "<br/>";
    echo "<a class='btn btn-primary' href=\"index.php\">Return to Submission System</a><br/>";
    echo $output_footer;
}
else {
    exit("Forbidden");
}
?> 
