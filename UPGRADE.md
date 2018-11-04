Apexx aktualisieren
-------------------

**Vorbereitung**
- Backup der Seite im aktuellen Zustand, d.h. Dateisystem und Datenbank
- Laden Sie die [aktuelle Version](https://github.com/scheb/open-apexx/releases) als ZIP herunter
- In dem ZIP befindet sich ein Ordner "src", dies ist die klassische Komplettinstallation von apexx. Im Folgenden werden
  nur Dateien aus diesem Ordner benötigt.

**Durchführung**

- .htaccess Datei im Hauptordner überschreiben und ggfs. eigene Änderungen übernehmen
- Alle PHP-Dateien im Hauptordner überschreiben
- Folgende Ordner werden überschreiben:
  - admin
  - forum (falls verwendet, die Ordner mit den Grafiken ggfs. nicht überschreiben)
  - language (nicht zwingend erforderlich, insbesondere wenn Änderungen an den Texten gemacht wurden evtl. überspringen oder mit einem Diffing-Tool wie WinMerge die Änderungen zusammenführen)
  - lib (außer der Config-Datei, ggfs. aus dem Backup übernehmen)
  - modules
- Stelle sicher, dass die Config-Datei folgende Zeile enthält: `$set['mysql_api'] = 'mysqli';`
- Die Seite sollte nun wieder in einem lauffähigen Zustand sein.
- Im Adminbereich anmelden und die Updates der Module ausführen.

**Bekannte Probleme**

Wenn die Seite PHP-Fehler vom Typ "Notice" oder "Warning" anzeigt, in die Config einfügen:
```php
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED ^ E_USER_DEPRECATED);
```

Wenn die Sonderzeichen nicht richtig angezeigt werden, in die Config einfügen:
```php
ini_set("default_charset", "ISO-8859-1");
```
