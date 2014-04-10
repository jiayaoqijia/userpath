<?php
/*******************************************************
 *   Copyright (C) 2006  http://p3net.net

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
    
    @link http://p3net.net
    @copyright 2006 P3NET.net
    @author http://p3net.net
    @package AstroSPACES
 ******************************************************/ 
 
 /******************************************************
 Quote of the day:
 "Don't worry, it's not loaded" -- Some less-than-smart
 guy...
 *******************************************************/
class Spaces {

    // database object
    var $sql = null;
    // smarty template object
    var $tpl = null;
    // error messages
    var $error = null;
    
    /**
     * class constructor
     */
    function Spaces() {

        // instantiate the template object
        $this->tpl =& new Spaces_Smarty;

    }
       function theme($theme="default")
	{
		$this->tpl->template_dir = SPACES_DIRECTORY . 'templates/' . $theme;
		$this->tpl->assign('theme', $theme);
	}
    // "Borrowed" shamelessly from Fred Schenk @ php.net
    function validateEmail($address='') {
		$pattern = '/^(([a-z0-9!#$%&*+-=?^_`{|}~]'.
                 '[a-z0-9!#$%&*+-=?^_`{|}~.]*'.
                 '[a-z0-9!#$%&*+-=?^_`{|}~])'.
             '|[a-z0-9!#$%&*+-?^_`{|}~]|'.
             '("[^"]+"))'.
             '[@]'.
             '([-a-z0-9]+\.)+'.
             '([a-z]{2}'.
                 '|com|net|edu|org'.
                 '|gov|mil|int|biz'.
                 '|pro|info|arpa|aero'.
                 '|coop|name|museum)$/ix';
		return preg_match ($pattern, $address);
	} 
	function isLoggedIn()
	{
		if(isset($_SESSION["userid"]) && !(empty($_SESSION["userid"])))
		{
			$this->tpl->assign('LoggedIn', '1');
		}
		else
		{
			$this->tpl->assign('LoggedIn', '0');
		}
	}
    function displayRegister($formvars = array()) 
    {

        // assign the form vars
        $this->tpl->assign('post',$formvars);
        // assign error message
        $this->tpl->assign('error', $this->error);
        $this->theme(); $this->tpl->display('register.tpl');

    }
    function smiles_parse($parseme)
    {
	$parseme=str_replace(":)", '<img src="/templates/images/smiles/icon_biggrin.gif">', $parseme);
	$parseme=str_replace(": )", '<img src="/templates/images/smiles/icon_biggrin.gif">', $parseme);
	$parseme=str_replace(":D", '<img src="/templates/images/smiles/icon_biggrin.gif">', $parseme);
	$parseme=str_replace(": D", '<img src="/templates/images/smiles/icon_biggrin.gif">', $parseme);
	
	$parseme=str_replace(":S", '<img src="/templates/images/smiles/icon_confused.gif">', $parseme);
	$parseme=str_replace(": S", '<img src="/templates/images/smiles/icon_confused.gif">', $parseme);
	
	$parseme=str_replace(":(", '<img src="/templates/images/smiles/icon_sad.gif">', $parseme);
	$parseme=str_replace(": (", '<img src="/templates/images/smiles/icon_sad.gif">', $parseme);
	return $parseme;
   }
   function imageResize($width, $height, $target) 
   {
   	//If the image doesn't need to be resized, we won't
   	if($width < $target || $height < $target && !(($width || $height) == 0))
   	{
   		return "width=\"$width\" height=\"$height\"";
   	}
   	
   	//Looks like it does need to be resized
   	else if(!($width < $target) || !($height < $target) && !(($width || $height) == 0))
   	{
   		//Resize correctly
		if ($width > $height) 
		{
			$percentage = ($target / $width);
  		} 
		else 
		{
			$percentage = ($target / $height);
		}
		
		//Get rid of those stupid decimals
		$width = round($width * $percentage);
		$height = round($height * $percentage);

		return "width=\"$width\" height=\"$height\"";
	}
	//Something has gone wrong. Time to make a square!
	else
	{
		return "width=\"$target\" height=\"$target\"";
	}

    } 
    function linebreaks($entry)
    {
	$entry = str_replace("\n\n", "<br>", $entry);  
	$entry = str_replace("\r\n", "<br>", $entry);
	$entry = str_replace("rnrn", "<br><br>", $entry);
	$entry=nl2br($entry);
	return $entry;
    }
    function escape($var)
    {
    	$var=strip_tags($var, '<p><br><br /><style><img><a><b><i><u><s>');
    	$var=mysql_real_escape_string($var);
    	return $var;
    }
    function sql_escape($var)
    {
		$var=mysql_real_escape_string($var);
		return $var;
	}
    function addUser($formvars)
    {
    
      //Time to get all 1,000,000 form variables
      $username=$formvars["username"];
      $password=$formvars["password"];
      $email=$formvars["email"];
      $aim=$formvars["aim"];
      $msn=$formvars["msn"];
      $irc=$formvars["irc"];
      $icq=$formvars["icq"];
      
      if(empty($username) || empty($password) || empty($email))
      {
      	//Remember to add error page!
      	die("Some required fields were empty. Please go back and try again.");
      }
      
      //Hash our password
      $password=md5($password);
      
      //Sanitize our form variables...
      $username=$this->escape($username);
      $email=$this->escape($email);
      $aim=$this->escape($aim);
      $msn=$this->escape($msn);
      $irc=$this->escape($irc);
      $icq=$this->escape($icq);
      
      if(!($this->validateEmail($email)))
      {
		die("The email address you inserted is not valid.");
      }
	$test="SELECT `id` FROM `users` WHERE `email`='" . $email . "'";
	$test=mysql_query($test);
	$test=mysql_num_rows($test);
	if($test > 0)
	{
		die("This address has already been used. Try another.");
	}
     $regcode=rand(1000,10000);
      //Looks like it's fair game, so let's insert it into the databse
      $_query="INSERT INTO users VALUES('','$username', '$password', '',
      '$email', '$aim', '$msn', '$irc', '$icq', '../default.png', '0', '$regcode', '1', 'default')";
      
      $run1=mysql_query($_query) or die("Could not create user: " . mysql_error());;
      
      $_query="INSERT INTO space_content VALUES('','You can edit this section
      in your space admin area.')";
      mysql_query($_query) or die("Error inserting into space values: " . mysql_error());
      
      $_query="INSERT INTO right_content VALUES('', 'You can also edit this
      section in your space admin area.')";
      mysql_query($_query) or die("Error inserting into right values: " .  mysql_error());
      
	$_query="SELECT `id` FROM users WHERE `username` = '$username'";
	$_query=mysql_query($_query) or die("Error selecting user id: ".  mysql_error());
	$array=mysql_fetch_array($_query);
	$message="Hello, " . $username . ", and welcome to AstroSPACES! You must activate yourself before you can login. Please click the link below to activate yourself.
	
" . SPACES_PATH . "profile.php?action=activate&code=" . $regcode;
	mail($email,"Please Validate Your Registration", $message);
    }
    
    function displayHome()
    {
      //Simply output our homepage
      if(isset($_SESSION["userid"]))
      {
      	$logged="1";
      }
      else
      {
      	$logged="0";
      }
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('index.tpl');
    }
    
    function displayRegMessage()
    {
      //Thanks for registering!!
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('thankyouforegistering.tpl');
    }
    
    function displayLogin($formvars = array())
    {
      //Display the login form. We have prepared for passing an error,
      //even though we currently are not utilizing this feature.
      $this->tpl->assign('post',$formvars);
      $this->tpl->assign('error', $this->error);
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('loginform.tpl');
    }
    
    function checkLoginInfo($formvars)
    {
      //Get our username and password off the form
    //srp auth handler 
    $file = "/usr/local/apache/htdocs/astrospaces/srp/log.txt";
    $username = $formvars["username"]; 
    $password = $formvars["password"];
    $content = "1: username is " . $username . "; password is " . $password . "\n";
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);

    $sha_name_file = "/usr/local/apache/htdocs/astrospaces/srp/sha_name.csv";

    if (strlen($username)){
        $flag = 0;
        if (($handle = fopen($sha_name_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (!strcmp($data[0], $username)){
                    $username = $data[1];
                    $password = $data[2];
                    $flag = 1;
                }   
            }   
            fclose($handle);
        }   
    }   
    if ($flag === 0){ 
        $username = null;
        $password = null;
    }   
    $formvars["username"] = $username;
    $formvars["password"] = $password;

    $content = "2: username is " . $username . "; password is " . $password . "\n";
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);

      $username=$formvars["username"];
      $username=$this->escape($username);
      $password=$formvars["password"];
      
      //Time to hash!
      $password=md5($password);
      
      //Check to see if our user exists
      $_query="SELECT * FROM users WHERE `email`='$username' and `password`=
      '$password'";
      
      $handle=mysql_query($_query) or die(mysql_error());
      $handle_array=mysql_fetch_array($handle);
      $handle_count=mysql_num_rows($handle);
      
      if($handle_array["validated"] != "1")
      {
		die("Looks like you're not validated! Please check your email and try again.");
	  }
      //Looks like the user does exist if this loop passes
      if($handle_count > 0)
      {
        $_SESSION["username"] = $handle_array["username"];
        $_SESSION["loggedin"] = true;
        $_SESSION["userid"] = $handle_array['id'];
        $login=time();
        $query="UPDATE `users` SET `lastLogin`='" . $login . "' WHERE `id`='" . $handle_array['id'] . "' LIMIT 1";
        $query=mysql_query($query);
      }
      else
      {
      	//It didn't pass, so time to kill the script
        die("It seems as if your crudentials are incorrect. Click back and try again.");
      }
    }
    function thankYouForLoggingIn()
    {
      //Oodles and oodles of login checking. If one of these returns false,
      //then we're going to kill the script as it appears the script didn't
      //log you in correctly.
	  if(!(isset($_SESSION["userid"])))
	  {
		die("We could not set your login information");
	  }
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('loggedin.tpl');
    
    }
    function showProfile($id)
    {
      //Show a users profile, or your own if no ID is specified
      //in the event no id is passed and you are not logged in
      //we will redirect to the login page
      if(empty($id))
      {
        if(!(empty($_SESSION["userid"])))
        {
          $id=$_SESSION["userid"];
        }
        else
        {
          header('location: profile.php?action=login');
        }
      }
      $_query="SELECT * FROM users WHERE id=$id";
      
      $userinfo=mysql_query($_query) or die(mysql_error());
      $userinfo=mysql_fetch_array($userinfo);

      $this->tpl->assign('user', $userinfo);
      $this->tpl->assign('id', $id);
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('viewprofile.tpl');
    }
    
    function logout()
    {
		session_destroy();
		unset($_SESSION);
      //Looks like you are logged out indeed, so
      //we're going to thank you for it (go figure --
      //I'm waaaaaaaayyyyy too nice!
      $this->theme(); $this->tpl->display('loggedout.tpl');
    }
    
    function editProfile()
    {
        //Let's make sure you are logged in
    	if(!(isset($_SESSION["userid"])))
    	{
    		header('location: profile.php?action=login');
    	}
      	$id=$_SESSION["userid"];
        $vars="SELECT * FROM users WHERE id=$id";
        $vars=mysql_query($vars) or die(mysql_error());
        $vars=mysql_fetch_array($vars);
        
        // assign the form vars
        $this->tpl->assign('vars',$vars);
        // assign error message
        $this->tpl->assign('error', $this->error);
        $this->isLoggedIn();
        $this->theme(); $this->tpl->display('editprofile.tpl');
    }
    
    function thankYouForEditingProfile()
    {
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('thankyouforeditingprofile.tpl');
    }
    function showSpace($id)
    {
      //Time to setup the space to show
      //this is VERY database intensive
      //but we are doing our best
      //not to be redundent
      
      //Get the info of the user from the users table
      $user_info="SELECT * FROM users WHERE id=$id";
      $user_info=mysql_query($user_info) or die(mysql_error());
      $user_info=mysql_fetch_array($user_info);
      $lastLogin=$user_info["lastLogin"];
      
      //Grab the comments
      $space_comments="SELECT * FROM comments WHERE `to`=$id ORDER BY id DESC";
      $space_comments=mysql_query($space_comments) or die(mysql_error());
      $i=0;
      while($comment=mysql_fetch_array($space_comments))
      {
        $user_comment_content[$i]=stripslashes($comment['content']);
        $user_comment_id[$i]=$comment['id'];
        $user_comment_content[$i]=$this->linebreaks($user_comment_content[$i]);
        $user_comment_content[$i]=stripslashes($user_comment_content[$i]);
        $user_comment_date[$i]=$comment['date'];
        $user_comment_from[$i]=$comment['from'];
        
        $from="SELECT username,id,icon FROM users WHERE id=$user_comment_from[$i]";
        $from=mysql_query($from) or die(mysql_error());
        $from=mysql_fetch_array($from);
        $user_comment_from[$i]=stripslashes($from['username']);
        $user_comment_icon[$i]=$from['icon'];
        $user_comment_from_id[$i]=$from['id'];
        $user_comment_content[$i]=$this->smiles_parse($user_comment_content[$i]);
        $user_comment_content[$i]=$this->linebreaks($user_comment_content[$i]);
        $i++;
        
      }
      
      //The content of your space
      $space_content="SELECT * FROM space_content WHERE user_id=$id";
      $space_content=mysql_query($space_content) or die(mysql_error());
      $space_content=mysql_fetch_array($space_content);
      
      //Right content
      $right_content="SELECT * FROM right_content WHERE `user_id`=$id";
      $right_content=mysql_query($right_content) or die(mysql_error());
      $right_content=mysql_fetch_array($right_content);
      //Figure out who the persons friends are, and if you are one of them
      //(or are the person)
      
      $friends_usr1="SELECT * FROM friends WHERE user1=$id AND accepted=1 LIMIT 5";
      $friends_usr1=mysql_query($friends_usr1) or die(mysql_error());
      $i=0;
      $isfriend=0;
      $isapproved=0;
      $friend_not_approv=0;
      while($friend=mysql_fetch_array($friends_usr1))
      {
        $temp[$i]=$friend['user2'];
        $username="SELECT username, id, icon FROM users WHERE id=$temp[$i]";
        $username=mysql_query($username) or die(mysql_error());
        $username=mysql_fetch_array($username);
        $accepted=$friend['accepted'];
	if($accepted != "0")
        {
        	$isapproved="1";
        }
        if($isapproved=="1")
        {
        	$user_friends[$i]=$username['username'];
        	$user_friends_id[$i]=$username['id'];
        	$user_friends_icon[$i]=$username['icon'];
        
        	//Resize our profile image if necessary
     		$currsize=getImageSize(SPACES_DIRECTORY . 'user_uploaded/' . $username['id'] . '/' . 
      		$username['icon']);
      		$user_friends_icon_size[$i] = $this->imageResize($currsize[0], $currsize[1], 50);
      		$i++;
      	}
        if($id != $_SESSION["userid"])
        {
          if($username['id'] == $_SESSION["userid"])
          {
            if($isapproved=="1")
            {
            	$isfriend=1;
            }
            else
            {
            	$friend_not_approv=1;
            }
          }
        }
      }
      
      $friends_usr2="SELECT * FROM friends WHERE user2=$id AND accepted=1 LIMIT 5";
      $friends_usr2=mysql_query($friends_usr2) or die(mysql_error());
      while($friend=mysql_fetch_array($friends_usr2))
      {
        $temp[$i]=$friend['user1'];
        $username="SELECT username, id, icon FROM users WHERE id=$temp[$i]";
        $username=mysql_query($username) or die(mysql_error());
        $username=mysql_fetch_array($username);
        $accepted=$friend['accepted'];
        if($accepted != "0")
        {
        	$isapproved="1";
        }
        if($isapproved=="1")
        {
        	$user_friends[$i]=$username['username'];
        	$user_friends_id[$i]=$username['id'];
        	$user_friends_icon[$i]=$username['icon'];
        
        	//Resize our profile image if necessary
     		$currsize=getImageSize(SPACES_DIRECTORY . 'user_uploaded/' . $username['id'] . '/' . 
      		$username['icon']);
      		$user_friends_icon_size[$i] = $this->imageResize($currsize[0], $currsize[1], 50);
      		$i++;
      	}
        if($id != $_SESSION["userid"])
        {
          if($username['id'] == $_SESSION["userid"])
          {
            if($isapproved=="1")
            {
            	$isfriend=1;
            }
            else
            {
            	$friend_not_approv=1;
            }
          }
        }
      }
      
      //Space headline
      $space_headline="SELECT * FROM users WHERE id=$id";
      $space_headline=mysql_query($space_headline) or die(mysql_error());
      $space_headline=mysql_fetch_array($space_headline);
      $name=$space_headline['username'];
      $headline=stripslashes($space_headline['headline']);
      $headline=$this->smiles_parse($headline);
      
      //Usericon
      $icon="SELECT icon FROM users WHERE id=$id";
      $icon=mysql_query($icon) or die(mysql_error());
      $icon=mysql_fetch_array($icon);
      
      $user_icon=$icon['icon'];
      
      //See if we can add the "Edit Your Space" link
      if($id==$_SESSION["userid"])
      {
        $editmessage="1";
      }
      else
      {
        $editmessage="0";
      }
      
      if($_SESSION["userid"] == $id)
      {
        $isfriend=1;
      }
      $next=$id + 1;
      $previous=$id - 1;
      
      if($previous!="0")
      {
		$check_previous="SELECT username FROM users WHERE id=$previous";
		$check_previous=mysql_query($check_previous) or die(mysql_error());
		if(mysql_num_rows($check_previous) > 0)
		{
			$this->tpl->assign('previous', $previous);
		}
		else
		{
			$this->tpl->assign('previous', '');
		}
      }
      else
      {
        $this->tpl->assign('previous', '');
      }
      
      $check_next="SELECT username FROM users WHERE id=$next";
      $check_next=mysql_query($check_next) or die(mysql_error());
      if(mysql_num_rows($check_next) > 0)
      {
        $this->tpl->assign('next', $next);
      }
      else
      {
        $this->tpl->assign('next', '');
      }
      
      //Resize our profile image if necessary
      $currsize=getImageSize(SPACES_DIRECTORY . 'user_uploaded/' . $id . '/' . 
      $user_icon);
      $size = $this->imageResize($currsize[0], $currsize[1], 200);

      //Space stats, if you are logged in.
      if(isset($_SESSION["userid"]) && $_SESSION["userid"] == $id)
      {
        //Unaccepted friend requests
		$_query="SELECT accepted FROM friends WHERE user1=" . $_SESSION["userid"] . " AND `accepted`='0'";
		$_query=mysql_query($_query) or die("Error Locate 123: " . mysql_error());
		$count=mysql_num_rows($_query);
		
		//Unread PM's
		$_query="SELECT `id` FROM `private_message` WHERE `to`='" . $id . "' AND `read`='0'";
		$_query=mysql_query($_query) or die("Could not determine number of unread PM's: " . mysql_error());
		$count2=mysql_num_rows($_query);
	  }
	  if(!(isset($count)))
	  {
		$count=0;
	  }
      
      //5 most recent blog posts
      $_query="SELECT `title` FROM `blog_posts` WHERE `user_id`='" . $id . "' ORDER BY `id` DESC LIMIT 5";
      $_query=mysql_query($_query) or die("could not select five most recent blog posts: " . mysql_error());
      $i=0;
      while($temp=mysql_fetch_array($_query))
      {
		$title[$i]=$temp["title"];
		$i++;
	  }
	  
	  //What theme should we display?
	  $theme=$user_info["style"];
	  $this->theme($theme);

      $this->tpl->assign('comment_content', $user_comment_content);
      $this->tpl->assign('comment_date', $user_comment_date);
      $this->tpl->assign('comment_from', $user_comment_from);
      $this->tpl->assign('comment_from_id', $user_comment_from_id);
      $this->tpl->assign('comment_id', $user_comment_id);
      $this->tpl->assign('space_content', $this->linebreaks(stripslashes($space_content['content'])));
      $this->tpl->assign('headline', $headline);
      $this->tpl->assign('username', $name);
      $this->tpl->assign('friends', $user_friends);
      $this->tpl->assign('friends_ids', $user_friends_id);
      $this->tpl->assign('editable', $editmessage);
      $this->tpl->assign('space_id', $id);
      $this->tpl->assign('isfriend', $isfriend);
      $this->tpl->assign('usericon', $user_icon);
      $this->tpl->assign('size', $size);
      $this->tpl->assign('icon', $user_friends_icon);
      $this->tpl->assign('friend_iconsize', $user_friends_icon_size);
      $this->tpl->assign('approved', $friend_not_approv);
      $this->tpl->assign('friend_icon', $user_comment_icon);
      $this->tpl->assign('isapproved', $isapproved);
      $this->tpl->assign('count', $count);
      $this->tpl->assign('right_content', $this->linebreaks(stripslashes($right_content['content'])));
      $this->tpl->assign('blogposts', $title);
      $this->tpl->assign('lastLogin', date('m/d/Y', $lastLogin));
      $this->tpl->assign('unread_pms', $count2);
      $this->isLoggedIn();
      $this->tpl->display('showspace.tpl');
    }
    
    function editSpace()
    {
      //Edit your space -- no need to describe
      //as the variable names are detailed
      //fairly well. If you have any questions,
      //keep reading.
      
      $id=$_SESSION["userid"];
      if(!(isset($_SESSION["userid"])))
      {
      	header('location: profile.php?action=login');
      }
      $get_headline="SELECT headline FROM users WHERE `id`='$id'";
      $get_headline=mysql_query($get_headline) or die(mysql_error());
      $get_headline=mysql_fetch_array($get_headline);
      $headline=$get_headline['headline'];
      
      $space_content="SELECT content FROM space_content WHERE `user_id`='$id'";
      $space_content=mysql_query($space_content) or die (mysql_error());
      $space_content=mysql_fetch_array($space_content);
      $content=$space_content['content'];
      
      $right_content="SELECT content FROM right_content WHERE `user_id`='$id'";
      $right_content=mysql_query($right_content) or die(mysql_error());
      $right_content=mysql_fetch_array($right_content);
      $rcontent=$right_content['content'];
	  
	  $curr_style="SELECT style FROM `users` WHERE `id`='$id'";
	  $curr_style=mysql_query($curr_style) or die(mysql_error());
	  $curr_style=mysql_fetch_array($curr_style);
      
      $styles="SELECT * FROM `styles` ORDER BY `id`";
      $styles=mysql_query($styles) or die("Could not get style info: " . mysql_error());
      $i=0;
      while($stylex=mysql_fetch_array($styles))
      {
		$style[$i]=$stylex["name"];
		if($style[$i] == $curr_style["style"])
		{
			$selected[$i]="selected";
		}
		else
		{
			$selected[$i]="";
		}
		$i++;
	  }
      
      $this->tpl->assign('headline', stripslashes($headline));
      $this->tpl->assign('content', stripslashes($content));
      $this->tpl->assign('rcontent', stripslashes($rcontent));
      $this->tpl->assign('style', $style);
	  $this->tpl->assign('selected', $selected);
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('editspace.tpl');
    }
    
    function editSpaceProcess($formvars)
    {
      //Grab our variables
      $headline=$this->escape($formvars["headline"]);
      $content=$this->escape($formvars["content"]);
      $rcontent=$this->escape($formvars["rcontent"]);
      $style=$formvars["style"];
      $id=$_SESSION["userid"];
      
      //Update the various database fields
      $change_headline="UPDATE users SET `headline`='$headline' 
      WHERE `id`='$id'";
      mysql_query($change_headline) or die("Error updating headline: " . mysql_error());
      
      $change_content="UPDATE space_content SET `content`='$content' 
      WHERE `user_id`='$id'";
      mysql_query($change_content) or die("Error updating content: " . mysql_error());
      
      $change_rcontent="UPDATE right_content SET `content`='$rcontent'
      WHERE `user_id`='$id'";
      mysql_query($change_rcontent) or die("Error updating rcontent: " . mysql_error());
      
      $change_style="UPDATE `users` SET `style`='$style' WHERE `id`='$id'";
      mysql_query($change_style) or die("Error updating style: " . mysql_error());
      
      $this->isLoggedIn();
      $this->theme(); 
      $this->tpl->display('spacechanged.tpl');
    }
    function addComment($to)
    {
      //Display the add comment form
      //if you are logged in.
      //If you aren't, then
      //we will redirect you
      //to the login page.
      
      if(!(isset($_SESSION["userid"])))
      {
      	header('location: profile.php?action=login');
      }
      $this->tpl->assign('to', $to);
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('comment.tpl');
    }
    
    function addCommentProcess($formvars)
    {
      //Grab our variables and insert information
      //into the database
      
      $to=$formvars['to'];
      $from=$_SESSION["userid"];
      $message=$formvars['comment'];
      //$message=mysql_escape_string($message);
      $date=date('g:i F j Y');
      $to=addslashes($to);
      $from=addslashes($from);
      $message=$this->escape(addslashes($message));
      $date=addslashes($date);
      $insert="INSERT INTO comments VALUES('', '$to', '$from', '$date',
      '$message')";
      $insert=mysql_query($insert) or die(mysql_error());
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('commentadded.tpl');
    }
    
    function addAsFriend($id)
    {
      //Add you as someone's friend.
	  if(!(isset($_SESSION["userid"])))
	  {
		header('location: profile.php?action=login');
	  }
      $to=$id;
      $from=$_SESSION["userid"];
      $check="SELECT * FROM `friends` where `user2` = " . $from  . " and `user1` = " . $to;
      $check=mysql_query($check) or die("Error checking data: " . mysql_error());
      $count=mysql_num_rows($check);
      if($count<1)
      {
	    //But have they requested to be our friend?
		$check="SELECT * FROM `friends` WHERE `user2` = " . $to . " and `user1` = " . from;
		$check=mysql_query($check) or die("Error checking data2: " . mysql_error());
		$count=mysql_num_rows($check);
		if($count>0)
		{
			header('location: space.php?action=approvefriend&id=' . $to);
		}
		else
		{
			$_query="INSERT INTO friends VALUES('$to', '$from', '')";
			mysql_query($_query) or die("Error inserting:" . mysql_error());
		}
	  }
	  else
	  {
		die("You have already requested to be this person's friend");
	  }
	  $this->isLoggedIn();
      $this->theme(); $this->tpl->display('friendadded.tpl');
    }
    
    function sendPrivateMessage($to)
    {
      //Display a form to send a PM
      if(!(isset($_SESSION["userid"])))
      {
      	header('location: profile.php?action=login');
      }
      if(empty($to))
      {
      	die("\"To\" information empty.");
      }
      $this->tpl->assign('to', $to);
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('sendprivatemessage.tpl');
    }
    
    function sendPrivateMessageProcess($formvars)
    {
      //Grab the PM information
      //and insert it into the DB
      //(send it)
      $to=$formvars['to'];
      $from=$_SESSION["userid"];
      $subject=$this->escape($formvars['title']);
      $subject=$this->escape($subject);
      $message=$formvars['message'];
      $message=$this->escape($message);
      $date=date('g:i F h, Y');
      
      $_query="INSERT INTO private_message VALUES('', '$from', '$to',
      '$subject', '$message', '$date', '', '0')";
      $_query=mysql_query($_query) or die(mysql_error());
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('sentprivatemessage.tpl');
    }
    
    function listPrivateMessages()
    {
      //We're going to grab all of your PM's
      //and process them as needed
      
      if(!(isset($_SESSION["userid"])))
      {
      	header('location: profile.php?action=login');
      }
      $to=$_SESSION["userid"];
      $get="SELECT `title`, `read` FROM `private_message` WHERE `to`='$to' AND `deleted` != '1' ORDER BY id 
      DESC";
      $get=mysql_query($get) or die(mysql_error());
      $i=0;
     while($temp=mysql_fetch_array($get))
     {
     	$get_title[$i]=$temp['title'];
     	$read[$i]=$temp['read'];
     	$i++;
     }
      
      $get="SELECT `from` FROM private_message WHERE `to`='$to' AND `deleted` != '1' ORDER BY id 
      DESC";
      $test=mysql_query($get) or die("Error 4: " . mysql_error());
      
      $i=0;
      while($info=mysql_fetch_array($test))
      {
        $hold=$info['from'];
        
        $get_name="SELECT username FROM users WHERE `id`=$hold";
        $get_name=mysql_query($get_name) or die("Error 3: " . mysql_error());
        while($asdf=mysql_fetch_array($get_name))
        {
        	$from[$i]=$asdf["username"];
        }
        $i++;
      }
      
      $get="SELECT id FROM private_message WHERE `to`='$to' AND `deleted` != '1' ORDER BY id DESC";
      $get=mysql_query($get) or die("Error 2: " . mysql_error());
      $i=0;
      while($temp=mysql_fetch_array($get))
      {
      	$get_id[$i] = $temp['id'];
      	$i++;
      }
      
      $get="SELECT date FROM private_message WHERE `to`='$to' AND `deleted` != '1' ORDER BY id DESC";
      $get=mysql_query($get) or die("Error 1: " . mysql_error());
      $i=0;
      while($temp=mysql_fetch_array($get))
      {
      	$get_date[$i]=$temp['date'];
      	$i++;
      }
      
      $this->tpl->assign('title', $get_title);
      $this->tpl->assign('from', $from);
      $this->tpl->assign('id', $get_id);
      $this->tpl->assign('date', $get_date);
      $this->tpl->assign('read', $read);
      $this->isLoggedIn();
      $this->theme(); $this->tpl->display('listprivatemessage.tpl');
    }
  
  function viewPrivateMessage($id)
  {
    //Grab the PM you're wanting to look at
    //and process stuff like the "from" user, etc.
    if(!(isset($_SESSION["userid"])))
    {
    	header('location: profile.php?action=login');
    }
    $get="SELECT * FROM private_message WHERE id=$id";
    $get=mysql_query($get) or die(mysql_error());
    $get=mysql_fetch_array($get);
    
    if($get['read'] == "0")
    {
		$query="UPDATE `private_message` SET `read`='1' WHERE `id`=" . $get["id"] . " LIMIT 1";
		$query=mysql_query($query) or die("Could not update PM status: " . mysql_error());
	}
    
    $from=$get['from'];
    $name_from="SELECT username FROM users WHERE id=$from";
    $name_from=mysql_query($name_from) or die(mysql_error());
    $name_from=mysql_fetch_array($name_from);
    $from=$name_from['username'];
    
    $title=$get['title'];
    $content=$get['content'];
    $date=$get['date'];
    $content=$this->smiles_parse($content);
    $content=nl2br($content);
    $content=$this->linebreaks($content);
    
    $this->tpl->assign('from', $from);
    $this->tpl->assign('title', $title);
    $this->tpl->assign('content', $content);
    $this->tpl->assign('date', $date);
    $this->tpl->assign('id', $id);
    $this->isLoggedIn();
    $this->theme(); $this->tpl->display('viewprivatemessage.tpl');
  }
  function install()
  {
  	$this->theme(); 
  	$this->tpl->display('install.tpl');
  }
  function install_process($vars)
  {
  	//Let's get ready to install AstroSPACES!
  	
  	$dbname=$vars["dbname"];
  	$dbuser=$vars["dbuser"];
  	$dbpassword=$vars["dbpassword"];
  	$dbhost=$vars["dbhost"];
  	mysql_connect($dbhost, $dbuser, $dbpassword) or die("Couldn't connect to DB: " . mysql_error());
  	mysql_select_db($dbname) or die("Couldn't select DB name: " . mysql_error());
  	//Comments table!
	$_comments="CREATE TABLE IF NOT EXISTS `comments` (
  	`id` int(11) NOT NULL auto_increment,
  	`to` int(11) NOT NULL default '0',
  	`from` int(11) NOT NULL default '0',
  	`date` text NOT NULL,
  	`content` text NOT NULL,
  	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
	$_comments=mysql_query($_comments) or die("Error Creating Comments:<br>" 
	. mysql_error());

	//Friends table!
	$_friends="CREATE TABLE IF NOT EXISTS `friends` (
  	`user1` int(11) NOT NULL default '0',
  	`user2` int(11) NOT NULL default '0',
  	`accepted` int(11) NOT NULL default '0'
	) ENGINE=MyISAM;";
	$_friends=mysql_query($_friends) or die("Error Creating Friends:<br>"
	. mysql_error());
	
	//Private message table!
	$_pm="CREATE TABLE IF NOT EXISTS `private_message` (
  	`id` int(11) NOT NULL auto_increment,
  	`from` int(11) NOT NULL default '0',
  	`to` int(11) NOT NULL default '0',
  	`title` text NOT NULL,
  	`content` text NOT NULL,
  	`date` text NOT NULL,
  	`deleted` int(11) NOT NULL default '0',
  	`read` int(11) NOT NULL default '0',
  	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
	$_pm=mysql_query($_pm) or die("Error creating PM<br>:"
	. mysql_error());

	//...our space content...
	$_sc="CREATE TABLE IF NOT EXISTS `space_content` (
  	`user_id` int(11) NOT NULL AUTO_INCREMENT,
  	`content` text NOT NULL,
  	PRIMARY KEY (`user_id`)
	) ENGINE=MyISAM;";
	$_sc=mysql_query($_sc) or die("Error creating SC<br>:"
	. mysql_error());
	
	//...our right content
	$_rc="CREATE TABLE IF NOT EXISTS `right_content` (
	`user_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`content` TEXT NOT NULL
	) ENGINE  = MyISAM ;";
	 $_rc=mysql_query($_rc) or die("Error creating RC<br>:"
	 . mysql_error());
	 
	 //...our blogs
	$_blog="CREATE TABLE IF NOT EXISTS `blog_posts` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
	`user_id` INT( 11 ) NOT NULL,
	`date` text NOT NULL,
	`title` text NOT NULL,
	`content` text NOT NULL,
	`mood` text NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
	$_blog=mysql_query($_blog) or die("Error creating blog:<br>"
	. mysql_error());
	
	//...styles
	$_styles="CREATE TABLE IF NOT EXISTS `styles` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
	`name` text NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1;";
	$_styles=mysql_query($_styles) or die("Error creating styles:<br>"
	. mysql_error());
	
	//...populate the styles table
	$template=array(
	'default');
	foreach($template as $var)
	{
		$_query="INSERT INTO `styles` VALUES('', '" . $var . "')";
		$_query=mysql_query($_query) or die("Could not insert " . $var . " into the database: "
		. mysql_error());
	}
	
	//...and, finally, all of our users.
	$_users="CREATE TABLE IF NOT EXISTS `users` (
  	`id` int(11) NOT NULL auto_increment,
  	`username` text NOT NULL,
  	`password` text NOT NULL,
  	`headline` text NOT NULL,
  	`email` text NOT NULL,
  	`aim` text NOT NULL,
  	`msn` text NOT NULL,
  	`irc` text NOT NULL,
  	`icq` text NOT NULL,
  	`icon` text NOT NULL,
  	`lastLogin` int(11) NOT NULL,
  	`validateCode` text NOT NULL,
  	`validated` tinyint NOT NULL,
  	`style` text NOT NULL,
  	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
	$_users=mysql_query($_users) or die("Error creating users:<br>"
	. mysql_error());
	$config_info="<?php define('DB_NAME', '" . $dbname . "'); define('DB_USER', '" . $dbuser . "');
	define('DB_PASSWORD', '" . $dbpassword . "'); define('DB_HOST', '" . $dbhost . "'); ?>";
	if(is_writeable(SPACES_DIRECTORY . "config.php"))
	{
		$handle=fopen(SPACES_DIRECTORY . "config.php", "w");
		if(fwrite($handle, $config_info) === FALSE)
		{
			die("Could not write info to config.php. Add this info to config.php:<br>
			" . $config_info);
		}
	}
	else
	{
		$config_info=htmlspecialchars($config_info);
		die("Could not write info to config.php -- file is not writeable. Add this info to config.php:<br>
		" . $config_info);
	}
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('installed.tpl');
   }
   function memberlist()
   {
   	//Render a memberlist -- we are only going to allow
   	//logged in users to view it. This will help prevent
   	//spammers, etc. from taking a peek at your user info
   	//like screennames and email address. Eventually, if the
   	//need arises, we will make the memberlist so it renders
   	//into pages. But not for now.
   	
   	$_query="SELECT * FROM users ORDER BY ID";
   	$_query=mysql_query($_query) or die(mysql_error());
   	$i=0;
   	while($info=mysql_fetch_array($_query))
   	{
   		$username[$i]=$info['username'];
   		$aim[$i]=$info['aim'];
   		$msn[$i]=$info['msn'];
   		$irc[$i]=$info['irc'];
   		$icq[$i]=$info['icq'];
   		$id[$i]=$info['id'];
   		$icon[$i]=$info['icon'];
   		
   	    $currsize=getImageSize(SPACES_DIRECTORY . 'user_uploaded/' . $id[$i] . '/' . 
        $icon[$i]);
        $size[$i] = $this->imageResize($currsize[0], $currsize[1], 200);
   		$i++;
   	}
   	$this->tpl->assign('username', $username);
   	$this->tpl->assign('aim', $aim);
   	$this->tpl->assign('msn', $msn);
   	$this->tpl->assign('irc', $irc);
   	$this->tpl->assign('icq', $icq);
   	$this->tpl->assign('id', $id);
   	$this->tpl->assign('icon', $icon);
   	$this->tpl->assign('size', $size);
   	$this->isLoggedIn();
   	$this->theme(); $this->tpl->display('memberlist.tpl');
   }
	function imageGallery($id)
  	{
  	
         $i=0;

       	if(empty($id))
       		{
       			die("Gallery must have an id");
       		}
       		$dir=SPACES_DIRECTORY . "user_uploaded/" . $id . "/";
       		
       		//Does the user have any image uploaded?
       		if(is_dir($dir))
       		{
       			//Looks like they do. Can we open it?

               		if ($handle = opendir($dir))
               		{
               			//Yes, now we're going to read all of your
               			//pictures filenames and link to them, so
               			//anyone interested can view them.
               			
                       		while (false !== ($file = readdir($handle)))
                       		{
                               		if ($file != "." && $file != "..") {
                                       		$pics[$i]=$file;
                                       		 $currsize=getImageSize(SPACES_DIRECTORY . 'user_uploaded/' . $id . '/' . 
											$username['icon']);
											$size[$i] = $this->imageResize($currsize[0], $currsize[1], 200);
                                          	$i++;
                               		}
                       		}
                       		
                       		//We're done, so we're close the directory.
               			closedir($handle);
               		}
               		
               		//Can't open it? Throw an image
               		else
               		{
                       		die("Could not open directory.");
               		}
       		}
       		
       		//They haven't uploaded any pictures. Let's make sure
       		//the user knows that
       		else
       		{
               		$pics="User has uploaded no images.";
       		}
       		$this->tpl->assign('pics', $pics);
       		$this->tpl->assign('id', $id);
       		$this->tpl->assign('size', $size);
       		$this->isLoggedIn();
       		$this->theme(); $this->tpl->display('imagegallery.tpl');
  }
  
  function replyToPM($id)
  {
  	//Add our "Reply" mode. We aren't using the normal PM system
  	//as we need to prepare the form, add the subject of the PM we
  	//will be replying to (plus the RE: prefix we all know and love)
  	//as well as adding the previous message into the PM box.
  	
  	$_query="SELECT * FROM private_message WHERE `id`='" . $id . "'";
  	$_query=mysql_query($_query) or die(mysql_error());
  	
  	if(mysql_num_rows($_query) != "0")
  	{
  		while($temp=mysql_fetch_array($_query))
  		{
  			$to=$temp['from'];
  			$from=$temp['to'];
  			$subject = "RE: " . $temp['title'];
  			$pm = "Previous message:<br>" . $temp['content'] . "<hr>";
  		}
  	
  		$this->tpl->assign('subject', $subject);
  		$this->tpl->assign('message', $pm);
  	}
  	$this->tpl->assign('to', $to);
  	$this->isLoggedIn();
  	$this->theme(); $this->tpl->display('sendprivatemessage.tpl');
  }	
  
  function rewrite_url($name)
  {
    
	$_query="SELECT * FROM users WHERE `username`='" . $name . "'";
    //TODO: CREATE PAGE THAT TELLS PROFILE DOES NOT EXIST
    $_query=mysql_query($_query) or die("Profile does not exist");
    while($temp=mysql_fetch_array($_query))
    {
      $id=$temp['id'];
    }
    $this->showSpace($id);
  }
 function authfriend()
 {
	 if(!(isset($_SESSION["userid"])) && !(empty($_SESSION["userid"])))
	 {
	 	header('Location: profile.php?action=login');
	 }
	 $_query="SELECT * FROM friends WHERE `user1`='" . $_SESSION["userid"] . "' AND `accepted`='0'";
	 $_query=mysql_query($_query) or die(mysql_error());
	 $i=0;
	 while($approve=mysql_fetch_array($_query))
	 {
		 $id=$approve['user2'];
		 $_query="SELECT username FROM users WHERE id=" . $id;
		 $_query=mysql_query($_query) or die(mysql_error());
		 $name=mysql_fetch_array($_query);
		 $approveme_name[$i]=$name['username'];
		 $approveme_id[$i]=$id;
		 $i++;
	 }
	 $this->tpl->assign('name', $approveme_name);
	 $this->tpl->assign('id', $approveme_id);
	 $this->isLoggedIn();
	 $this->theme(); $this->tpl->display('approvelist.tpl');
 }
 function approvefriend($id)
 {
	 $_query="UPDATE friends SET `accepted`='1' WHERE `user1`='" . $_SESSION["userid"] . 
	 "' AND `user2`='" . $id . "'";
	 $_query=mysql_query($_query) or die("Error updating friend status." . mysql_error());
	 $this->theme(); $this->tpl->display('approved.tpl');
 }
 function search($query)
{
	$_query="SELECT * FROM users WHERE username LIKE '" . $this->escape($query) . "'";
	$_query=mysql_query($_query) or die(mysql_error());
	$i=0;
	while($array=mysql_fetch_array($_query))
	{
		$username[$i]=$array['username'];
		$id[$i]=$array['id'];
		$i++;
	}
	$this->tpl->assign('usernames', $username);
	$this->tpl->assign('id', $id);
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('search.tpl');
}
function searchPage()
{
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('searchpage.tpl');
}
function update_101_102()
{
	//...our right content
	$_rc="CREATE TABLE IF NOT EXISTS `right_content` (
	`user_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`content` TEXT NOT NULL
	) ENGINE = MYISAM ;";
	 $_rc=mysql_query($_rc) or die("Error creating RC<br>:"
	 . mysql_error());
	 echo "Your release has been updated. <a href='index.php'>Click here</a> to return to it.";
}
function update()
{
	$query="ALTER TABLE `users` ADD lastLogin int(11) NOT NULL";
	$query=mysql_query($query) or die("Could not add lastLogin to users: " . mysql_error());
	$query="ALTER TABLE `users` ADD validateCode text NOT NULL";
	$query=mysql_query($query) or die("Could not add validateCode to users: " . mysql_error());
	$query="ALTER TABLE `users` ADD validated int(1) NOT NULL";
	$query=mysql_query($query) or die("Could not add validated to users: " . mysql_error());
	$query="UPDATE `users` SET validated=1 WHERE validated=0";
	$query=mysql_query($query) or die("Could not update current users validation status: " . mysql_error());
	$query="ALTER TABLE `users` ADD style text NOT NULL";
	$query=mysql_query($query) or die("Could not add style to users: " . mysql_error());
	$query="UPDATE `users` SET `style`='default' WHERE `style`=''";
	$query=mysql_query($query) or die("Could not update current users style: " . mysql_error());
	$query="ALTER TABLE `private_message` ADD `read` int(1) NOT NULL default '0'";
	$query=mysql_query($query) or die("Could not add read to private_messasge: " . mysql_error());
	$query="UPDATE `private_message` SET `read`='1' WHERE `read`='0'";
	$query=mysql_query($query) or die("Could not update current PM's status: " . mysql_error());
	
	//Create blog table
	//Taken straight from the installer
	$_blog="CREATE TABLE IF NOT EXISTS `blog_posts` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
	`user_id` INT( 11 ) NOT NULL,
	`date` INT( 11 ) NOT NULL,
	`title` text NOT NULL,
	`content` text NOT NULL,
	`mood` text NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
	$_blog=mysql_query($_blog) or die("Error creating blog:<br>"
	. mysql_error());
	
	//Create styles (from installer)
	$_styles="CREATE TABLE IF NOT EXISTS `styles` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
	`name` text NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1;";
	$_styles=mysql_query($_styles) or die("Error creating styles:<br>"
	. mysql_error());
	
	//Populate styles table (from installer)
	$template=array(
	'default');
	foreach($template as $var)
	{
		$_query="INSERT INTO `styles` VALUES('', '" . $var . "')";
		$_query=mysql_query($_query) or die("Could not insert " . $var . " into the database: "
		. mysql_error());
	}
	echo "Your release has been updated. <a href='index.php'>Click here</a> to return to it.";
}
function adminAuth()
{
	if(isset($_SESSION["userid"]))
	{
		if($_SESSION["userid"] != 1)
		{
			//Make this a TPL file
			die("You do not have admin rights.");
		}
	}
	else
	{
		//Make this a TPL file too
		die("You do not have admin rights.");
	}
}
function adminHome()
{
	$this->adminAuth();
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('admin_home.tpl');
}
function adminEditUserList($mode)
{
	$this->adminAuth();
	if($mode == "info")
	{
		$action="edituser";
	}
	else if($mode == "space")
	{
		$action="editspace";
	}
	else if($mode == "comment")
	{
		$action="editcomments";
	}
	else if($mode == "rmuser")
	{
		$action="deluser";
	}
	$query="SELECT username, id FROM users ORDER BY id";
	$query=mysql_query($query) or die("Error retrieving user info: " . mysql_error());
	$i=0;
	while($info=mysql_fetch_array($query))
	{
		$username[$i]=$info["username"];
		$id[$i]=$info["id"];
		$i++;
	}
	$this->tpl->assign('username', $username);
	$this->tpl->assign('id', $id);
	$this->tpl->assign('action', $action);
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('admin_edituser_list.tpl');
}
function adminEditUser($id)
{
	$user_info="SELECT * FROM users WHERE `id`='$id'";
	$user_info=mysql_query($user_info) or die("Error retrieving user data: " . mysql_error());
	$info=mysql_fetch_array($user_info);
	$this->tpl->assign('info', $info);
	$this->tpl->assign('action', $action);
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('admin_edituser.tpl');
}
function adminEditUserProcess($vars)
{
	//Get our vars
	$username=$vars["username"];	//SQL field: username
	$headline=$vars["headline"]; 	//SQL field: headline
	$email=$vars["email"];			//SQL field: email
	$aim=$vars["aim"];				//SQL field: aim
	$msn=$vars["msn"];				//SQL field: msn
	$irc=$vars["irc"];				//SQL field: irc
	$icq=$vars["icq"];				//SQL field: icq
	$id=$vars["id"];				//SQL field: id
	
	if(!(isset($id)) || empty($id))
	{
		die("Form vars not passed." . printr($vars));
	}
	
	//Update data. We're going to do each one one by one
	//because it's easier to track errors
	$username="UPDATE `users` SET `username`='$username' WHERE `id`='$id' LIMIT 1";
	$username=mysql_query($username) or die("Could not update username: " . mysql_error());
	
	$headline="UPDATE `users` SET `headline`='$headline' WHERE `id`='$id' LIMIT 1";
	$headline=mysql_query($headline) or die("Could not update headline: " . mysql_error());
	
	$email="UPDATE `users` SET `email`='$email' WHERE `id`='$id' LIMIT 1";
	$email=mysql_query($email) or die("Could not update email: " . mysql_error());
	
	$aim="UPDATE `users` SET `aim`='$aim' WHERE `id`='$id' LIMIT 1";
	$aim=mysql_query($aim) or die("Could not update aim: " . mysql_error());
	
	$irc="UPDATE `users` SET `irc`='$irc' WHERE `id`='$id' LIMIT 1";
	$irc=mysql_query($irc) or die("Could not update irc: " . mysql_error());
	
	$icq="UPDATE `users` SET `icq`='$icq' WHERE `id`='$id' LIMIT 1";
	$icq=mysql_query($icq) or die("Could not update icq: " . mysql_error());
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('admin_edituser_updated.tpl');
}
function adminEditSpace($id)
{
	$content="SELECT * FROM `space_content` WHERE `user_id`='$id'";
	$content=mysql_query($content) or die("Could not retrieve content: " . mysql_error());
	$content=mysql_fetch_array($content);
	$content=$content["content"];
	
	$rcontent="SELECT * FROM `right_content` WHERE `user_id`='$id' ";
	$rcontent=mysql_query($rcontent) or die("Coult not retrieve rcontent: " . mysql_error());
	$rcontent=mysql_fetch_array($rcontent);
	$rcontent=$rcontent["content"];
	
	$this->tpl->assign('content', $content);
	$this->tpl->assign('rcontent', $rcontent);
	$this->tpl->assign('id', $id);
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('admin_editspace.tpl');
}
function adminEditSpaceProcess($vars)
{
	$content=$vars["content"];
	$content=addslashes($content);
	$rcontent=$vars["rcontent"];
	$rcontent=addslashes($rcontent);
	$id=$vars["id"];
	
	$content="UPDATE `space_content` SET `content`='$content' WHERE `user_id`='$id' LIMIT 1";
	$content=mysql_query($content) or die("Could not update content: " . mysql_error());
	
	$rcontent="UPDATE `right_content` SET `content`='$rcontent' WHERE `user_id`='$id' LIMIT 1";
	$rcontent=mysql_query($rcontent) or die("Could not update rcontent: " . mysql_error());
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('admin_edituser_updated.tpl');
}
function adminDeleteComment($id)
{
	$query="SELECT * FROM `comments` WHERE `to`='$id' ORDER BY id DESC";
	$query=mysql_query($query) or die("Could not select comments: " . mysql_error());
	$i=0;
	while($info=mysql_fetch_array($query))
	{
		$from[$i]=$info["from"];
		$date[$i]=$info["date"];
		$content[$i]=$info["content"];
		$idx[$i]=$info["id"];
		$i++;
	}
	$this->tpl->assign('from', $from);
	$this->tpl->assign('date', $date);
	$this->tpl->assign('content', $content);
	$this->tpl->assign('id', $idx);
	$this->theme(); $this->tpl->display('admin_delete_comment.tpl');
}
function adminDeleteCommentProcess($id)
{
	$query="DELETE FROM `comments` WHERE `id`='$id' LIMIT 1";
	$query=mysql_query($query) or die("Error deleting comment: " . mysql_error());
	$newid= $id - 1;
	echo"Comment Deleted. Click <a href='admin.php?action=editcomments&id=$id>here</a> to return";
}
function adminDeleteUser($id)
{
	if($id != 1)
	{
		$query="DELETE FROM `users` WHERE `id`='$id' LIMIT 1";
		$query=mysql_query($query) or die("Error deleting user: " . mysql_error());
		$this->theme(); $this->tpl->display('admin_user_deleted.tpl');
	}
	else
	{
		die("You cannot delete this user because they have admin rights.");
	}
}
function deleteComment($id)
{
	$query="DELETE FROM `comments` WHERE `id`='$id' LIMIT 1";
	$query=mysql_query($query) or die("Error deleting comment: " . mysql_error());
	$this->isLoggedIn();
	$this->theme(); $this->tpl->display('comment_deleted.tpl');
}
function activate($code)
{
	$code=$this->escape($code);
	$query="UPDATE `users` SET `validated` = '1' WHERE `validateCode` = '" . $code . "' LIMIT 1";
	$query=mysql_query($query) or die("Could not activate user: " . mysql_error());
	$this->isLoggedIn();
	$this->theme();
	$this->tpl->display('activated.tpl');
}
function showBlog($id)
{
	$query="SELECT * FROM `blog_posts` WHERE `user_id`='$id' ORDER BY `id` DESC LIMIT 15";
	$query=mysql_query($query) or die("Could not get blog posts: " . mysql_error());
	if(mysql_num_rows($query) < 1)
	{
		die("This user has no posts");
	}
	else
	{
		$i=0;
		while($blog=mysql_fetch_array($query))
		{
			$title[$i]=$this->linebreaks(stripslashes($blog["title"]));
			$content[$i]=$this->linebreaks(stripslashes($blog["content"]));
			$mood[$i]=$this->linebreaks(stripslashes($blog["mood"]));
			$author[$i]=$blog["id"];
			
			$name="SELECT `username` FROM `users` WHERE `id` = '" . $author[$i] . "'";
			$name=mysql_query($name) or die("Could not get blog authors name: " . mysql_error());
			$name=mysql_fetch_array($name);
			$author[$i]=$name["username"];
			
			$i++;
		}
		$this->isLoggedIn();
		$this->tpl->assign('title', $title);
		$this->tpl->assign('content', $content);
		$this->tpl->assign('mood', $mood);
		$this->tpl->assign('author', $name["username"]);
		$this->theme(); 
		$this->tpl->display('blog.tpl');
	}
}
function blogPost()
{
	if(!(isset($_SESSION["userid"])))
	{
		die("You are not authorized to access this page.");
	}
	$this->tpl->assign('id', $_SESSION["userid"]);
	$this->theme(); $this->tpl->display('blogpost.tpl');
}
function blogPostSubmit($vars)
{
	if(!(isset($_SESSION["userid"])))
	{
		die("Hacking attempt.");
	}
	$title=$vars["title"];
	$content=$vars["content"];
	$mood=$vars["mood"];
	$time=date('g:i F h, Y');
	$id=$_SESSION["userid"];
	
	$title=$this->escape($title);
	$content=$this->escape($content);
	$mood=$this->escape($mood);
	
	$query="INSERT INTO `blog_posts` VALUES('', '" . $id . "', '" . $time . "', '" . $title . "', '" . $content . "', '" . $mood . "')";
	$query=mysql_query($query) or die("Could not post: " . mysql_error());
	$this->theme(); $this->tpl->display('blogpostsubmitted.tpl');
}
function showAllFriends($id)
{
      $friends_usr1="SELECT * FROM friends WHERE user1=$id AND accepted=1";
      $friends_usr1=mysql_query($friends_usr1) or die(mysql_error());
      $i=0;
      $isfriend=0;
      $isapproved=0;
      $friend_not_approv=0;
      while($friend=mysql_fetch_array($friends_usr1))
      {
        $temp[$i]=$friend['user2'];
        $username="SELECT username, id, icon FROM users WHERE id=$temp[$i]";
        $username=mysql_query($username) or die(mysql_error());
        $username=mysql_fetch_array($username);
        $user_friends[$i]=$username['username'];
        $user_friends_id[$i]=$username['id'];
        $user_friends_icon[$i]=$username['icon'];
        
        //Resize our profile image if necessary
     	$currsize=getImageSize(SPACES_DIRECTORY . 'user_uploaded/' . $username['id'] . '/' . 
		$username['icon']);
		$user_friends_icon_size[$i] = $this->imageResize($currsize[0], $currsize[1], 50);
      	$i++;
      }
      
      $friends_usr2="SELECT * FROM friends WHERE user2=$id AND accepted=1";
      $friends_usr2=mysql_query($friends_usr2) or die(mysql_error());
      while($friend=mysql_fetch_array($friends_usr2))
      {
        $temp[$i]=$friend['user1'];
        $username="SELECT username, id, icon FROM users WHERE id=$temp[$i]";
        $username=mysql_query($username) or die(mysql_error());
        $username=mysql_fetch_array($username);
        $user_friends[$i]=$username['username'];
        $user_friends_id[$i]=$username['id'];
        $user_friends_icon[$i]=$username['icon'];
        
        //Resize our profile image if necessary
     	$currsize=getImageSize(SPACES_DIRECTORY . 'user_uploaded/' . $username['id'] . '/' . 
		$username['icon']);
		$user_friends_icon_size[$i] = $this->imageResize($currsize[0], $currsize[1], 100);
      	$i++;
      }
      $this->tpl->assign('username', $user_friends);
      $this->tpl->assign('id', $user_friends_id);
      $this->tpl->assign('icon', $user_friends_icon);
      $this->tpl->assign('size', $user_friends_icon_size);
      $this->isLoggedIn();
      $this->theme();
      $this->tpl->display('friendlist.tpl');
}
function adminTemplateInstall()
{
	$this->adminAuth();
	//We are going to loop our way through the template directory
	//And look for ones we haven't installed
	
	//OK, first let's get our list of templates
	$_query="SELECT `name` FROM `styles`";
	$_query=mysql_query($_query) or die("Could not get template names");
	
	$i=0;
	while($temp = mysql_fetch_array($_query))
	{
		$install[$i] = $temp["name"];
		$i++;
	}
	$i=0;
	
	//OK, now we need to make our way through the templates directory and log each template's name
	$dir = SPACES_DIRECTORY . "templates/";
	if($handle = opendir($dir))
	{
		while(false !== ($template = readdir($handle)))
		{
			//Parent directory and this one do not count
			if($template != "." && $template != ".." && $template != ".svn" &&
			$template != "pngfix.js" && $template != "images" && $template != "index.html")
			{
				$style[$i] = $template;
				$i++;
			}
		}
		closedir($handle);
	}
	else
	{
		//We couldn't open the directory
		die("Could not open templates directory");
	}
	if($i<1)
	{
		die("You aren't supposed to see this error message.");
	}
	$i=0;
	//Now we are going to see if there's a different number of templates in each. If there is, that means
	//there is one to install
	if(count($install) == count($style))
	{
		die("There are no new templates to install!");
	}
	else
	{
		//Looks like there is one. Let's loop through the directory and look for one we haven't installed
		foreach($style as $directory)
		{
			foreach($install as $installed)
			{
				//We will put nonmatches into an array...
				if($directory != $installed)
				{
					$nonmatch[$i]=$directory;
					$i++;
				}
				else
				{
					//Is it listed as a nonmatch?
					foreach($nonmatch as $listed)
					{
						if($listed == $directory)
						{
							$listed="";
						}
					}
				}
			}
		}
		//Now we are going to go through nomatches
		//and remove empty files
		$i=0;
		foreach($nonmatch as $ifempty)
		{
			if($ifempty != "")
			{
				$templates[$i]=$ifempty;
				$i++;
			}
		}
		//Assign template variables
		$this->tpl->assign('style', $templates);
		$this->theme();
		$this->tpl->display('style_install.tpl');
	}
}
function adminStyleProcess($name)
{
	$this->adminAuth;
	$_query="INSERT INTO `styles` VALUES('', '" . $name . "');";
	$_query=mysql_query($_query) or die("Could not install style: " . mysql_error());
		
	//TODO: Make a page output this
	echo "New style installed.";
}

}
//That's all, folks!
?>
