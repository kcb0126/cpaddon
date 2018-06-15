<?php
	session_start();
	include("includes/config.php");

	if($_SESSION['loggeduser'] == '') 
	{
		header("Location: index.php"); 
	}
	
	$userDet = get_user_details($_SESSION['loggeduser']);
	
    if(isset($_POST['add_cpanel']))
    {
		$hosttitle = mysqli_real_escape_string($mysqli, $_POST['cpname']);
		$hostname = mysqli_real_escape_string($mysqli, $_POST['cphost']);  
		$hostuser = mysqli_real_escape_string($mysqli, $_POST['cpuser']);
		$hostpass = mysqli_real_escape_string($mysqli, $_POST['cppass']);
		
		$updatecpanel = $mysqli->query("UPDATE `tbl_config` SET `title`='$hosttitle',`cPanelHost`='$hostname',`cPanelUser`='$hostuser',`cPanelPass`='$hostpass' WHERE `id`='1'");
		if($updatecpanel)
		{
			$status = "cPanel Details Updated Successfully.";
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
      <a href="cpanels.php"><div class="menubutton active">cPanel Account</div></a>
	  <a href="uploadfiles.php"><div class="menubutton">Upload Files</div></a>
	  <a href="automatic.php"><div class="menubutton">Automatic</div></a>
	  <a href="logout.php"><div class="menubutton" style="border-right: 0px !important">Logout</div></a>
  </div>
  <div class="allcontent">
  	  
      <br />
      <?php
         $getconf = $mysqli->query("SELECT * FROM `tbl_config` WHERE `id`='1'");
		 $conf = $getconf->fetch_object();
      ?>
      <div class="form" style="margin-bottom:20px">
      <h3>cPanel Account Setting</h3>
	  <?php
	  if(isset($status)) { ?>
		  <p class="message"><?=$status?></p>
      <?php } else { ?>
     	  <form class="register-request" method="post" action="cpanels.php">
          	<input type="text" placeholder="Account Name / Title" value="<?=$conf->title?>" name="cpname" required />
          	<input type="text" placeholder="Host Name or IP" value="<?=$conf->cPanelHost?>"  name="cphost" required />
            <input type="text" placeholder="cPanel Username" value="<?=$conf->cPanelUser?>"  name="cpuser" required />
            <input type="password" placeholder="cPanel Password" value="<?=$conf->cPanelPass?>"  name="cppass" required />
            <button name="add_cpanel">Save / Update</button>
          </form> 
      <?php } ?>
	  </div>
      
		  
  </div>
</div>
</body>
</html>