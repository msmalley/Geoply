This folder contains a copy of the GeoNames database as a MongoDB Collection

1) Extract contents of /db/geonames.zip
2) Stop mongoDB server
3) Add geo.0 - geo.ns files to data/db
4) Restart mongoDB server
5) Visit root index

If markers do not display, see if any $near commands work from console
If $near commands not working, will be asked to repair db - do so then re-try