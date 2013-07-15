<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */

/**
 * Print the header for the annotation admin pages.
 *
 * Creates a consistent navigation across the pages.
 *
 * @param array $subsections Array of names that specify the "path" to this page.
 * @return string
 */


function annotation_admin_header($subsections = array())
{
    $mainTitle = __('Annotation');
    $subsections = array_merge(array($mainTitle), $subsections);
    $displayTitle = implode(' | ', $subsections);
    $head = array('title' => $displayTitle,
            'bodyclass' => 'annotation',
            'content_class' => 'horizontal-nav');
    echo head($head);
}

/**
 * Get a link to the public annotation page.
 *
 * @param string $linkText
 * @param string $action Action to link to, main index if none.
 * @return string HTML
 */
function annotation_link_to_annotate($linkText = 'Annotate', $actionName = null)
{
    $url = annotation_annotate_url($actionName);
    return "<a href=\"$url\">$linkText</a>";
}

/**
 * Get a URL to the public annotation page.
 *
 * @param string $action Action to link to, main index if none.
 * @return string URL
 */
function annotation_annotate_url($actionName = null)
{
    $path = get_option('annotation_page_path');
    if (empty($path)) {
        $route = 'annotationDefault';
    } else {
        $route = 'annotationCustom';
    }
    $options = array();
    if (!empty($actionName)) {
        $options['action'] = $actionName;
    }
    return get_view()->url($options, $route, array(), true);
}

