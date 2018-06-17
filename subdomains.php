<?php
	session_start();
	include("includes/config.php");

	if($_SESSION['loggeduser'] == '') 
	{
		header("Location: index.php"); 
	}
	
	$userDet = get_user_details($_SESSION['loggeduser']);
	
    if(isset($_POST['add_subdom']) && !empty($_POST))
    {
        include("includes/xmlapi.php");

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

        $xmlapi = new xmlapi($host);
        $xmlapi->set_port(2083);
        $xmlapi->password_auth($my_user, $my_pass);
        $xmlapi->set_output('json');
		
		$rootdomain = mysqli_real_escape_string($mysqli, $_POST['maindomain']);
		$addonDom = get_addondet($rootdomain);
		
		$reqsubdomain = mysqli_real_escape_string($mysqli, $_POST['subname']);
		$subdirectory = mysqli_real_escape_string($mysqli, $_POST['subdomaindirectory']);
		
		$ifexist = $mysqli->query("SELECT * FROM `tbl_subdomain` WHERE `shortname`='$reqsubdomain'");
		if($ifexist->num_rows > 0)
		{
			$status = "Sub Domain Already Exist";
		}
		else {
			// Create a subdomain.
			$addsubdomain = $xmlapi->api2_query($my_user,
				'SubDomain', 'addsubdomain', 
					array(
						'domain'                => trim($reqsubdomain), // subdomain
						'rootdomain'            => trim($addonDom->dom_name), // example.com
						'dir'                   => '/public_html/' . $addonDom->dom_dir .'/'. trim($subdirectory), // '/public_html/subdomain'
						'disallowdot'           => '1',
					)
			);
		}

        //echo $addsubdomain;
        $res = json_decode($addsubdomain, TRUE);
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
	  <a href="subdomains.php"><div class="menubutton active">Sub Domains</div></a>
      <a href="cpanels.php"><div class="menubutton">cPanel Account</div></a>
	  <a href="uploadfiles.php"><div class="menubutton">Upload Files</div></a>
	  <a href="automatic.php"><div class="menubutton">Automatic</div></a>
	  <a href="logout.php"><div class="menubutton" style="border-right: 0px !important">Logout</div></a>
  </div>
  <div class="allcontent">
  	  
      <br />
      <?php
        if(isset($_POST) && !empty($_POST))
        {
			$result = json_encode($res);
			$subdomainis = explode("\u201c", $result);
			$realsub = explode("\u201d", $subdomainis[1]);
			
			$shortname = $_POST['subname'];
			$parentid = $_POST['maindomain'];
			
			$addonDomn = get_addondet($rootdomain);
			$fulldir = $addonDomn->dom_dir .'/'. trim($_POST['subdomaindirectory']);
			$datetimenow = date('Y-m-d H:i:s');
			
			$addsub = $mysqli->query("INSERT INTO `tbl_subdomain`(`shortname`, `parent_domain`, `full_domain`, `files_directory`, `added_on`, `added_by`) VALUES ('$shortname', '$parentid', '$realsub[0]', '$fulldir', '$datetimenow', '$userDet->id')");
			if($addsub) {
				echo '<div style="color:#1aac07">Subdomain (' . $realsub[0] . ') has been added successfully.<br /><br /></div>';
			}
        }
      ?>
      <div class="form" style="margin-bottom:20px">
      <h3>Add New Sub-Domain</h3>
	  <?php
	  if(isset($status)) { ?>
		  <p class="message"><?=$status?></p>
      <?php } else { ?>
     	  <form class="register-request" method="post" action="subdomains.php">
          	<input type="text" id="subname" placeholder="Name" name="subname" onchange="subname_changed(this.value);" required />
            <select name="maindomain" required>
            	<option value="">Select Parent Domain</option>
                <?php 
				$getAddon = $mysqli->query("SELECT * FROM tbl_addondom");
				while($addon = $getAddon->fetch_object())
				{
				?>
                <option value="<?=$addon->id?>"><?=$addon->dom_name?></option>
                <?php } ?>
            </select>
            <input type="text" id="edtDir" name="subdomaindirectory" placeholder="Directory" required />
            <button name="add_subdom">Add Sub-Domain</button>
          </form>
          <script>
              function subname_changed(domainname) {
                  // constants for numerical suffix
                  var UPPER = 1000; // = 10 ^ SIZE
                  var SIZE = 3;

                  var dirname = (domainname.split("."))[0];
                  dirname += ("" + UPPER + Math.floor(Math.random() * UPPER)).substr(-SIZE);
                  document.getElementById("edtDir").value = dirname;
              }
          </script>
      <?php } ?>
	  </div>
      
      
      
	  <h3>All Sub-Domains</h3>
	  <table>
	  <tr>
		  <td>ID</td>
		  <td>Domain Name</td>
		  <td>Directory</td>
          <td>Created On</td>
	  </tr>
	  <?php 
		  $getAddon = $mysqli->query("SELECT * FROM `tbl_subdomain`");
		  while($addon = $getAddon->fetch_object())
		  {
      ?>
	  <tr>
		  <td><?=$addon->id?></td>
		  <td><?=$addon->full_domain?></td>
		  <td>/public_html/<?=$addon->files_directory?></td>
          <td><?=$addon->added_on?></td>
	  </tr>
	  <?php } ?>  
		  
	  </table>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script type="text/javascript">
$('#subname').bind("keypress", function(event) { 
	var charCode = event.which;

	if(charCode == 8 || charCode == 0)
	{
		 return;
	}
	else
	{
		var keyChar = String.fromCharCode(charCode); 
		return /[a-zA-Z0-9]/.test(keyChar); 
	}
});
</script>
</body>
</html>