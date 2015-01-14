<!DOCTYPE html>
<html>
<head>
<?php 
echo '  <title>' . (isset($title) ? $title : 'ESB - Monitoring des flux') . '</title>' . PHP_EOL;
echo '  <link rel="icon" type="image/png" href="images/favicons/' . (!empty($action) ? $action : 'list') . '.png" />' . PHP_EOL;
?>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" type="text/css" href="css/styles.css" />
</head>
<body>
  <div id="header">
    <img src="images/logo.jpg" alt="Logo OCP" />
    <h1>ESB - Monitoring de flux</h1>
    <div id="menu">
      <ul>
        <li>Interfaces :</li>
        <li><a href="?action=list"<?php echo ($action=='list' ? ' class="active"' : '') ?>>Liste</a></li>
<?php if (!empty($id)) {
  echo '        <li> - </li>' . PHP_EOL;
  echo '        <li><a href="?action=history&amp;id=' . $id . '"' . ($action=='history' ? ' class="active"' : '') . '>Historique</a></li>' . PHP_EOL;
} ?>
<?php if (!empty($id)) {
  echo '        <li> - </li>' . PHP_EOL;
  echo '        <li><a href="?action=info&amp;id=' . $id . '"' . ($action=='info' ? ' class="active"' : '') . '>Fiche</a></li>' . PHP_EOL;
} ?>
      </ul>
    </div>
  </div>
  <div id="content">
