# Quick Reference - Project CRUD API

## Quick Start

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Start Queue Worker (for async project closure)
```bash
php artisan queue:work
```

## API Endpoints

All endpoints require `Authorization: Bearer YOUR_JWT_TOKEN` header.

### List Projects
```http
GET /api/projects?per_page=10&name=search_term
```

### Get Single Project (with Requests)
```http
GET /api/projects/{id}
```

### Create Project
```http
POST /api/projects
Content-Type: application/json

{
  "title": "Project Name",
  "description": "Optional description",
  "filename": "optional-file.pdf"
}
```

### Update Project
```http
PUT /api/projects/{id}
Content-Type: application/json

{
  "title": "Updated Name",
  "description": "Updated description",
  "filename": "updated-file.pdf"
}
```

### Delete Project
```http
DELETE /api/projects/{id}
```

### Search Projects
```http
POST /api/projects-search
Content-Type: application/json

{
  "term": "search keyword"
}
```

### Toggle Project Active Status
```http
GET /api/projects/{id}/state/{state}
# state: 1 (active) or 0 (inactive)
```

### Add Requests to Project
```http
POST /api/projects/{id}/add-requests
Content-Type: application/json

{
  "request_ids": [1, 2, 3, 4, 5]
}
```

**Note:** This adds requests while preserving existing associations.

### Close Project & All Requests
```http
POST /api/projects/{id}/close
```

**Response:**
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

**What happens:**
1. Immediately confirms project closure initiated
2. Dispatches background job to close all requests
3. Job closes each request individually
4. Updates project status to 'closed'
5. Sets `closed_at` timestamp on each request

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    "id": 1,
    "title": "Project Name",
    "description": "Description",
    "filename": "file.pdf",
    "status": "open",
    "created_at": "2026-01-15T10:30:00Z",
    "updated_at": "2026-01-15T10:30:00Z",
    "requests": []
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "data": null
}
```

## Project Statuses

- `open` - Project is active and accepting requests
- `closed` - Project is closed and all requests are closed

## Field Validation

| Field | Type | Required | Max Length | Notes |
|-------|------|----------|-----------|-------|
| title | string | Yes | 255 | Cannot be empty |
| description | string | No | 1000 | Optional |
| filename | string | No | 255 | Optional |
| request_ids | array | Yes (on add) | - | Valid request IDs only |

## Error Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad request (e.g., project already closed) |
| 404 | Resource not found |
| 422 | Validation error |
| 500 | Server error |

## Database Tables

### projects
- id, title, description, filename, status, created_at, updated_at, deleted_at

### project_requete (many-to-many)
- id, project_id, requete_id, created_at, updated_at

### requetes (updated)
- Added: closed_at field

## Files Created

1. **Models:**
   - `app/Models/Project.php`

2. **Controllers:**
   - `app/Http/Controllers/ProjectController.php`

3. **Repositories:**
   - `app/Http/Repositories/ProjectRepository.php`

4. **Form Requests:**
   - `app/Http/Requests/Project/StoreProjectRequest.php`
   - `app/Http/Requests/Project/UpdateProjectRequest.php`
   - `app/Http/Requests/Project/AddRequestsToProjectRequest.php`

5. **Jobs:**
   - `app/Jobs/CloseProjectRequests.php`

6. **Migrations:**
   - `database/migrations/2026_01_15_create_projects_table.php`
   - `database/migrations/2026_01_15_add_closed_at_to_requetes_table.php`

7. **Documentation:**
   - `PROJECT_CRUD_DOCUMENTATION.md`
   - `PROJECT_CRUD_SUMMARY.md`
   - `QUICK_REFERENCE.md` (this file)

## Example cURL Commands

### Create Project
```bash
curl -X POST http://localhost/api/projects \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Project",
    "description": "Description here",
    "filename": "file.pdf"
  }'
```

### Get Project with Requests
```bash
curl -X GET http://localhost/api/projects/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Add Requests to Project
```bash
curl -X POST http://localhost/api/projects/1/add-requests \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "request_ids": [1, 2, 3]
  }'
```

### Close Project
```bash
curl -X POST http://localhost/api/projects/1/close \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Logging

All operations are logged:
- Project creation
- Project updates
- Project deletion
- Request additions to projects
- Project closure initiation

Access logs via: `GET /api/logs`

## Relationships

### Project → Requests (Many-to-Many)
```php
$project->requests(); // Get all requests in project
$request->projects(); // Get all projects containing request
```

## Notes

- Projects use soft deletes (don't remove data permanently)
- Request closure happens in background (async queue job)
- Each request is closed individually when project closes
- All timestamps are UTC
- JWT authentication required for all endpoints
