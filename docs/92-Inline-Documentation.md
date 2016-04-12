
# TKMON-WEB JSON service catalogue how-to

TKMON-WEB uses inline help to provide context sensitive help to users

## Under the hood

Documentation resides in a subdirectory of the templates

```
"doc.basepath":         "{core.share_dir}/templates/doc",
```

If a page is rendered the path will be substituted explained by the following example:

```
# Page is
http://192.168.56.101/tkmon/Index/Index

# Identifier is
Index/Index

# Document (If current locale is de_DE)
{core.share_dir}/templates/doc/Index/Index/de/inline.twig
```

If no specific locale part is found, "en" is used therefore.

```
# Document (If current locale is de_DE and no inline doc is present)
{core.share_dir}/templates/doc/Index/Index/en/inline.twig
```

## File format

The files must be named "inline.twig" and support [Twig Template Syntax](http://twig.sensiolabs.org/).

## Examples

Have a look in ```share/tkmon/templates/doc`` for basic usage.
