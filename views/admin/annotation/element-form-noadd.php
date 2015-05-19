<?php 

//where does $element come from? -> from controller AnnotationController AJAX call
//also pass annotationElement for additional controls

echo annotation_element_form($element, $record, array('divWrap'=>false, 'extraFieldCount'=>0, 'annotationTypeElement'=>$annotationTypeElement[0]));

?>