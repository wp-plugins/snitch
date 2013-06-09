=== Snitch ===
Contributors: sergej.mueller
Tags: sniffer, snitch, network, monitoring, firewall
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5RDDW9FEHGLG6
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html



Network monitor for WordPress. Connecting overview for monitoring and controlling of outbound blog traffic.



== Description ==

Netzwerkmonitor für WordPress. Mit Verbindungsübersicht zur Überwachung und Steuerung des Datenverkehrs im Blog.

= Vertrauen ist gut, Kontrolle ist besser =

*Snitch* - vom Englischen übersetzt *Petze*, *Spitzel*, *Plaudertasche* - überwacht und protokolliert den ausgehenden Datenstrom im Blog. Jede Verbindung aus WordPress heraus wird aufgezeichnet und Administratoren in tabellarischer Form zur Verfügung gestellt.

Verbindungsanfragen werden von *Snitch* nicht nur mitgeschrieben, auch können zukünftige Versuche blockiert werden: Wahlweise abhängig von der Ziel-URL (Internet-Adresse, die im Hintergrund aufgerufen wird) oder aber vom ausgeführten Skript (Datei, die die Verbindung angefordert hat). Blockierte Verbindungen hebt das WordPress-Plugin visuell ab. Bereits gesperrte Einträge können wieder freigegeben werden.

*Snitch* ist also perfektes Werkzeug fürs „Mithören“ der Kommunikation nach „Außen“. Auch geeignet für die Früherkennung von installierter Malware und Tracking-Software.


= Zusammenfassung =
*Snitch* führt ein Logbuch mit allen autorisierten und blockierten Konnektivitätsversuchen. Die Übersicht verschafft Transparenz und Kontrolle über ausgehende Verbindungen, die von Plugins, Themes und WordPress ausgelöst wurden. Weitere Details und Antworten auf Häufige Fragen im [Snitch Handbuch](http://playground.ebiene.de/snitch-wordpress-netzwerkmonitor/).

= Pluspunkte =
* Übersichtliche Oberfläche
* Anzeige der Ziel-URL und Ursprungsdatei
* Gruppierung, Sortierung und Durchsuchen
* Optische Hervorhebung geblockter Anfragen
* Blockieren/Freigabe der Verbindungen nach Domain/Datei
* Überwachung der Kommunikation im Backend und Frontend
* Löschung aller Einträge per Knopfdruck
* Kosten- und werbefrei


= Support =
Freundlich formulierte Fragen rund um das Plugin werden per E-Mail beantwortet.


= Systemvoraussetzungen =
* WordPress ab 3.5


= Unterstützung =
* Per [Flattr](https://flattr.com/donation/give/to/sergej.mueller)
* Per [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5RDDW9FEHGLG6)


= Handbuch =
* [Snitch: Netzwerkmonitor für WordPress](http://playground.ebiene.de/snitch-wordpress-netzwerkmonitor/)


= Autor =
* [Google+](https://plus.google.com/110569673423509816572 "Google+")
* [Plugins](http://wpcoder.de "Plugins")




== Changelog ==

= 1.0.7 =
* Entfernung des überflüssigen "Neu"-Links in der Toolbar
* Unterbindung direkter Dateiaufrufe

= 1.0.6 =
* Umbenennung/Umstellung der Funktion *delete_items* auf public

= 1.0.5 =
* Aufbewahrung von maximal 200 Snitch-Einträgen

= 1.0.4 =
* Neu: Durchsuchen der Ziel-URLs möglich

= 1.0.3 =
* Neu: Schaltfläche *Protokoll leeren*
* Entfernt: Verzicht auf den Papierkorb

= 1.0.2 =
* Umbenennung der Custom Field Keys zwecks Konfliktvermeidung

= 1.0.1 =
* Fix für *Call to undefined function get_current_screen*

= 1.0.0 =
* Snitch goes online




== Screenshots ==

1. Snitch Verbindungsliste mit Ziel-URL und Aktionen

2. Snitch Verbindungsliste mit weiteren Informationen