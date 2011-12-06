<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// ----------------------------------------------------------------------
// Original Author of file: Damien Touraine
// Purpose of file:
// ----------------------------------------------------------------------


define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

$nn = new NetworkName();

if (isset($_POST["add"])) {
   $nn->check(-1, 'w', $_POST);
   $nn->add($_POST);
   Event::log(0, "networkname", 5, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][124]);
   Html::back();

} else if (isset($_POST["delete"])) {
   $nn->check($_POST['id'], 'd');
   $nn->delete($_POST);
   Event::log($_POST["id"], "networkname", 5, "inventory",
              $_SESSION["glpiname"]." ".$LANG['log'][126]);
   $node = new $nn->fields["itemtype"]();
   if ($node->can($nn->fields["items_id"], 'r')) {
      Html::redirect($node->getLinkURL());
   }
   Html::redirect($CFG_GLPI["root_doc"]."/front/central.php");

} else if (isset($_POST["update"])) {
   $nn->check($_POST['id'], 'w');
   $nn->update($_POST);
   Event::log($_POST["id"], "networkname", 4, "inventory",
              $_SESSION["glpiname"]." ".$LANG['log'][125]);
   Html::back();

} else if (isset($_POST['assign_address'])) { // From NetworkPort or NetworkEquipement
   $nn->check($_POST['addressID'],'w');

   if ((!empty($_POST['itemtype'])) && (!empty($_POST['items_id']))) {
      $node = new $_POST['itemtype']();
      $node->check($_POST['items_id'],'w');
      NetworkName::affectAddress($_POST['addressID'], $_POST['items_id'], $_POST['itemtype']);
      Event::log(0, "networkport", 5, "inventory", $_SESSION["glpiname"]."  ".$LANG['log'][79]);
      Html::back();
   } else {
      Html::displayNotFoundError();
   }

} else if (isset($_GET['remove_address'])) { // From NetworkPort or NetworkEquipement
   if ($_GET['remove_address'] == "purge") {
      $nn->check($_GET['id'],'d');
      $nn->delete($_GET);
      Event::log($nn->getID(), $nn->getType(), 5, "inventory",
                 $_SESSION["glpiname"]." ".$LANG['log'][126]);
   } else {
      $nn->check($_GET['id'],'w');
      NetworkName::unaffectAddressByID($_GET['id']);
      Event::log($nn->getID(), $nn->getType(), 5, "inventory",
                 $_SESSION["glpiname"]."  ".$LANG['log'][79]);
   }
   Html::back();

} else {
   if (!isset($_GET["id"])) {
      $_GET["id"] = "";
   }
   if (empty($_GET["items_id"])) {
      $_GET["items_id"] = "";
   }
   if (empty($_GET["itemtype"])) {
      $_GET["itemtype"] = "";
   }

   Session::checkRight("internet","w");
   Html::header($LANG['title'][6],$_SERVER['PHP_SELF'],"inventory");

   $nn->showForm($_GET["id"], $_GET);
   Html::footer();
}
?>