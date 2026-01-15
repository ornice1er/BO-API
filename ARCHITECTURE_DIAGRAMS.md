# Project CRUD System - Architecture & Flow Diagrams

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        API Client (Frontend)                    │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 │ HTTP Requests (with JWT Auth)
                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                      API Routes Layer                            │
│  GET/POST/PUT/DELETE /api/projects                             │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                  ProjectController                              │
│  ├── index()           - List all projects                     │
│  ├── show()            - Get with requests                     │
│  ├── store()           - Create project                        │
│  ├── update()          - Update project                        │
│  ├── destroy()         - Delete project                        │
│  ├── search()          - Search projects                       │
│  ├── changeState()     - Toggle active status                 │
│  ├── addRequests()     - Add requests to project              │
│  └── closeProject()    - Initiate closure                      │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────┐
│              Request Validation Layer                            │
│  ├── StoreProjectRequest                                       │
│  ├── UpdateProjectRequest                                      │
│  └── AddRequestsToProjectRequest                               │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────┐
│              ProjectRepository                                   │
│  ├── getAll()           - Query with filters                  │
│  ├── get()              - Find by ID                          │
│  ├── makeStore()        - Create                              │
│  ├── makeUpdate()       - Update                              │
│  ├── makeDestroy()      - Delete                              │
│  ├── getWithRequests()  - Load with relations                 │
│  ├── addRequests()      - Sync requests                       │
│  ├── close()            - Mark closed                         │
│  └── search()           - Search by term                      │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────┐
│              Database Layer                                      │
│  ├── projects table         (Main project data)               │
│  ├── project_requete table  (Many-to-many junction)           │
│  └── requetes table         (Request data + closed_at)        │
└─────────────────────────────────────────────────────────────────┘
```

## Request Lifecycle - Create Project

```
                    ┌─────────────────┐
                    │  POST /projects │
                    └────────┬────────┘
                             │
                             ▼
                    ┌─────────────────────────────────┐
                    │ StoreProjectRequest Validation  │
                    │ ├─ title: required              │
                    │ ├─ description: nullable        │
                    │ └─ filename: nullable           │
                    └────────┬────────────────────────┘
                             │
                    ┌────────▼────────┐
                    │   Validation    │
                    │    Passes?      │
                    └────┬────────┬───┘
                         │        │
                    No   │        │   Yes
                         ▼        ▼
                    [Error 422] ┌──────────────────┐
                                │ ProjectController│
                                │    store()       │
                                └────────┬─────────┘
                                         │
                                         ▼
                                ┌─────────────────────┐
                                │ProjectRepository    │
                                │  makeStore($data)   │
                                └────────┬────────────┘
                                         │
                                         ▼
                                ┌─────────────────────┐
                                │  Project Model      │
                                │   ::create()        │
                                └────────┬────────────┘
                                         │
                                         ▼
                                ┌─────────────────────┐
                                │  INSERT projects    │
                                │  Database           │
                                └────────┬────────────┘
                                         │
                                         ▼
                                ┌─────────────────────┐
                                │ LogService::trace() │
                                │ (Log operation)     │
                                └────────┬────────────┘
                                         │
                                         ▼
                                ┌─────────────────────┐
                                │ Return Success      │
                                │ Response [201]      │
                                └─────────────────────┘
```

## Request Closure Flow - Close Project (Async)

```
                  ┌──────────────────┐
                  │ POST /projects/1 │
                  │     /close       │
                  └────────┬─────────┘
                           │
                           ▼
                  ┌─────────────────────────┐
                  │ ProjectController       │
                  │  closeProject($id)      │
                  └────────┬────────────────┘
                           │
                           ▼
                  ┌──────────────────────────┐
                  │ Check if already closed? │
                  └────┬────────────┬────────┘
                       │            │
                     Yes            No
                       │            │
                       ▼            ▼
                   [400 Error]  ┌──────────────────────────┐
                                │ Dispatch CloseProject    │
                                │ RequestsJob to queue     │
                                │ queue->push(job)         │
                                └────────┬─────────────────┘
                                         │
                                         ▼
                                ┌──────────────────────┐
                                │ Return Immediate     │
                                │ Success Response     │
                                │ status: 'closing'    │
                                └──────────────────────┘
                                         │
                    ┌────────────────────┘
                    │
                    │ (User gets response immediately)
                    │ (Job processes in background)
                    │
                    ▼
        ┌────────────────────────────────────┐
        │  Queue Worker Picks Up Job         │
        │  CloseProjectRequests::handle()    │
        └────────┬──────────────────────────┘
                 │
                 ▼
        ┌──────────────────────────────────┐
        │ Get Project by ID                │
        │ Project::findOrFail($projectId)  │
        └────────┬───────────────────────┘
                 │
                 ▼
        ┌──────────────────────────────────┐
        │ Load Associated Requests         │
        │ $project->requests()->get()      │
        └────────┬───────────────────────┘
                 │
                 ▼
        ┌──────────────────────────────────────────┐
        │ For Each Request:                        │
        │   ├─ Set status = 'closed'               │
        │   ├─ Set closed_at = now()               │
        │   ├─ Update database                     │
        │   ├─ Log operation                       │
        │   └─ Continue to next...                 │
        └────────┬─────────────────────────────────┘
                 │
                 ▼
        ┌──────────────────────────────────┐
        │ Update Project Status            │
        │ Set status = 'closed'            │
        └────────┬───────────────────────┘
                 │
                 ▼
        ┌──────────────────────────────────┐
        │ Log Project Closure Complete     │
        │ LogService::log()                │
        └──────────────────────────────────┘
```

## Database Schema Relationships

```
┌──────────────────────────────────────────────────────────┐
│                      PROJECTS TABLE                      │
├──────────────────────────────────────────────────────────┤
│ PK  id           INTEGER                                 │
│     title        VARCHAR(255)          [REQUIRED]        │
│     description  TEXT                  [NULLABLE]        │
│     filename     VARCHAR(255)          [NULLABLE]        │
│     status       ENUM(open, closed)    [DEFAULT: open]   │
│     created_at   TIMESTAMP             [AUTO]            │
│     updated_at   TIMESTAMP             [AUTO]            │
│     deleted_at   TIMESTAMP             [SOFT DELETE]     │
└──────────────────────────────────────────────────────────┘
          │
          │ Many-to-Many
          │ (through project_requete)
          │
          ▼
┌──────────────────────────────────────────────────────────┐
│               PROJECT_REQUETE TABLE (PIVOT)              │
├──────────────────────────────────────────────────────────┤
│ PK  id           INTEGER                                 │
│ FK  project_id   INTEGER   → projects.id                │
│ FK  requete_id   INTEGER   → requetes.id                │
│     created_at   TIMESTAMP                              │
│     updated_at   TIMESTAMP                              │
│     UNIQUE: (project_id, requete_id)                    │
└──────────────────────────────────────────────────────────┘
          │
          │
          ▼
┌──────────────────────────────────────────────────────────┐
│                     REQUETES TABLE                       │
├──────────────────────────────────────────────────────────┤
│ PK  id           INTEGER                                 │
│     ... existing fields ...                             │
│     status       VARCHAR (existing)                     │
│     closed_at    TIMESTAMP             [NEW - NULLABLE] │
│     created_at   TIMESTAMP                              │
│     updated_at   TIMESTAMP                              │
│     deleted_at   TIMESTAMP             [SOFT DELETE]    │
└──────────────────────────────────────────────────────────┘
```

## Class Hierarchy & Dependencies

```
ProjectController (extends Controller)
  │
  ├─ depends on ─→ ProjectRepository
  │               ├─ uses ─→ Project (Model)
  │               │         ├─ has relationship ─→ Requete
  │               │         └─ uses ─→ Filterable (Trait)
  │               │
  │               └─ Repository Trait
  │
  ├─ depends on ─→ StoreProjectRequest (extends FormRequest)
  ├─ depends on ─→ UpdateProjectRequest (extends FormRequest)
  ├─ depends on ─→ AddRequestsToProjectRequest (extends FormRequest)
  │
  ├─ dispatches ─→ CloseProjectRequests (implements ShouldQueue)
  │               ├─ uses ─→ Project (Model)
  │               ├─ uses ─→ Requete (Model)
  │               └─ implements ─→ Dispatchable, Queueable
  │
  └─ uses ─→ LogService
            └─ logs all operations
```

## Data Flow: Add Requests to Project

```
        ┌──────────────────────┐
        │ POST /projects/1/    │
        │   add-requests       │
        └──────────┬───────────┘
                   │
                   ▼
    ┌──────────────────────────────────────┐
    │ AddRequestsToProjectRequest Validation│
    │ ├─ request_ids: required|array        │
    │ └─ request_ids.*: exists:requetes,id │
    └──────────┬─────────────────────────┘
               │
               ▼
    ┌─────────────────────────────────────┐
    │ ProjectController::addRequests()    │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │ ProjectRepository::addRequests()     │
    │ ├─ Find project by ID                │
    │ └─ syncWithoutDetaching(requestIds) │
    │   (Preserve existing associations)   │
    └──────────┬──────────────────────────┘
               │
               ▼
    ┌────────────────────────────────────────┐
    │ Project::requests()->syncWithoutDetaching│
    │                                         │
    │ For each request_id:                   │
    │   ├─ Check if already exists           │
    │   ├─ If not exists: INSERT into        │
    │   │  project_requete table             │
    │   └─ If exists: KEEP (don't remove)   │
    └──────────┬───────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │ Load updated project with requests   │
    │ with('requests')                     │
    └──────────┬──────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │ LogService::trace() - Log operation  │
    └──────────┬──────────────────────────┘
               │
               ▼
    ┌──────────────────────────────────────┐
    │ Return Success Response [200]        │
    │ with project + requests data         │
    └──────────────────────────────────────┘
```

## Status Transitions

```
Project Lifecycle:
        ┌────────┐
        │  OPEN  │  ← Default status
        └────────┘
            │
            │ closeProject() called
            │ CloseProjectRequests job dispatched
            │
            ▼
        ┌────────┐
        │ CLOSED │  ← All requests closed
        └────────┘
        (Cannot reopen)
```

## Error Handling Flow

```
        API Request
            │
            ├─ Validation Failed?
            │   └─ [422] Return Validation Errors
            │
            ├─ Authentication Failed?
            │   └─ [401] Unauthorized
            │
            ├─ Resource Not Found?
            │   └─ [404] Not Found
            │
            ├─ Already Closed?
            │   └─ [400] Bad Request
            │
            ├─ Database Error?
            │   └─ [500] Server Error
            │
            └─ Success
                └─ [200/201] Return Data
```

## Summary

- **Models:** Project, Requete (updated)
- **Controllers:** ProjectController
- **Repositories:** ProjectRepository
- **Jobs:** CloseProjectRequests (async queue job)
- **Requests:** StoreProjectRequest, UpdateProjectRequest, AddRequestsToProjectRequest
- **Relationships:** Project ←→ Requete (Many-to-Many)
- **Features:** CRUD, Search, Async Closure, Logging
- **Authentication:** JWT Required
- **Database:** 2 new tables (projects, project_requete), 1 updated table (requetes)
