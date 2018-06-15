<?php
	session_start();
	include("includes/config.php");

	if($_SESSION['loggeduser'] == '') 
	{
		header("Location: index.php"); 
	}
	
	$userDet = get_user_details($_SESSION['loggeduser']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome <?php echo $_SESSION['loggeduser']; ?></title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>
  <div class="all-pages">
  <h2 style="text-align:center">Welcome <?php echo $userDet->fullname; ?></h2>
  <div style="height: 50px">
	  <a href="home.php"><div class="menubutton active">Home</div></a>
	  <a href="addondomains.php"><div class="menubutton">Addon Domains</div></a>
	  <a href="subdomains.php"><div class="menubutton">Sub Domains</div></a>
      <a href="cpanels.php"><div class="menubutton">cPanel Accounts</div></a>
	  <a href="uploadfiles.php"><div class="menubutton">Upload Files</div></a>
	  <a href="automatic.php"><div class="menubutton">Automatic</div></a>
	  <a href="logout.php"><div class="menubutton" style="border-right: 0px !important">Logout</div></a>
  </div>
  <div class="allcontent">Welcome <?php echo $userDet->fullname; ?> to Admin Panel. <br /><br />Please select menu to perform any action<br /><br />Thanks</div>
</div>
</body>
</html>