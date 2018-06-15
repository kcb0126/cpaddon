<?php
	session_start();
	include("includes/config.php");

	if($_SESSION['loggeduser'] == '') 
	{
		header("Location: index.php"); 
	}
	
	$userDet = get_user_details($_SESSION['loggeduser']);
	  
    if(isset($_POST['add_file']) && !empty($_POST))
    {
		$gethost = $mysqli->query("SELECT * FROM `tbl_config` WHERE `id`='1'");
		if($gethost->num_rows > 0)
		{
			$config = $gethost->fetch_object();
			$host = $config->cPanelHost;
			$my_user = $config->cPanelUser;
			$my_pass = $config->cPanelPass; 
		}
		else
		{
			die("cPanel Details Required");
		}

		$productImage = $_FILES["fileup"]["name"];
		$temp = explode(".", $_FILES["fileup"]["name"]);
		$extension = end($temp);
			
		$fileData = pathinfo(basename($_FILES["fileup"]["name"]));
		$fileName = trim($temp[0]) . '.' . $fileData['extension'];
			
		$target_path = "uploads/" . preg_replace('/\s+/', '', $fileName);
		$filepath = preg_replace('/\s+/', '', $fileName);
				
		if(move_uploaded_file($_FILES["fileup"]["tmp_name"], $target_path))
		{
			$request_uri = "https://$host:2083/execute/Fileman/upload_files";
			
			// Define the filename and destination.
			$upload_file = realpath("$target_path");
			$destination_dir = 'public_html';
			
			// Set up the payload to send to the server.
			if( function_exists( 'curl_file_create' ) ) {
				$cf = curl_file_create( $upload_file );
			} else {
				$cf = "@/".$upload_file;
			}
			$payload = array(
				'dir'    => $destination_dir,
				'file-1' => $cf
			);
			
			// Set up the cURL request object.
			$ch = curl_init( $request_uri );
			curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
			curl_setopt( $ch, CURLOPT_USERPWD, $my_user . ':' . $my_pass );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			
			// Set up a POST request with the payload.
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			
			// Make the call, and then terminate the cURL caller object.
			$curl_response = curl_exec( $ch );
			curl_close( $ch );
			
			// Decode and validate output.
			$response = json_decode( $curl_response );
			
			if( empty( $response ) ) {
				echo "The cURL call did not return valid JSON:\n";
				die( $response );
			} elseif ( !$response->status ) {
				echo "The cURL call returned valid JSON, but reported errors:\n";
				die( $response->errors[0] . "\n" );
			}
			else {
			    $realfilename = preg_replace('/\s+/', '', $fileName);
			    $datetimenow = date("Y-m-d H:i:s");
			    $addrecord = $mysqli->query("INSERT INTO `tbl_files`(`file_name`, `folder_name`, `added_on`, `extracted_on`, `added_by`) VALUES ('$realfilename', '$destination_dir', '$datetimenow', '', '$userDet->id')");
			}
		}
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome <?php echo $userDet->fullname; ?></title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>
  <div class="all-pages">
  <h2 style="text-align:center">Welcome <?php echo $userDet->fullname; ?></h2>
  <div style="height: 50px">
	  <a href="home.php"><div class="menubutton">Home</div></a>
	  <a href="addondomains.php"><div class="menubutton">Addon Domains</div></a>
	  <a href="subdomains.php"><div class="menubutton">Sub Domains</div></a>
      <a href="cpanels.php"><div class="menubutton">cPanel Account</div></a>
	  <a href="uploadfiles.php"><div class="menubutton active">Upload Files</div></a>
	  <a href="automatic.php"><div class="menubutton">Automatic</div></a>
	  <a href="logout.php"><div class="menubutton" style="border-right: 0px !important">Logout</div></a>
  </div>
  <div class="allcontent">
  	  
      <br />
      <div class="form" style="margin-bottom:20px">
      <h3>Upload Zip File</h3>
	  <?php
	  if(isset($_POST) && !empty($_POST))
      {
        if(strpos($response, "Upload") !== false) {
            echo '<div style="color:#3C0">File Uploaded Successfully</div>';
        }
        else {
            echo '<div style="color:#F00">Error uploading file. Please try again</div>';
        }
      }
    ?>
     	  <form class="register-request" enctype="multipart/form-data" method="post" action="uploadfiles.php">
          	<input type="file" id="file" placeholder="fileupload" name="fileup" accept=".zip" required />
            
            <button name="add_file">Upload</button>
          </form> 
          
	  </div>
     
	  <h3>All Uploaded Files</h3>
	  <table>
	  <tr>
		  <td>ID</td>
		  <td>File Name</td>
		  <td>Directory</td>
          <td>Created On</td>
          <td>Extracted On</td>
	  </tr>
	  <?php 
		  $getFiles = $mysqli->query("SELECT * FROM `tbl_files`");
		  while($gfiles = $getFiles->fetch_object())
		  {
      ?>
	  <tr>
		  <td><?=$gfiles->id?></td>
		  <td><?=$gfiles->file_name?></td>
		  <td><?=$gfiles->folder_name?></td>
          <td><?=$gfiles->added_on?></td>
          <td><?php if($gfiles->extracted_on == '') { echo '<a href="extractfile.php?extract='.$gfiles->id.'">Extract Now</a>'; } else { echo $gfiles->extracted_on; } ?></td>
	  </tr>
	  <?php } ?>  
		  
	  </table>
  </div>
</div>
</body>
</html>