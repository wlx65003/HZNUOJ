<?php
  /**
   * This file is modified
   * by yybird
   * @2016.05.25
  **/
?>

<?php 
  require("admin-header.php");
  require_once("../include/set_get_key.php");
  require_once("../include/my_func.inc.php");
  // everyone can see
  // if (!HAS_PRI("edit_privilege_group")) {
  //   echo "Permission denied!";
  //   exit(1);
  // }
  echo "<title>Privilege List</title>"; 
  echo "<h1>Privilege List</h1><hr/>";
  $sql=<<<sql
    SELECT
      a.*,
      b.group_order
    FROM 
      privilege a 
    JOIN
      privilege_groups b
    ON
      a.rightstr=b.group_name
    ORDER BY
      b.group_order
sql;
  
  $user_group=mysql_fetch_array(mysql_query("SELECT `rightstr` FROM `privilege` WHERE `user_id`='".mysql_real_escape_string($_SESSION['user_id'])."'"))[0];
  $user_order=get_order($user_group);
  // echo "<pre>user_group:$user_group</pre>";
  // echo "<pre>user_order:$user_order</pre>";

  $result=mysql_query($sql) or die(mysql_error());
  echo "<center><table class='table table-condensed table-striped table-hover'>";
  echo "<thead><tr><th>user<th>right<th>defunct</tr></thead>";
  $can_delete=false;
  for (;$row=mysql_fetch_object($result);){
    echo "<tr>";
    echo "<td>".$row->user_id;
    echo "<td>".$row->rightstr;
  //  echo "<td>".$row->start_time;
  //  echo "<td>".$row->end_time;
  //  echo "<td><a href=contest_pr_change.php?cid=$row->contest_id>".($row->private=="0"?"Public->Private":"Private->Public")."</a>";
    if (HAS_PRI("edit_privilege_group") && $user_order<get_order($row->rightstr)) 
      echo "<td><a href=privilege_delete.php?uid=$row->user_id&rightstr=$row->rightstr&getkey=".$_SESSION['getkey'].">Delete</a>";
    else echo "<td></td>";
  //  echo "<td><a href=contest_edit.php?cid=$row->contest_id>Edit</a>";
  //  echo "<td><a href=contest_add.php?cid=$row->contest_id>Copy</a>";
    echo "</tr>";
  }
  echo "</table></center>";
?>
<?php 
  require_once("admin-footer.php")
?>