<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

defined('JPATH_PLATFORM') or die;

$value = $displayData;

if($value){
    ?>
    <span class="icon-publish"></span>
    <?php
}else{
    ?>
    <span class="icon-unpublish"></span>
    <?php
}