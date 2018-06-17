<?php
	session_start();
	include("includes/config.php");

	if($_SESSION['loggeduser'] == '') 
	{
		header("Location: index.php"); 
	}
	
	$userDet = get_user_details($_SESSION['loggeduser']);
	
    if(isset($_POST['add_addon']) && !empty($_POST))
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
        //$xmlapi->set_debug(1);  
		$domainname = $_POST['domainname'];
		$getsubdom = explode(".", $domainname);
		$subdomain = $getsubdom[0];

        // add addon domain to cpanel
        $result = $xmlapi->api2_query($my_user, 'AddonDomain', 'addaddondomain', array(
                'dir'            => "public_html/" . $_POST['domaindirectory'], //'public_html/logictest.com', 
                'newdomain'      => $domainname, //'logictest.com', 
                'subdomain'      => $subdomain, //'logictest',
            ));

        //echo $result;

        $res = json_decode($result, TRUE);

//        echo "<pre>";
//        print_r($res);
//        echo "</pre>";
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
	  <a href="addondomains.php"><div class="menubutton active">Addon Domains</div></a>
	  <a href="subdomains.php"><div class="menubutton">Sub Domains</div></a>
      <a href="cpanels.php"><div class="menubutton">cPanel Accounts</div></a>
	  <a href="uploadfiles.php"><div class="menubutton">Upload Files</div></a>
	  <a href="automatic.php"><div class="menubutton">Automatic</div></a>
	  <a href="logout.php"><div class="menubutton" style="border-right: 0px !important">Logout</div></a>
  </div>
  <div class="allcontent">
  	  
      <br />
      <?php
        $success = -1;
        if(isset($_POST) && !empty($_POST))
        {
            if (array_key_exists('error', $res['cpanelresult'])) {
                $success = 0;
			// MARK: if (strpos($result, "error") !== false) {
				$resnow = explode('Sorry', $result);
				// MARK: $resisnow = explode(' or have your administrator', $resnow[1]);
				// MARK: echo '<div style="color:#F00">Sorry' . $resisnow[0] . '.<br /><br /></div>';
                $errmsgs = explode(')', $res['cpanelresult']['error']);
                if(array_key_exists(1, $errmsgs)) {
                    $errmsg = $errmsgs[1];
                } else {
                    $errmsg = $errmsgs[0];
                }
                echo '<div style="color:#F00">' . $errmsg . '<br /><br /></div>';
			}
			else {
                $success = 1;
				$resultnow = explode('"reason":"', $result);
				$resultnowis = explode('","result":', $resultnow[1]);
				
				$domainname = $_POST['domainname'];
				$getsubdom = explode(".", $domainname);
				$subdomain = $getsubdom[0];
				$ftpdirectory = $_POST['domaindirectory'];
				$datetimenow = date('Y-m-d H:i:s');
				
				$adddom = $mysqli->query("INSERT INTO `tbl_addondom`(`dom_name`, `dom_dir`, `created_on`, `created_by`) VALUES ('$domainname', '$ftpdirectory', '$datetimenow', '$userDet->id')");
				if($adddom) {
					echo '<div style="color:#1aac07">' . $resultnowis[0] . '<br /><br /></div>';
				}
				else echo $mysqli->error;
			}
        }
      ?>
      <div class="form" style="margin-bottom:20px">
      <h3>Add New Addon Domain</h3>
	  <?php
	  if(isset($status)) { ?>
		  <p class="message"><?=$status?></p>
      <?php } else { ?>
     	  <form class="register-request" method="post" action="addondomains.php">
          	<input id="edtDomain" type="text" placeholder="Name" name="domainname" onchange="domainname_changed(this.value);" required />
            <input id="edtDir" type="text" name="domaindirectory" placeholder="Directory" required />
              <?php if($success == 0) { ?>
                    <div style="color:#F00;">Failed</div>
              <?php } ?>
              <?php if($success == 1) { ?>
                  <div style="color:#1aac07">Success</div>
              <?php } ?>
            <button name="add_addon">Add Domain</button>
          </form>
          <script>
              function domainname_changed(domainname) {
                  // constants for numerical suffix
                  var UPPER = 1000; // = 10 ^ SIZE
                  var SIZE = 3;

                  var dirname = (domainname.split("."))[0];
                  console.log(dirname);
                  dirname += ("" + UPPER + Math.floor(Math.random() * UPPER)).substr(-SIZE);
                  document.getElementById("edtDir").value = dirname;
              }
          </script>
      <?php } ?>
	  </div>
      
      
      
	  <h3>All Addon Domains</h3>
	  <table>
	  <tr>
		  <td>ID</td>
		  <td>Domain Name</td>
		  <td>Check</td>
          <td>Created On</td>
          <td>Delete</td>
	  </tr>
	  <?php
          if(isset($_GET) && array_key_exists("id", $_GET)) {
              $mysqli->query("DELETE FROM tbl_addondom WHERE id = ".$_GET['id']);
          }

		  $getAddon = $mysqli->query("SELECT * FROM tbl_addondom");
		  while($addon = $getAddon->fetch_object())
		  {
      ?>
	  <tr>
		  <td><?=$addon->id?></td>
		  <td><?=$addon->dom_name?></td>
<!--		  <td>/public_html/--><?//=$addon->dom_dir?><!--</td>-->
          <td><a href="//<?=$addon->dom_name?>/check.php">Check this domain</a> </td>
          <td><?=$addon->created_on?></td>
          <td><a href="addondomains.php?id=<?=$addon->id?>">Delete</a></td>
	  </tr>
	  <?php } ?>  
		  
	  </table>
  </div>
</div>
</body>
</html>