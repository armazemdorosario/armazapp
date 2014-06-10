<?php
$loader = require 'vendor/autoload.php';
use armazemapp\FacebookAdapter;
$facebook = new FacebookAdapter();
$current_user = $facebook->getUser();

$eventfbid = $_GET['eventfbid'];
$benefit_type = $_GET['benefit'];
$event = $facebook->getEventInfo($_GET['eventfbid']);

if(!$facebook::userIsAdmin() || !isset($eventfbid) || !isset($benefit_type)) {
	die();
}

if($benefit_type == '1') {
	$users = $facebook->getEventAttendees($eventfbid, $benefit_type);
	$filename = 'Lista VIP';
}
elseif($benefit_type == '2') {
	$users = $facebook->getChosenUsersFor($benefit_type, $eventfbid);
	$filename = 'Sorteio';
}

if(!isset($users) || is_null($users)) { die(); }

header('Content-disposition: attachment; filename="' . $event['name'] . ' - ' . $filename . '.xls"');
header('Content-type: application/msexcel"');
?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
	<head>
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    	<meta name="ProgId" content="Excel.Sheet" />
        <meta name="Generator" content="Microsoft Excel 11" />
        <style type="text/css" media="screen">
			<?php include 'css/theme.css'; ?>
		</style>
<!--[if gte mso 9]><xml>
 <x:excelworkbook>
  <x:excelworksheets>
   <x:excelworksheet>
    <x:name>** WORKSHEET NAME **</x:name>
    <x:worksheetoptions>
     <x:selected></x:selected>
     <x:freezepanes></x:freezepanes>
     <x:frozennosplit></x:frozennosplit>
     <x:splithorizontal>** FROZEN ROWS + 1 **</x:splithorizontal>
     <x:toprowbottompane>** FROZEN ROWS + 1 **</x:toprowbottompane>
     <x:splitvertical>** FROZEN COLUMNS + 1 **</x:splitvertical>
     <x:leftcolumnrightpane>** FROZEN COLUMNS + 1**</x:leftcolumnrightpane>
     <x:activepane>0</x:activepane>
     <x:panes>
      <x:pane>
       <x:number>3</x:number>
      </x:pane>
      <x:pane>
       <x:number>1</x:number>
      </x:pane>
      <x:pane>
       <x:number>2</x:number>
      </x:pane>
      <x:pane>
       <x:number>0</x:number>
      </x:pane>
     </x:panes>
     <x:protectcontents>False</x:protectcontents>
     <x:protectobjects>False</x:protectobjects>
     <x:protectscenarios>False</x:protectscenarios>
    </x:worksheetoptions>
   </x:excelworksheet>
  </x:excelworksheets>
  <x:protectstructure>False</x:protectstructure>
  <x:protectwindows>False</x:protectwindows>
 </x:excelworkbook>
</xml><![endif]-->
    </head>
    <body>
    	<?php include 'views/admin-list-winners.phtml'; ?>
    </body>
</html>