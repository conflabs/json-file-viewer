# json-file-viewer
A simple app to display text file links from Google Drive containing JSON data in a machine readable format.

## Getting Started

Add the following pre-text to your google links: `http://localhost/?url=`

Example: http://localhost/?url=https://drive.google.com/file/d/{FILE_ID}/view

This is the link you will send customers.

## Notes:

- Web Server is currently configured for Apache.
- Web Server requires write permissions for everything in the `storage/` folder for the apache logs.
