<?php 

//where does $element come from? -> from controller AnnotationController AJAX call
//also pass annotationElement for additional controls
//print_r($annotationTool[0]);
echo annotation_element_form($element, $record, array('divWrap'=>false, 'extraFieldCount'=>0, 'annotationTypeElement'=>$annotationTypeElement[0]));//, 'annotationTool'=>$annotationTool[0])); 
?>