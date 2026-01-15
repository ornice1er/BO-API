# Project CRUD System Documentation

## Overview

This document describes the complete Project CRUD (Create, Read, Update, Delete) system implemented in the BO-API application. The system allows you to manage projects and associate them with requests (Requête).

## Components Created

### 1. Model: `Project`
**Location:** `app/Models/Project.php`

The Project model represents a project entity with the following properties:
- `id` - Primary key
- `title` - Project title (required)
- `description` - Project description (nullable)
- `filename` - Associated filename (nullable)
- `status` - Project status (open/closed)
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp
- `deleted_at` - Soft delete timestamp

#### Relationships
- `requests()` - Many-to-many relationship with Requete model through `project_requete` pivot table

#### Methods
- `isClosed()` - Check if project is closed
- `scopeByStatus($query, $status)` - Query scope to filter by status

### 2. Database Tables

#### `projects` Table
```sql
- id (Primary Key)
- title (String, 255 chars)
- description (Text, nullable)
- filename (String, 255 chars, nullable)
- status (Enum: open, closed) - Default: open
- created_at (Timestamp)
- updated_at (Timestamp)
- deleted_at (Timestamp, nullable - for soft deletes)
```

#### `project_requete` Pivot Table
```sql
- id (Primary Key)
- project_id (Foreign Key → projects.id)
- requete_id (Foreign Key → requetes.id)
- created_at (Timestamp)
- updated_at (Timestamp)
- UNIQUE: (project_id, requete_id)
```

#### `requetes` Table (Updated)
```sql
- Added: closed_at (Timestamp, nullable) - When request was closed
```

### 3. Request Validation Classes

#### `StoreProjectRequest`
**Location:** `app/Http/Requests/Project/StoreProjectRequest.php`

Validates input for creating a new project:
```php
- title: required|string|max:255
- description: nullable|string|max:1000
- filename: nullable|string|max:255
```

#### `UpdateProjectRequest`
**Location:** `app/Http/Requests/Project/UpdateProjectRequest.php`

Validates input for updating an existing project:
```php
- title: sometimes|required|string|max:255
- description: nullable|string|max:1000
- filename: nullable|string|max:255
```

#### `AddRequestsToProjectRequest`
**Location:** `app/Http/Requests/Project/AddRequestsToProjectRequest.php`

Validates input for adding requests to a project:
```php
- request_ids: required|array
- request_ids.*: required|integer|exists:requetes,id
```

### 4. Repository: `ProjectRepository`
**Location:** `app/Http/Repositories/ProjectRepository.php`

Handles all database operations for projects:

#### Methods
- `getAll($request)` - Get all projects with filtering and pagination
- `get($id)` - Get a single project by ID
- `makeStore($data)` - Create a new project
- `makeUpdate($id, $data)` - Update a project
- `makeDestroy($id)` - Delete a project
- `getWithRequests($id)` - Get project with associated requests
- `addRequests($projectId, $requestIds)` - Add/update request associations
- `close($id)` - Mark project as closed
- `search($term)` - Search projects by title or description

### 5. Job: `CloseProjectRequests`
**Location:** `app/Jobs/CloseProjectRequests.php`

Background job that closes all requests associated with a project:
- Implements `ShouldQueue` for asynchronous processing
- Iterates through all project requests
- Updates each request with `status = 'closed'` and `closed_at = now()`
- Logs all operations
- Handles errors gracefully

### 6. Controller: `ProjectController`
**Location:** `app/Http/Controllers/ProjectController.php`

RESTful API controller with complete CRUD operations and custom actions.

#### Endpoints

##### List Projects
```
GET /api/projects
Query Parameters:
  - per_page: number (default: 10)
  - name: string (filter by name)
```
Returns paginated list of projects.

##### Get Single Project with Requests
```
GET /api/projects/{id}
```
Returns project details including all associated requests.

##### Create Project
```
POST /api/projects
Body:
{
  "title": "Project Title",
  "description": "Optional description",
  "filename": "optional-file.pdf"
}
```
Returns created project object.

##### Update Project
```
PUT /api/projects/{id}
Body:
{
  "title": "Updated Title",
  "description": "Updated description",
  "filename": "updated-file.pdf"
}
```
Returns updated project object.

##### Delete Project
```
DELETE /api/projects/{id}
```
Soft deletes the project.

##### Search Projects
```
POST /api/projects-search
Body:
{
  "term": "search term"
}
```
Returns projects matching the search term.

##### Change Project Status
```
GET /api/projects/{id}/state/{state}
```
Changes project active/inactive status.

##### Add Requests to Project
```
POST /api/projects/{id}/add-requests
Body:
{
  "request_ids": [1, 2, 3, 4]
}
```
Adds multiple requests to a project. Uses `syncWithoutDetaching` to preserve existing associations.

Returns project with updated requests list.

##### Close Project
```
POST /api/projects/{id}/close
```
Initiates project closure:
1. Verifies project exists
2. Checks if already closed
3. Dispatches `CloseProjectRequests` job to background queue
4. Returns success message with project ID

The job will:
- Close all associated requests
- Set `closed_at` timestamp on each request
- Update project status to 'closed'
- Log all operations

## API Routes

All routes are protected with JWT authentication and registered in `routes/api.php`:

```php
Route::apiResources([
    'projects' => 'ProjectController',
]);

Route::get('projects/{id}/state/{state}', 'ProjectController@changeState');
Route::post('projects-search', 'ProjectController@search');
Route::post('projects/{id}/add-requests', 'ProjectController@addRequests');
Route::post('projects/{id}/close', 'ProjectController@closeProject');
```

## Usage Examples

### Create a Project
```bash
POST /api/projects
Content-Type: application/json

{
  "title": "Marketing Campaign 2026",
  "description": "Annual marketing campaign planning",
  "filename": "campaign-plan.pdf"
}
```

### Add Requests to Project
```bash
POST /api/projects/1/add-requests
Content-Type: application/json

{
  "request_ids": [10, 15, 22, 33]
}
```

### Close Project (Async)
```bash
POST /api/projects/1/close
```

The response will be:
```json
{
  "success": true,
  "message": "Clôture du projet initiée. Les requêtes seront clôturées en arrière-plan",
  "data": {
    "project_id": 1,
    "status": "closing"
  }
}
```

### Get Project with Requests
```bash
GET /api/projects/1
```

Response:
```json
{
  "success": true,
  "message": "Projet trouvé",
  "data": {
    "id": 1,
    "title": "Marketing Campaign 2026",
    "description": "Annual marketing campaign planning",
    "filename": "campaign-plan.pdf",
    "status": "open",
    "created_at": "2026-01-15T10:30:00Z",
    "updated_at": "2026-01-15T10:30:00Z",
    "requests": [
      {
        "id": 10,
        "title": "Request Title",
        ...
      },
      ...
    ]
  }
}
```

## Database Migration

Run migrations to create the tables:
```bash
php artisan migrate
```

Migrations created:
1. `2026_01_15_create_projects_table.php` - Creates projects and project_requete tables
2. `2026_01_15_add_closed_at_to_requetes_table.php` - Adds closed_at column to requetes table

## Error Handling

All endpoints follow the application's error handling pattern using `Common::error()` and `Common::success()` utility methods.

Common error codes:
- `400` - Bad request (e.g., project already closed)
- `404` - Resource not found
- `422` - Validation error
- `500` - Server error

## Logging

All operations are logged via the `LogService`:
- Project creation
- Project updates
- Project deletion
- Request additions
- Project closure initiation

## Queue Configuration

The `CloseProjectRequests` job runs asynchronously. Ensure your queue worker is running:

```bash
php artisan queue:work
```

Or use a process manager like Supervisor for production.

## Related Models

### Requete Model
The Requete model has been updated to include a relationship back to projects:

```php
public function projects()
{
    return $this->belongsToMany(Project::class, 'project_requete', 'requete_id', 'project_id')->withTimestamps();
}
```

## Notes

- Projects use soft deletes for data integrity
- The `project_requete` pivot table enforces unique combinations
- Request closure is handled asynchronously for better performance
- All operations are logged for audit purposes
- Full OpenAPI/Swagger documentation is included in controller annotations
