# TKMON-WEB How to create languages

TKMON-WEB is translates its frontend into different languages using GNU’s gettext framework.

## Prerequisites

Gettext is based on some tools need to be installed on your system:

```
# aptitude install gettext
```

## Init languages

To start a fresh translation based on a default catalogue invoke the following call

```
# msginit -i share/tkmon/locales/messages.pot -l es_ES
```

### Options

<dl>

<dt class="hdlist1">-i</dt>

<dd>

Input file from which new po file is created, should be share/tkmon/locales/messages.pot

</dd>

<dt class="hdlist1">-l</dt>

<dd>

Locale in which should be translated

</dd>

</dl>

After that a new po file is created in the base directory. You have to copy that to the required location:

```
# mkdir -p share/tkmon/locales/es_ES/LC_MESSAGES/
# mv es.po share/tkmon/locales/es_ES/LC_MESSAGES/messages.po
```

Now you can translate your messages into the desired language

## Compile languages

After you have created a new po file you need to compile them into a binary file.

Just invoke

```
# php bin/compile_catalogues.php
```

After that you need to restart apache2 because mo files are cached in its using processes:

```
# sudo service apache2 restart
```

## Update the base catalogue

If you change something in the code a new master catalogue have to created:

```
# php bin/create_message_catalogue.php
```

Base file _share/tkmon/locales/messages.pot_ should be updated

## Merge existing catalogues

If you change something in the base catalogue you need to merge the existing ones:

```
# php bin/merge_existing_catalogues.php
```

Start translating or compile the new catalogues.

## Configure available languages in frontend

Have a look into etc/tkmon/config.json

There are settings for the locales list:

```
"locale.list": [
    { "locale": "de_DE", "label": "German" },
    { "locale": "en_US", "label": "English" }
]
```

Change it upon your needs and existing languages. Do not forget to restart apache afterwards.

## Appendix

### List of language tools

*   [Poedit](http://www.poedit.net/)
