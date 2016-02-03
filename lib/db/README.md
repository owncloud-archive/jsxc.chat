lib/stanzahandlers
===

This are the Entity and Mappers of the different stanza types. These are used to:
 - store them in the DB. (e.g. the message entity)
 - to easily return them to the client via the polling system (e.g. IQRoster)
 - the Presence Entity is also used to parse incoming data to an object.