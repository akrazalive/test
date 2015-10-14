<?php 
/*
Plugin Name: Wp Xapo Faucet
Author: XYZ
Description: This plugin integrates Xapo along with solvemedia info to get you bitcoins.
Author URI: coolingneurons.com
Version: 4.9
*/


/******** Tables Creation Function STARTS*******************/

function create_xf_tables()
{
	global $wpdb;
	global $jal_db_version;

	// inserting table code starts here
   
   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
    $wpdb->prefix = "wp_" ;
    // Data Table code Start
	$table1 = $wpdb->prefix . 'xf_data';
	
	$sql1= "CREATE TABLE IF NOT EXISTS  $table1 (
`id` int(11) NOT NULL,
  `user` text NOT NULL,
  `amount` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `result` tinyint(1) DEFAULT NULL,
  `message` text
);
" ;  
	dbDelta( $sql1 );

    // Data Table Code Ends
    
   
   // Data Refferal Table Starts
	$table2 = $wpdb->prefix . 'xf_data_referals';
   $sql2 ="CREATE TABLE IF NOT EXISTS $table2  (
`id` int(11) NOT NULL,
  `user` text NOT NULL,
  `referrer` text NOT NULL,
  `amount` int(11) NOT NULL,
  `date` date NOT NULL,
  `result` tinyint(1) NOT NULL,
  `message` text NOT NULL
);";
   
   	dbDelta( $sql2 );

   // Data Refferal Table Ends	
   
   
   //Referal Table Code Starts
   
   $table3 = $wpdb->prefix . 'xf_referals';
   $sql3="CREATE TABLE IF NOT EXISTS $table3  (
 `username` text NOT NULL,
  `reffered_by` text
);";
   	dbDelta( $sql3 );

   //Referal Table Code Ends
   
   
    //Xapo User Table Code Starts
   
   $table4 = $wpdb->prefix . 'xf_users';
   $sql4 ="CREATE TABLE IF NOT EXISTS $table4 (
  `username` varchar(50) NOT NULL,
  `ip` text NOT NULL,
  `claimed_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
   dbDelta( $sql4 );
   
   //Xapo User Table Code Ends

	
	
	// Code ends here
}

register_activation_hook( __FILE__, 'create_xf_tables' );

/******** Tables Creation Function ENDS*******************/

// Initialize the plugin


/******** Function that load required files after init hook STARTS **********/
function initiate_xapo_faucet()
{   
    require_once dirname( __FILE__ ) . '/admin/class.settings-api.php';
    require_once dirname( __FILE__ ) . '/admin/oop-example.php';

    new WeDevs_Settings_API_Test();

	require 'config.php';
    require 'functions.php';
	
}

add_action('init', 'initiate_xapo_faucet');


function initiate_xapo_admin()
{
	
}

add_action('admin_init', 'initiate_xapo_admin');


/******** Function that load required files after init hook ENDS **********/



/******** Shortcode function handling the form STARTS*******************/

function shortcode_handler()
{  

global $wpdb ;
$settings      = array();
$time          = time();
$myHashKey = "8c278cfb4784588efd29effc613e5983";


   $settings =  get_option('wedevs_basics');
   
  $GLOBALS["settings"] = $settings ;  
  $GLOBALS['hashKey'] = $myHashKey ;
    
  $rewards = get_rewards();

    if(isset($_POST['xapoform']) && $_POST['xapoform']=="posted")
    {
     
	 
  $view['main']['result_html']  = '';
  $view['main']['waiting_time'] = 0;
  $success                      = "false";
  $ip                           = get_ip();

  //Checks that the username is not empty
  if (!isset($_POST['username'])||$_POST['username']=="") {

    $view['main']['result_html'] = '<div class="row text-center"><div class="col-sm-6 col-md-offset-3 bg-danger"><p>Missing email address!</p></div></div>';
    $message                     = "Missing email address";
     
	goto b; 
  }

  $username = $_POST['username'];
  //Checks if the user has written something in the captcha box

  $captchaChallange = $_POST['adcopy_challenge'];
  $captchaResponse  = $_POST['adcopy_response'];

  if (empty($captchaChallange) || empty($captchaResponse)) {

    $view['main']['result_html'] = '<div class="row text-center"><div class="col-sm-6 col-md-offset-3 bg-danger"><p>Missing captcha, try again!</p></div></div>';
    $message                     = "Missing captcha";
    
	goto b;
  }


  $response = @file('http://verify.solvemedia.com/papi/verify?privatekey=' . $settings['solvemedia_verification_key'] . '&challenge=' . rawurlencode($captchaChallange) . '&response=' . rawurlencode($captchaResponse) . '&remoteip=' . $ip);

  if (!isset($response[0]) || trim($response[0]) === 'false'){
    $view['main']['result_html'] = '<div class="row text-center"><div class="col-sm-6 col-md-offset-3 bg-danger"><p>Wrong captcha!</p></div></div>';
    $message                     = "Wrong captcha";
    
	goto b;
  }

/*  $q = $sql->prepare("select * from wp_xf_users where LOWER(username) = LOWER(?) or ip = ? order by claimed_at desc");
  $q->execute(array($username,$ip));
  $row = $q->fetch();*/
  
  // wordpress version starts
   $row = $wpdb->query( $wpdb->prepare( 
	"
		select * from wp_xf_users where LOWER(username) = LOWER(%s) or ip = %s order by claimed_at desc
	", 
        array(
		$username, 
		$ip
	) 
) );
  // wordpress version ends
     
  //timer check

  if ($row === null || $row['claimed_at'] <= $time - ($settings['timer'] * 60)) {
    $amount = intval($rewards['random_reward']);


    $response = pay($username,$amount,"Earnings from XXX, payed through Xapo!");

echo "<pre>" ;
     echo $username ;
	 echo $amount ;
	 print_r($response) ;
	echo "</pre>" ;
	

    try{
      $message=$response->message;

      if(!$response->success){
        $success = 0;
      }
      else{
        $success = 1;
      }
    
/*	  $q = $sql->prepare("INSERT into wp_xf_data (user, amount, date, result, message) values (?, ?, CURRENT_TIMESTAMP, ?, ?)");
      $q->execute(array($username,$amount,$success,$message));
         */
		 // Wordpress Version Starts
       $wpdb->query( $wpdb->prepare( 
	"
INSERT into wp_xf_data (user, amount, date, result, message) values (%s, %d, CURRENT_TIMESTAMP, %s, %s)	", 
       array($username,$amount,$success,$message)
) );
		 // Wordpress Version Ends
	}
    catch(Exception $e){
    goto b;
    }

    if($response->success)
	{
      $view['main']['result_html'] = '<div class="row text-center"><div class="col-sm-6 col-md-offset-3 bg-success"><p>Congratulations you have won '.$amount.' Satoshis !!!</p></div></div>';
      $url = get_main_url()."?r=".$username;
      $view['main']['ref_link'] = '<div class="row text-center"><div class="col-sm-6 col-md-offset-3 bg-success"><p>Share your referal link and earn a '.$settings["referral_percentage"].'% lifetime bonus. Your referal link is '.$url.'</p></div></div>';

    /*  $q = $sql->prepare("INSERT into wp_xf_users (username, ip, claimed_at) values (?,?,?) on duplicate key update ip = values(ip), claimed_at = values(claimed_at)");
      $q->execute(array($username, $ip, $time));*/

        // Wordpress Version Starts
       $wpdb->query( $wpdb->prepare( 
	"
INSERT into wp_xf_users (username, ip, claimed_at) values (%s,%s,%s) on duplicate key update ip = values(ip), claimed_at = values(claimed_at)", 
       array($username, $ip, $time)
) );
		 // Wordpress Version Ends


  /*    $q = $sql->prepare("Select * from wp_xf_referals where username=?");
      $q->execute(array($username));
      $row = $q->fetch();*/
       
	   
	    // Wordpress Version Starts
       $row = $wpdb->get_results( 
	"
Select * from wp_xf_referals where username='$username'" );   

		 // Wordpress Version Ends 
    

      $ref=null;

      if(!$row){
        //new user
        $ref = $_GET["r"];

/*        $q = $sql->prepare("INSERT into wp_xf_referals(username,reffered_by) Values(?,?)");
        $q->execute(array($username,$ref));
*/
         
		  // Wordpress Version Starts
       $wpdb->query( $wpdb->prepare( 
	"
INSERT into wp_xf_referals(username,reffered_by) Values(%s,%s)
", 
       array($username,$ref)
) );
		 // Wordpress Version Ends 
		 

      }
      else{
        $ref = $row[0]->reffered_by;
      }

      if(!is_null($ref)){
        $refAmount = $amount * ($settings["referral_percentage"]/100);
        $response = pay($ref,$refAmount,"Referral earnings from XXX, payed through Xapo!");
        if(!$response->success){
          $success = 0;
        }
        else{
          $success = 1;
        }
		
/*        $q = $sql->prepare("INSERT into wp_xf_data_referals (user, referrer, amount, date, result, message) values (?,?,?,now(),?,?)");
        $q->execute(array($ref, $username, $refAmount, $success, $response->message));
*/        
		  // Wordpress Version Starts
       $wpdb->query( $wpdb->prepare( 
	"
INSERT into wp_xf_data_referals (user, referrer, amount, date, result, message) values (%s,%s,%d,now(),%s,%s)", 
array($ref, $username, $refAmount, $success, $response->message)) );
		 // Wordpress Version Ends 
		
      }


    }
    else{
      $view['main']['result_html'] = '<div class="row text-center"><div class="col-sm-6 col-md-offset-3 bg-danger"><p>'.$response->message.'</p></div></div>';
      $message                     = $response->message;
     goto b;
    }

  }
  else{

    $waitingTime = ($row['claimed_at'] + ($settings['timer'] * 60)) - $time;
    if ($waitingTime > 0) {
      $waitingTime                  = format_timer($waitingTime);
    }
    $view['main']['result_html'] = '<div class="row text-center"><div class="col-sm-6 col-md-offset-3 bg-danger"><p>You can get a reward again in ' . htmlspecialchars($waitingTime) . '.</p></div></div>';
    $message                     = "Time!!";
   goto b;
  }
		
	}
   
    //Form on front end Starts
  b:
	    ?>
  <div><strong><p class="alert alert-info">Your possible rewards <?php echo $rewards["reward_list_html"]; ?></p></strong></div>
      <div>
        <strong><p>Earning bitcoins is simple:</p></strong>
      </div>
      <?php echo $view['main']['result_html']; ?>
      <?php echo $view['main']['ref_link']; ?>

         <form method="Post">
         <input type="hidden" name="xapoform" value="posted" />
        <div >
          <div><label>Insert your email or BTC address:</label>
            <input name="username" id="username" class="form-control text-center" type="text" placeholder="Enter your email or BTC address"></div>
          </div><br>
          <div>
            <div class="form-group"><label>Solve the captcha:</label>

              <center class="captcha"><script type="text/javascript" src="http://api.solvemedia.com/papi/challenge.script?k=<?php echo $settings['solvemedia_challenge_key']; ?>"></script></center></div>
              </div>
            <div>
              <div>
                <button class="btn btn-success" type="submit">Claim Prize</button>
              </div>
            </div>
          </form>
        <?php
	//Form on front end Ends
}

add_shortcode('XF_FORM','shortcode_handler');
/******** Shortcode function handling the form ENDS*******************/

?>