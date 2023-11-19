# json-file-viewer
A simple app to display text file links from Google Drive containing JSON data in a machine readable format.

## Getting Started

### Using a Google Drive File View URL

Add the following pre-text to your google links: `http://localhost/?url=`

Example: http://localhost/?url=https://drive.google.com/file/d/{FILE_ID}/view

This is the link you will send customers.

### Using a Google Drive File ID

For brevity, you may want to use only the file ID. It is the last part of the URL,
after the `/d/` and before the `/view` or `/edit` parts.

Add the following pre-text to your google links: `http://localhost/?id=`

Example: http://localhost/?id={FILE_ID}

This is the link you will send customers.

## Configuration

The config.php file contains environment settings you may wish to change.  

#### VIEW_DEBUG
This constant is set to false by default. This will hide the debug information
from the view. Setting it to true will show all notices, warnings, and errors.

#### ROOT_PATH
This constant is set to the root of the project. By default, it is set to the 
default Apache and Httpd root directory. If you are using a different web server,
you may need to adapt this to your environment.

## Notes:

- Web Server is currently configured for Apache.
- Web Server requires write permissions for everything in the `storage/` folder for the apache logs.
