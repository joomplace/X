<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

defined('JPATH_PLATFORM') or die;

$value = $displayData['value'];
$task = $displayData['task'];
$id = $displayData['id'];

\JHtml::_( 'behavior.framework' );
\JHtmlGrid::behavior();
echo \JHtmlGrid::published($value, $id,'tick.png','publish_x.png','proxy.model.');