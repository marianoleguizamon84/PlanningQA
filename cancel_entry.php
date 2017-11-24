<?php
ob_start();
// $Id: del_entry.php 1288 2009-12-17 18:32:24Z cimorrison $

require_once "defaultincludes.inc";
require_once "mrbs_sql.inc";

// Get form variables
$day = get_form_var('day', 'int');
$month = get_form_var('month', 'int');
$year = get_form_var('year', 'int');
$area = get_form_var('area', 'int');
$id = get_form_var('id', 'int');
$series = get_form_var('series', 'int');
$returl = get_form_var('returl', 'string');
$action = get_form_var('action', 'string');
$note = get_form_var('note', 'string');

$id = $_GET['id'];
$returl = $_GET['returl'];

if (!isset($note))
{
  $note = "";
}

if (empty($returl))
{
  switch ($default_view)
  {
    case "month":
      $returl = "month.php";
      break;
    case "week":
      $returl = "week.php";
      break;
    default:
      $returl = "day.php";
  }
  $returl .= "?year=$year&month=$month&day=$day&area=$area";
}


  sql_begin();
  $result = mrbsCancelEntry($id);
  sql_commit();
  header("Location: $returl");
  exit();
    

/*if (getAuthorised(1) && ($info = mrbsGetBookingInfo($id, FALSE, TRUE)))
{
  $user = getUserName();
  // check that the user is allowed to delete this entry
  if (isset($action) && ($action="reject"))
  {
    $authorised = auth_book_admin($user, $info['room_id']);
  }
  else
  {
    $authorised = getWritable($info['create_by'], $user, $info['room_id']);
  }
  if ($authorised)
  {
    
    sql_begin();
    $result = mrbsCancelEntry($id);
    sql_commit();
    if ($result)
    {
      
      header("Location: $returl");
      exit();
    }
  }
}*/

// If you got this far then we got an access denied.
//showAccessDenied($day, $month, $year, $area, "");
ob_end_flush();
?>
