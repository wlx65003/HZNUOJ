<?php

?>

<?php if (!isset($_POST['class_checkbox'])) { ?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
	    <meta name="description" content="">
	    <meta name="author" content="">
	    <link rel="icon" href="../../favicon.ico">
	    <title> contestrank-solution </title>
	</head>
	<body>
		<?php require_once("../include/db_info.inc.php");?>

		<!--  form  -->
		<form action="contestrank-solutions.php?cid=<?php echo $_GET['cid'] ?>" method='post'>
			<?php
				if (!isset($_GET['cid'])) 
					die("No Such Contest!");
				$cid=intval($_GET['cid']);

				//echo "contest_id: $cid<br />";
				echo "contest_id: <input type='text' value='$cid' name='cids' /><br />";
				
				// $sql = "SELECT
				//           DISTINCT(class)
				//         FROM
				//           (select * from solution where solution.contest_id='$cid') solution
				//             left join users
				//           on users.user_id=solution.user_id
				//         ORDER BY class";

				$sql = "SELECT DISTINCT(class) FROM users ORDER BY class";
				$result = mysql_query($sql) or die(mysql_error());

				$i = 0;
				$classSet = array();
				while ($row = mysql_fetch_object($result)) {
					if ($row->class == '' || $row->class == 'null')
						continue;
					$classSet[$i] = $row->class;
					$i++;
				}

				for ($t = 0; $t < $i; ++$t) {
					echo "<input type='checkbox' name='class_checkbox[]' value='$classSet[$t]'>$classSet[$t]</input><br />";
				}
			?>
			<input type='submit'></input>&nbsp;<input type='reset'></input>
		</form>
	</body>
</html>

<?php
	
	} else {

		$cids = $_POST['cids'];
		$cids = explode(',', $cids);
		$class_checkbox = $_POST['class_checkbox'];

		function delete_dir($dir) {
			$dh = opendir($dir);
			while ($file = readdir($dh)) {
				if ($file != "." && $file != "..") {
					$fullpath = $dir."/".$file;
					if (!is_dir($fullpath)) {
						unlink($fullpath);
						echo "delete file ".$fullpath."<br />";
					} else {
						delete_dir($fullpath);
					}
				}
			}

			closedir($dh);

			if (rmdir($dir)) {
				echo "delete dir ".$dir."<br />";
				return true;
			} else {
				return false;
			}
		}

		function create($cid) {
			global $class_array;
			global $root_dir;
			$fp = NULL;

			$MSG_Pending="Pending";
			$MSG_Pending_Rejudging="Pending Rejudging";
			$MSG_Compiling="Compiling";
			$MSG_Running_Judging="Running & Judging";
			$MSG_Accepted="Accepted";
			$MSG_Presentation_Error="Presentation Error";
			$MSG_Wrong_Answer="Wrong Answer";
			$MSG_Time_Limit_Exceed="Time Limit Exceed";
			$MSG_Memory_Limit_Exceed="Memory Limit Exceed";
			$MSG_Output_Limit_Exceed="Output Limit Exceed";
			$MSG_Runtime_Error="Runtime Error";
			$MSG_TEST_RUN="Test Running Done";
			$MSG_Compile_Error="Compile Error";
			$MSG_Compile_OK="";
			   
			$language_name=Array("C","C++","Pascal","Java","Ruby","Bash","Python","PHP","Perl","C#","Obj-C","FreeBasic","Schema","Clang","Clang++","Lua","Swift","Other Language");
			$judge_result=Array($MSG_Pending,$MSG_Pending_Rejudging,$MSG_Compiling,$MSG_Running_Judging,$MSG_Accepted,$MSG_Presentation_Error,$MSG_Wrong_Answer,$MSG_Time_Limit_Exceed,		 $MSG_Memory_Limit_Exceed,$MSG_Output_Limit_Exceed,$MSG_Runtime_Error,$MSG_Compile_Error,$MSG_Compile_OK,$MSG_TEST_RUN);

			$sql="SELECT
		        source_code.source,source_code.solution_id
		                FROM
		                        (select * from solution where solution.contest_id='$cid' ) solution
		                left join source_code
		                on source_code.solution_id=solution.solution_id";

	        $result = mysql_query($sql) or die(mysql_error());
	        $rows_cnt = mysql_num_rows($result);
	        
	        //echo $rows_cnt;
	        //echo $root_dir."/".$cid;
	        if (is_dir($root_dir."/".$cid)) {
				delete_dir($root_dir."/".$cid);
				//echo "delete $root_dir/$cid";
			}

			mkdir($root_dir."/".$cid, 0777);
			echo "create dir ".$root_dir."/".$cid."<br />";

	        for ($i = 0; $i < $rows_cnt; $i++) {
	        	$row = mysql_fetch_object($result);
	        	$sid = $row->solution_id;
	        	
	        	$sql = "select class from (select user_id from solution where solution_id = '$sid') solution left join users on users.user_id=solution.user_id";
	        	$temp_result = mysql_query($sql);
	        	$temp_row = mysql_fetch_object($temp_result);
	        	$sclass = $temp_row->class;

	        	if (!empty($class_array[$sclass])) {

	        		$fp = fopen($root_dir."/$cid/solution_of_$sclass.txt", "a+");
		        	
		        	$source = $row->source;
		        	
					//echo "/**************************************************************<br />";
		        	fwrite($fp, "\r\n**************************************************************\r\n");

		        	//echo "<pre>".htmlspecialchars($source)."</pre>";
		        	fwrite($fp, $source);
		        	
		        	$sql = "select * from solution where solution_id=$sid";
		        	$temp_result = mysql_query($sql);
		        	$srow = mysql_fetch_object($temp_result);
		        	$sproblem_id = $srow->problem_id;
		        	$suser_id = $srow->user_id;
		        	$slanguage = $srow->language;
		        	$sresult = $srow->result;
		        	$stime = $srow->time;
		        	$smemory = $srow->memory;
		        	$sip = $srow->ip;
		        	
		        	$sql = "select nick from users where user_id='$suser_id'";
		        	$temp_result = mysql_query($sql);
		        	$srow = mysql_fetch_object($temp_result);
		        	$snick = $srow->nick;
		        	
				//echo "Problem: $sproblem_id<br />User: $suser_id&nbsp;($snick)<br />";
				fwrite($fp, "\r\n\r\nProblem: $sproblem_id\r\nUser: $suser_id&nbsp;($snick)\r\n");
				//echo "Language: ".$language_name[$slanguage]."<br />Result: ".$judge_result[$sresult]."<br />";
				fwrite($fp, "Language: ".$language_name[$slanguage]."\r\nResult: ".$judge_result[$sresult]."\r\n");
				//echo "Sresult: ".$sresult."<br />";
				fwrite($fp, "Sresult: ".$sresult."\r\n");
				
				if ($sresult==4){
					//echo "Time:".$stime." ms<br />";
					fwrite($fp, "Time:".$stime." ms\r\n");
					//echo "Memory:".$smemory." kb<br />";
					fwrite($fp, "Memory:".$smemory." kb\r\n");
				}
				//echo "ip: ".$sip."<br />";
				fwrite($fp, "ip: ".$sip."\r\n");
				
				//echo "****************************************************************/<br /><br />";
				fwrite($fp, "****************************************************************\r\n\r\n");
			}
	    	}

	    	foreach ($class_array as $key => $value) {
	    		if ($value == true) {
	    			if (file_exists($root_dir."/$cid/solution_of_$key.txt")) {
	    				$fp = fopen($root_dir."/$cid/solution_of_$key.txt", "a+");
	    				fclose($fp);
	    			}
	    		}
	    	}
		}

		function downloads($name) {
				
	        $file_dir=""; 
	         
	        if (!file_exists($file_dir.$name)){
	            header("Content-type: text/html; charset=utf-8");
	            echo "File not found!";
	            exit; 
	        } else {
	            $file = fopen($file_dir.$name,"r"); 
	            Header("Content-type: application/octet-stream");
	            Header("Accept-Ranges: bytes");
	            Header("Accept-Length: ".filesize($file_dir . $name));
	            Header("Content-Disposition: attachment; filename=".$name);
	            echo fread($file, filesize($file_dir.$name));
	            fclose($file);
	        }
		}

		foreach ($class_checkbox as $key => $value) {
			$class_array[$value] = true;
		}

		$root_dir = "../hhhh";
		//$root_dir = realpath($root_dir);

		$link = mysql_connect('172.17.151.2', 'root', 'hznujudge');
		//$link = mysql_connect('localhost', 'root', 'root');
		mysql_query("set names GB2312;");
		
		if (!$link) {
			die('connect fail');
		}
		mysql_select_db('jol', $link);

		delete_dir($root_dir);
		mkdir($root_dir, 0777);

		foreach ($cids as $cid) {
			$cid = intval($cid);

			//----------------------------------------------------------main()----------------------------------------------------------
			$zipname = "solutions.zip";
			$rootPath = realpath($root_dir);
			//$rootPath = $root_dir;

			// if (!is_dir($root_dir)) {
			// 	mkdir($root_dir);
			// }

			create($cid);
		 
			if (file_exists($zipname)) {
				unlink($zipname);
			}

			 // Get real path for our folder
			 //$rootPath = realpath('hhhh');

			 // Initialize archive object
			 $zip = new ZipArchive();
			 $zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

			 // Create recursive directory iterator
			 /** @var SplFileInfo[] $files */
			 $files = new RecursiveIteratorIterator(
			     new RecursiveDirectoryIterator($rootPath),
			     RecursiveIteratorIterator::LEAVES_ONLY
			 );

			 foreach ($files as $name => $file)
			 {
			     // Skip directories (they would be added automatically)
			     if (!$file->isDir())
			     {
			         // Get real and relative path for current file
			         $filePath = $file->getRealPath();
			         $relativePath = substr($filePath, strlen($rootPath) + 1);

			         // Add current file to archive
			         $zip->addFile($filePath, $relativePath);
			     }
			 }

			 // Zip archive will be created only after closing object
			 $zip->close();

		    downloads($zipname);
		 //    $file = $zip;
		 //    if (file_exists($file))
			// {
			// 	if (FALSE!== ($handler = fopen($file, 'r')))
			// 	{
			// 		header('Content-Description: File Transfer');
			// 		header('Content-Type: application/octet-stream');
			// 		header('Content-Disposition: attachment; filename='.basename($file));
			// 		header('Content-Transfer-Encoding: chunked'); //changed to chunked
			// 		header('Expires: 0');
			// 		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			// 		header('Pragma: public');
			// 		//header('Content-Length: ' . filesize($file)); //Remove

			// 		//Send the content in chunks
			// 		while(false !== ($chunk = fread($handler,4096)))
			// 		{
			// 			echo $chunk;
			// 		}
			// 	}
			// 	exit;
			// }
			// echo "<h1>Content error</h1><p>The file does not exist!</p>";
		}
	}
?>