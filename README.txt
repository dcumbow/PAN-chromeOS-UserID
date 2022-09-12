PLEASE NOTE, GOOGLE HAS DEPRICATED THE USAGE OF APPS WITHIN CHROMEOS.  PLEASE CONSIDER ONE OF THE TWO FOLLOWING OPTIONS:
- PULL USER-ID INFORMATION FROM YOUR WLC USING 802.1X AUTH AND SYSLOG USER-ID INGESTION
- IF YOU HAVE THE GP LICENSE, UTILIZE THE GLOBALPROTECT APP FOR ANDROID RUNNING ON YOUR CHROMEBOOKS FOR ALWAYS-ON FUNCTIONALITY WITH INTERNAL GATEWAY AND USER-ID INFORMATION SHARING

###########################################################
Project:  ChromeOS UserID Plugin for PanOS


###########################################################

INTRO
The plugin consists of three factors.
    1) The extension which is installed on the Chromebook
    2) The PHP script which resides on a server listening for communication from the chromebook plugin.  This then translates the information into syslog entries.
    3) The Palo Alto Networks firewall being configured to listen for syslog messages from the PHP script server.


	
PRE-REQUISITES
For mass deployment, the plugin requires that you have a Google Apps for Work or Google Apps for Education domain, a chromebook, an available server to run a PHP script on, and a Palo Alto Networks firewall.



EXTENSION
The extension folder contains the files needed to publish the app internally for your google domains.  Search the background.min.js, manifest.json, and schema.json files for the # symbol.  Replace any variables surrounded by the # symbol with your own values.  Once your values are set.  Publish this as an internal application within your GAFE/GAFW domain.

Some guides for deploying apps:
Official Guide:
https://support.google.com/chrome/a/answer/6306504
https://support.google.com/chrome/a/answer/6177431

Video from Gooru (old)
https://www.youtube.com/watch?v=A2ByMty7kL0



PHP
The php folder contains the files needed for your PHP server.  Both files will be needed within an accessible server from inside your network.  We do not recommend having this script accessible from the outside at this time.  Search the index.php script for the # symbol.  Replace any variables surounded by the $ symbol with your own values.



PALO ALTO NETWORKS FIREWALL
First you will need to setup a custom Syslog Filter under the Device > User Identification > User Mapping section.  Click the gear in the top right corner to edit the settings, then click on Syslog Filter.  Click Add at the bottom to create a new filter.  Give it the following information:

    Syslog Parse Profile: Enter a name you'd like (e.g. ChromeAuth)
	Type: Field Identifier
	Event String: UserIPMap;
	Username Prefix: User=
	Username Delimiter: ;
	Address Prefix: IP=
	Address Delimiter: ;
	
Save this information and head to the User Mapping tab.  Add an entry under the Server Monitoring section.  Give it the following information:

    Name: Enter a name you'd like (e.g. panauth)
	Type: Syslog Sender
	Network Address: address of the PHP server above
	Connection Type: UDP
	Filter: The name of the syslog parse profile youc reated above
	Default Domain: Your default AD domain (e.g. mvusd for Moreno Valley USD)
	
Make sure to commit your changes
