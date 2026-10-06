# File Reader PHP

Simple PHP service for file processing.

Implemented actions: list, upload, read, write and delete CSV and JSON files.
It can be extended to work with other file formats.
Currently write replaces the whole file; new actions could be added, such as appending rows or editing a single row.

Requires PHP 8.4+. Install dependencies with `composer install`, start the server with `composer serve` and open `http://localhost:8000` to pick a file and an action.

The page calls the backend as a JSON API: `index.php?action=<action>&file=<file_name>` with GET for list and read, POST for write and upload, DELETE for delete.

Files are stored in the `storage` folder. Run tests with `composer test`.
