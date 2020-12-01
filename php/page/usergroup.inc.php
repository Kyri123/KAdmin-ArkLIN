<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

if(!$session_user->perm("all/is_admin")) {
    header("Location: $ROOT/401");
    exit;
}

$resp       = null;
$pageicon   = "<i class=\"fas fa-users\" aria-hidden=\"true\"></i>";
$pagename   = "{::lang::allg::nav::usergroup}";
$urltop     = "<li class=\"breadcrumb-item\">$pagename</li>";
$TPL_PAGE   = new Template("index.htm", __ADIR__."/app/template/core/$page/");
$TPL_PAGE->load();

// Erstelle eine Benutzergruppe
if(isset($_POST["addgroup"])) {
    $GROUPNAME      = $_POST["group_name"];
    $PERMISSIONS    = isset($_POST["permissions"]) ? $helper->jsonToString($_POST["permissions"]) : "{}";
    $PERMISSIONS    = str_replace("\"on\"", 1, $PERMISSIONS);
    $TEST_QUERY     = "SELECT * FROM `ArkAdmin_user_group` WHERE `name`='$GROUPNAME'";

    if($mycon->query($TEST_QUERY)->numRows() == 0) {
        if($GROUPNAME != "") {
            $QUERY      = "INSERT INTO `ArkAdmin_user_group` 
                                (id, name, editform, time, permissions, canadd) VALUES 
                                (null, ?, ".$user->read('id').", ".time().", '$PERMISSIONS', '[]')";
            if($mycon->query($QUERY, $GROUPNAME)) {
                $resp .= $alert->rd(100);
            }
            else {
                $resp .= $alert->rd(3);
            }
        }
        else {
            $resp .= $alert->rd(2);
        }
    }
    else {
        $resp .= $alert->rd(5);
    }
}

// Ändern von Benutzergruppen
if(isset($_POST["editgroup"])) {
    $ID             = $_POST["id"];
    $GROUPNAME      = $_POST["group_name"];
    $PERMISSIONS    = isset($_POST["permissions"]) ? $helper->jsonToString($_POST["permissions"]) : "{}";
    $PERMISSIONS    = str_replace("\"on\"", 1, $PERMISSIONS);
    $TEST_QUERY     = "SELECT * FROM `ArkAdmin_user_group` WHERE `id`='$ID'";

    if($mycon->query($TEST_QUERY)->numRows() > 0 && $ID != 1) {
        if($GROUPNAME != "") {
            $QUERY      = "UPDATE `ArkAdmin_user_group` 
                                SET name= ?, editform='".$user->read("id")."', time='".time()."', permissions='$PERMISSIONS'
                                WHERE `id`='$ID'";
            if($mycon->query($QUERY, $GROUPNAME)) {
                $resp .= $alert->rd(102);
            }
            else {
                $resp .= $alert->rd(3);
            }
        }
        else {
            $resp .= $alert->rd(2);
        }
    }
    else {
        $resp .= $alert->rd(30);
    }
}

// Löschen von Benutzergruppen
if(isset($_POST["removegroup"])) {
    $ID             = $_POST["id"];
    $TEST_QUERY     = "SELECT * FROM `ArkAdmin_user_group` WHERE `id`='$ID'";

    if($mycon->query($TEST_QUERY)->numRows() > 0 && $ID != 1) {
        $QUERY      = "DELETE FROM `ArkAdmin_user_group` WHERE `id`='$ID'";
        if($mycon->query($QUERY)) {
            $resp .= $alert->rd(101);
        }
        else {
            $resp .= $alert->rd(3);
        }
    }
    else {
        $resp .= $alert->rd(30);
    }
}

// Kenn geben
if(isset($_POST["canadd"])) {
    $ID             = $_POST["id"];
    $CANADD        = str_replace('"', null, isset($_POST["ids"]) ? $helper->jsonToString($_POST["ids"]) : "[]");
    $TEST_QUERY     = "SELECT * FROM `ArkAdmin_user_group` WHERE `id`='$ID'";

    if($mycon->query($TEST_QUERY)->numRows() > 0 && $ID != 1) {
        $QUERY      = "UPDATE `ArkAdmin_user_group` 
                                SET canadd='$CANADD'
                                WHERE `id`='$ID'";
        if($mycon->query($QUERY)) {
            $resp .= $alert->rd(102);
        }
        else {
            $resp .= $alert->rd(3);
        }
    }
    else {
        $resp .= $alert->rd(30);
    }
}

// Gruppenliste

$USER_QUERY     = "SELECT * FROM `ArkAdmin_users`";
$USER_ARRAY     = $mycon->query($USER_QUERY)->fetchAll();
$TEST_QUERY     = "SELECT * FROM `ArkAdmin_user_group` ORDER BY `id`";
$QUERY          = $mycon->query($TEST_QUERY);
$GROUPS         = null;
$GROUPS_MODALS  = null;

if($QUERY->numRows() > 0) {
    foreach ($QUERY->fetchAll() as $KEY => $ITEM) {
        $JSID               = rndbit(5).$ITEM["id"];
        $TPL_LIST           = new Template("group.htm", __ADIR__."/app/template/lists/$page/");
        $TPL_LIST_MODALS    = new Template("group_modals.htm", __ADIR__."/app/template/lists/$page/");

        $GROUP_PERM = array_replace_recursive($D_PERM_ARRAY, $helper->stringToJson($ITEM["permissions"]));

        // Listitems
        $TPL_LIST->load();

        $TPL_LIST->rif("id1", $ITEM["id"] == 1);
        $TPL_LIST->rif("addgroups", ($GROUP_PERM["userpanel"]["add_group"] == 1 && $GROUP_PERM["userpanel"]["show"] == 1));
        $TPL_LIST->r("id", $JSID);
        $TPL_LIST->r("name", $ITEM["name"]);
        $TPL_LIST->r("lastedit", date("d.m.Y - H:i", $ITEM["time"]));

        $count = 0;
        foreach ($USER_ARRAY as $I) {
            $JSON = json_decode($I["rang"]);
            foreach ($JSON as $RI) {
                if($ITEM["id"] == $RI) {
                    $count++;
                    break;
                }
            }
        }
        $TPL_LIST->r("count", $count);

        $user->setid($ITEM["editform"]);
        $TPL_LIST->r("lastedit_from", $user->read("username"));

        $GROUPS         .= $TPL_LIST->load_var();

        // MODALitems
        $TPL_LIST_MODALS->load();

        $ADD_ARRAY = $helper->stringToJson($ITEM["canadd"]);
        $ADDLIST = null;

        $GROUP_QUERY     = "SELECT * FROM `ArkAdmin_user_group` ORDER BY `id`";
        foreach ($mycon->query($GROUP_QUERY)->fetchAll() as $K => $I) {
            $TPL_iCHECK_LIST    = new Template("from_item_add.htm", __ADIR__."/app/template/lists/$page/");
            $TPL_iCHECK_LIST->load();

            $TPL_iCHECK_LIST->rif("active", in_array($I["id"], $ADD_ARRAY));
            $TPL_iCHECK_LIST->r("ro", /*$I["id"] == 1 ? " disabled" : */"");
            $TPL_iCHECK_LIST->r("qid", $I["id"]);
            $TPL_iCHECK_LIST->r("md5name", $I["id"].rndbit(5));
            $TPL_iCHECK_LIST->r("name", $I["name"]);

            $ADDLIST .= $TPL_iCHECK_LIST->load_var();
        }

        $TPL_LIST_MODALS->rif("id1", $ITEM["id"] == 1);
        $TPL_LIST_MODALS->rif("addgroups", ($GROUP_PERM["userpanel"]["add_group"] == 1 && $GROUP_PERM["userpanel"]["show"] == 1));
        $TPL_LIST_MODALS->r("id", $JSID);
        $TPL_LIST_MODALS->r("form_editgroup", creatform($GROUP_PERM, $GROUP_PERM));
        $TPL_LIST_MODALS->r("qid", $ITEM["id"]);
        $TPL_LIST_MODALS->r("md5id", md5($ITEM["id"]));
        $TPL_LIST_MODALS->r("addlist", $ADDLIST);
        $TPL_LIST_MODALS->r("name", $ITEM["name"]);

        $GROUPS_MODALS  .= $TPL_LIST_MODALS->load_var();
    }
}

$btns .= '  <a href="#" class="btn btn-outline-success btn-icon-split rounded-0 ml-2" data-toggle="modal" data-target="#addgroup">
                <span class="icon">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                </span>
            </a>';

$TPL_PAGE->r("resp", $resp);
$TPL_PAGE->r("groups", $GROUPS);
$TPL_PAGE->r("groups_modals", $GROUPS_MODALS);
$TPL_PAGE->r("form_addgroup", creatform($D_PERM_ARRAY, $D_PERM_ARRAY));

$content = $TPL_PAGE->load_var();