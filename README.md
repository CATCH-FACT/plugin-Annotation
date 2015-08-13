# ABOUT

Annotation provides the user with an annotation interface for Items. Fields can be coupled to webapplications that generate metadatavalues. These values will be added to the item with a single click. 

Another function of the plugin is cloning. An Item can be cloned, and the fields that are cloned can be chosen from a list of metadatafields and values.

# REQUIREMENTS

Omeka 2.1

# CONFIGURATION

* Tools are individually configured.
* Each itemtype can be configured with specialized metadata fields.
* Metadata fields can be configured with a lot of extra's like date picker, long / short text, simple vocab and autocomplete based on existing values in the database.
* Cloning fields are deselected when smaller than 50 characters.

# INTERFACE CHANGES

In the browse section 2 links are added to each item: Annotate and Clone.
In the Dashboard an extra section is added where new Items can be created with the Annotation interface. It also shows recently annotated items and if there is a collection that contains unfinished items, it can be presented on the Dashboard as well.

# USE CASES

## Folktale annotation

If the text of a folktale is known, it can be sent to a webapplication that will retrieve metadata values about the text. When a webapplication is coupled to a field, it can automatically retrieve those values an add them to the item. Examples are: Genre, language, wordcount, extreme values, Named entities, etc.
