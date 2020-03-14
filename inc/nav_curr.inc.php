<?php
$n_home = null;
$n_home_css = null;

if($page == 'home') {
    $n_home = '<span class="sr-only">(current)</span>';
    $n_home_css = 'active';
}

$tpl_b->repl('curr_home', $n_home);
$tpl_b->repl('curr_home_css', $n_home_css);

###############################
# next
###############################

$n_changelog = null;
$n_changelog_css = null;

if($page == 'changelog') {
    $n_changelog = '<span class="sr-only">(current)</span>';
    $n_changelog_css = 'active';
}

$tpl_b->repl('curr_changelog', $n_changelog);
$tpl_b->repl('curr_changelog_css', $n_changelog_css);








?>


