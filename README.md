# File Reader PHP

Simple PHP service for file processing.
Implemented actions: list, upload, read, write and delete CSV and JSON files.
It can be extended to work with other file formats.
Currently write replaces the whole file; new actions could be added, such as appending rows or editing a single row.

Install dependencies with `composer install`, start the server with `composer serve` and open `http://localhost:8000` to pick a file and an action.

The same actions are available as an API:

- `GET index.php?action=list`
- `GET index.php?action=read&file=fruits.csv`
- `POST index.php?action=write&file=fruits.csv` with JSON in the body
- `DELETE index.php?action=delete&file=fruits.csv`
- `POST index.php?action=upload` with the file as form data

Files are stored in the `storage` folder. Run tests with `composer test`.
