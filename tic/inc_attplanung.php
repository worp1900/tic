<table align="center">
  <tr>
    <td class="datatablehead">Attplaner 3.0</td>
  </tr>
<?php
   if ($Benutzer['rang'] >= $Rang_VizeAdmiral) {
	echo "  <tr><td class=\"fieldnormallight\"><a href=\"?modul=attplaneradmin\">Attplaner Config</a></td></tr>";
   }
?>
  <tr>
    <td class="fieldnormallight"><a href="?modul=attplanerlist">Attplaner Liste</a></td>
  </tr>
  <tr>
    <td class="fieldnormallight"><a href="?modul=atteinplanen">Neues Att-Ziel erfassen</a></td>
  </tr>
  <tr>
    <td class="fieldnormallight"><a href="?modul=scanliste">Scannerliste</a></td>
  </tr>
</table>