<?php
	session_start();
	include("includes/config.php");

	if($_SESSION['loggeduser'] == '') 
	{
		header("Location: index.php"); 
	}
	
	$userDet = get_user_details($_SESSION['loggeduser']);
	
  if(isset($_POST['extract_file'])) 
  {   
	  $file_id = $_POST['selectedfile'];
	  $fileinf = get_file($file_id);
	  $destination_dir = $_POST['destdir'];
	  
	  include("includes/xmlapi.php");
	  $gethost = $mysqli->query("SELECT * FROM `tbl_config` WHERE `id`='1'");
	  if($gethost->num_rows > 0)
	  {
			$config = $gethost->fetch_object();
			$host = $config->cPanelHost;
			$my_user = $config->cPanelUser;
			$my_pass = $config->cPanelPass; 
	  }
	  
	  $xmlapi = new xmlapi($host);
	  $xmlapi->set_port(2083);
	  $xmlapi->password_auth($my_user, $my_pass);
	  $xmlapi->set_output('json');
	  
	  // add addon domain to cpanel
	  $result = $xmlapi->api2_query($my_user, 'Fileman', 'fileop', array(
			'op'            => "extract", 
			'sourcefiles'      =>  $fileinf->folder_name . '/' . $fileinf->file_name,
			'destfiles'      => $destination_dir,
			'doubledecode'		=> '1'
	   ));
	   
	   $datetimenow = date("Y-m-d H:i:s");
	   $updatetbl = $mysqli->query("UPDATE `tbl_files` SET `extracted_on`='$datetimenow' WHERE `id`='$file_id'");
	   if($updatetbl)
	   {
	       $resultstatus = '<div style="color:#3C0">File Extracted Successfully</div>';
	   }
	   else {
	       $resultstatus = '<div style="color:#F00">Error Extracting File</div>';
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
	  <a href="logout.php"><div class="menubutton" style="border-right: 0px !important">Logout</div></a>
  </div>
  <div class="allcontent">
  	  
      <br />
      <div class="form" style="margin-bottom:20px">
      <h3>Extract File</h3>
	  <?php
	  if(isset($resultstatus)) {
            echo $resultstatus;
      }
    ?>
     	  <form class="register-request" method="post" action="extractfile.php">
            <select name="selectedfile" required>
            	<option value="">Select File</option>
                <?php 
				$getFiles = $mysqli->query("SELECT * FROM `tbl_files`");
				while($efiles = $getFiles->fetch_object())
				{
				?>
                <option value="<?=$efiles->id?>"<?php if(isset($_GET['extract']) == $efiles->id) { echo ' selected="selected"'; } ?>><?=$efiles->file_name?></option>
                <?php } ?>
            </select>
            <select name="destdir" required>
            	<option value="">Destination</option>
                <?php 
				$getAddon = $mysqli->query("SELECT * FROM tbl_addondom");
				while($addon = $getAddon->fetch_object())
				{
				?>
                <option value="<?=$addon->dom_dir?>"><?=$addon->dom_name?></option>
                <?php
					$getSub = $mysqli->query("SELECT * FROM `tbl_subdomain` WHERE `parent_domain`='$addon->id'");
					while($subdet = $getSub->fetch_object())
					{ ?>
						<option value="<?=$subdet->files_directory?>">-- <?=$subdet->full_domain?></option>
					<?php }
				} ?>
            </select>
            <button name="extract_file">Extract</button>
          </form> 
          
	  </div>
      
  </div>
</div>
</body>
</html>