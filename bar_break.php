<?php

global $db;

session_start();
include("vars.php");
flush();
header("Cache-control: private");
$_SESSION = array();
session_destroy();
?>
<html>

<head>
  <title><?php echo ("$title"); ?> - Surf Error</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script>
    <!--
    if (window != top) {
      top.location.href = location.href;
    }
    // 
    -->
  </script>
  <?php echo ("<meta http-equiv=\"Refresh\" content=\"1;URL=$self_url\">"); ?>
</head>

<body>
  <div align="center">
    <table width="100%" border="0">
      <tr>
        <td height="79">
          <div align="center">
            <font size="2" face="<?php echo ("$fontface"); ?>"><strong><a href="<?php echo ("$self_url" . "login.php"); ?>" target="_top">Click
                  Here to Login</a></strong></font>
          </div>
        </td>
        <td width="69%" valign="top">
          <div align="center">
            <p><strong><?php echo ($_SERVER['QUERY_STRING']); ?><br>
              </strong></p>
          </div>
        </td>
      </tr>
    </table>
  </div>
</body>

</html>
<?php
$db->close();
exit;
?>