# Events Calendar System

## Overview
The Events Calendar is a fully responsive, interactive scheduler integrated into the Visitor System. It allows admins to create, view, update, and delete events.

## Features
- **Responsive Layout**: Adapts from a 2-column sidebar layout on desktop to a stacked layout on mobile.
- **Interactive Grid**: Click days to filter events; visual "dots" indicate event density.
- **CRUD Operations**: Complete management of events (Title, Date/Time, Location, Visibility).
- **Legacy Support**: Maintains backward compatibility with previous event table schemas.

## Technical Details

### Database
The `events` table has been extended with the following columns:
- `title` (VARCHAR): Main event title.
- `start_datetime` (DATETIME): Event start.
- `end_datetime` (DATETIME): Event end.
- `location` (VARCHAR): Venue or location.
- `visibility` (ENUM): 'public' or 'internal'.
- `created_by` (INT): User ID of creator.

*Note: Legacy columns (`event_title`, `event_date`, `time_slot`, `venue`) are preserved in the schema but mapped functionally to the new columns.*

### API Endpoints
- **GET** `get_events.php?start=YYYY-MM-DD&end=YYYY-MM-DD`
    - Returns JSON array of events in range.
- **POST** `create_event.php`
    - Requires: `title`, `start_datetime`, `end_datetime`, `csrf_token`.
- **POST** `update_event.php`
    - Requires: `id`, `start_datetime`, ...
- **POST** `delete_event.php`
    - Requires: `id`.

### Frontend
- **`events_calendar.php`**: Main entry point.
- **`events_calendar.css`**: Scoped styles using CSS Grid and Flexbox.
- **`events_calendar.js`**: Handles state, API calls, calendar rendering, and modals.

## Usage
1.  **Navigate**: Use "Prev/Next" buttons or click "Today".
2.  **View**: Click a day cell to see events in the sidebar.
3.  **Add**: Click the "+ Add Event" button (top right).
4.  **Edit**: Click an event in the sidebar list.

## Troubleshooting
- **Migration**: If column errors appear, run `http://localhost/visitor-system/db/migrate_events.php`.
- **Permissions**: Only Super Admins, Directors, and Destination Admins can create/edit events.
