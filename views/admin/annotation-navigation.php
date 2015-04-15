<nav id="section-nav" class="navigation vertical">
<?php
    
    if(is_allowed('annotation_Types', 'edit')) {
        $navArray = array(
        'Getting Started' => array('label'=>__('Getting Started'), 'uri'=>url('annotation/index') ),
        'Annotate' => array('label'=>__('Annotate')."!", 'uri'=>url('annotation/annotation') ),
        'Annotation Types' => array('label'=>__('Annotation Types'), 'uri'=>url('annotation/types') ),
        'Annotation Tools' => array('label'=>__('Annotation Tools'), 'uri'=>url('annotation/tools') ),
        'Submission Settings' => array('label'=> __('Submission Settings'), 'uri'=>url('annotation/settings') ) 
        );
        
    } else {
        $navArray = array();
    }    
    
    $navArray['annotators'] = array('label'=> __('Annotations'), 
                                        'uri'=>url('annotation/items') );
 
    echo nav($navArray, 'annotation_navigation');
?>
</nav>