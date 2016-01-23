lib/stanzahandlers
===

This are the Entity and Mappers of the different stanza types. These are used to:
 - store them in the DB. (e.g. the message entity)
 - to easily return them to the client via the polling system (e.g. IQRoster)
 
 
They are not used for parsing the input stanzas. (This is handled via the array system of sabre-xml)