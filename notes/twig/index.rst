.. toctree::
   :hidden:
   :maxdepth: 3

Twig Macro: form.twig
=====================
This project relies on the Twig macro ``form.twig`` bundled with this
package. To be able to use this class properly with that macro, it’ll be
described here.

Creator
^^^^^^^

The macro constructor looks like
``{% macro create( form, errors = {} ) %}`` . In the following macro
documentation, we’ll have a look on all important configuration options
for the macro.

.. code:: twig

   {% import "macros/form.twig" as forms %}

   {% set form = {
     "create":
     {
         "method": "get"
     },
     "rows":
     [
         {
             "name": "formelement",
             "placeholder": "text shown as placeholder",
             "type": "text",
             "title": "single line textfield"
         }
     ]
   } %}

   {% set errors = {
    'formelement': 'This error will be shown.'
   } %}

   {{ forms.create( form, errors ) }}

Rendering this Twig template will result in this HTML snippet:

.. code:: html

   <form method="GET" enctype="multipart/form-data" class="form-horizontal" >
       <div class="form-group row has-error" >
           <label for="formelement" class="col-md-3 col-form-label">single line textfield</label>
           <div class="col-md-9">
               <input id="formelement" class=" form-control" type="text" class="form-control" name="formelement" placeholder="text shown as placeholder" value="" />
               <span class="help-block errormsg">
                   <strong>This error will be shown.</strong>
               </span>
           </div>
       </div>
       <input type="submit" name="submit" value="submit" class="btn btn-primary" />
   </form>

The Macro can be initiated given an object ``form`` – which will be
described in detail below.

The optional dictionary ``errors`` is used to populate all input rows
(and so fields) with errors that occured e.g. while validating the sent
form data. It has to be a dictionary with the ``row.name`` as key and
the error message to be displayed as value.

The form object
^^^^^^^^^^^^^^^

.. code:: js

   {
     "create": {},
     "buttons": [],
     "submit": {},
     "rows": []
   }

The form object in general can consist out of three sub-objects: the
``create`` and the ``rows`` objects are mandatory, the ``submit`` /
``buttons`` object is optional.

*All given attributes within this Macro documentation are case
sensitive!*

The ``create`` sub-object
'''''''''''''''''''''''''

This is a key-value dictionary that can consist out of those keys: \*
``method`` (default ``POST`` – has to be a valid HTTP Request method) \*
``url``, ``route`` or ``action`` – those define the actual action path
for the form. \* ``enctype`` – will be overridden if ``files`` is set
``true`` by ``multipart/form-data`` \* any other Attribute to be part of
the ``form`` tag like ``class`` , ``id`` , …

The ``submit`` sub-object or the (optional) ``buttons`` sub-list
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

By default and if ``buttons`` sub-list is not defined, you’ll get a
simple ``submit`` input form element at the end of your form. That
element can be configured by the ``submit`` sub-object of the whole form
object with these attributes:

-  ``name`` – equivalent to the attribute ``name`` of ``input`` tag,
   defaults to ``submit``
-  ``value`` – equivalent to the attribute ``value`` of ``input`` tag,
   defaults to ``submit``
-  ``class`` – equivalent to the attribute ``class`` of ``input`` tag,
   defaults to ``btn btn-primary``
   (`Bootstrap <https://getbootstrap.com/docs/>`__)
-  ``attributes`` – additional attributes for the currently described
   ``button`` tag as dictionary (key is attribute name, value is
   attribute value)

As said above, if the ``buttons`` sub-list is defined, the ``submit``
sub-object is fully ignored.

That’s because of the fact, that the ``buttons`` sub-list tells the
macro per definition, that the form actions are all managed by those
buttons. So the ``buttons`` sub-list is a list of button definition
dictionaries whith those attributes:

-  ``name`` – equivalent to the attribute ``name`` of the ``button`` tag
-  ``value`` – equivalent to the attribute ``value`` of the ``button``
   tag
-  ``class`` – equivalent to the attribute ``class`` of the ``button``
   tag
-  ``text`` – equivalent to the (shown) value between opening and
   closing ``button`` tags
-  ``attributes`` – additional attributes for the currently described
   ``button`` tag as dictionary (key is attribute name, value is
   attribute value) – the ``button_attributes`` dictionary at top level
   of the ``form`` object will be overridden by this local dictionary.

For buttons, you have to define all those attributes since they’ll stay
empty otherwise.

The ``rows`` sub-list
'''''''''''''''''''''

A form created by this form Twig macro is structured into different rows
– each row holds one field of the form.

common attributes


-  ``name`` – the name of the rows input field, turned into the ``id``
   attribute and the ``name`` attribute. It’s the only mandatory
   attribute for each single row.
-  ``type`` – defines the type of current row form element. May be one
   out of ``checkbox`` , ``radio`` , ``textarea`` , ``select`` or
   ``datalist`` – every other value will cause an ``input`` tag with the
   ``type`` attribute set to given value. Value defaults to ``text`` .
-  ``value`` – if no ``option`` tags are required, this attribute may
   set the value of the form element to be sent; defaults to already
   sent value if possible to retrieve
-  ``help`` – HTML content to be shown beneath the form element to help
   the user to correctly fill the form.
-  ``title`` - content of the rows ``label`` tag; defaults to ``name``
-  ``noTitle`` – turns off the showing of a label in / for current input
   row
-  ``class`` – CSS class(es) of the form elements of the current row.
-  ``placeholder`` – sets the placeholder attribute on ``textarea`` and
   regular ``input`` tags
-  ``autofocus`` – if defined, the current element is provided the
   ``autofocus`` attribute. Don’t provide, if you don’t want to apply!
-  ``disabled`` – if defined, option attribute ``disabled`` is set.
   Don’t provide, if you don’t want to apply!
-  ``hidden`` – turns an ``input`` field (and the whole row) into a
   hidden row / input. Don’t provide, if you don’t want to apply!
-  ``readonly`` – turns the form element of current row into a read-only
   one. Don’t provide, if you don’t want to apply!
-  ``plaintext`` – if row is readonly, Bootstrap allows to view the form
   element as plain text instead of form element. This attributes allows
   you to apply that. Don’t provide, if you don’t want to apply!
-  ``required`` – activates the HTML / Browser check for required form
   elements: the element of current row has to be filled for being able
   to submit the form. Don’t provide, if you don’t want to apply!
-  ``label_attributes`` – attributes that are applied to the label of
   the row
-  ``option_attributes`` – default attributes to be added to all
   ``option`` tags belonging to the current row, if no ``attributes``
   attribute defined within ``options`` sub-object item (see below)

``textarea`` specific attributes


-  ``cols`` – columns of the textarea
-  ``rows`` – rows of the thextarea

``select`` specific attributes


-  ``multiple`` – if set, one can select multiple values from current
   row ``select`` element. Don’t provide, if you don’t want to apply!

the sub-object ``options`` with attributes for form elements with options


Those form elements are ``select`` , ``input`` of types ``checkbox`` or
``radio`` and ``input`` in addition of a ``datalist`` tag.

For the ``input`` tag in combination with the ``datalist`` tag only the
marked ``(*)`` attributes are useable since ``datalist`` children
``option`` are not (directly) visible.

-  ``attributes`` – override ``option_attributes`` . Has to be a
   key-value-dictionary, so the tag will be expanded by ``key="value"``
   . Double quotes ``"`` are replaced by ``&amp;quote;`` within values.
   ``(*)``
-  ``class`` – CSS class(es) of the option; defaults to ``class``
   attribute of current ``row`` (only ``input`` of types ``checkbox``
   and ``radio`` )
-  ``description`` – visible text fo the current opiton; defaults to
   ``value`` attribute.
-  ``disabled`` – if defined, option attribute ``disabled`` is set. So
   if you don’t want to disable an option, just don’t define that
   attribute. ``(*)``
-  ``selected`` – if set, the ``selected`` attribute for current option
   is set. Is also set, if the value is set within ``request_data`` twig
   function for current row, which reflects ``$_REQUEST`` variable.
-  ``value`` – the actual value sent with form submission. ``(*)``
