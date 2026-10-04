# AI Usage Log

## 2026-10-04

### Task
Designed the `document_chunks` migration for RAG ingestion.

### AI Assistance
AI suggested:
- Table structure.
- pgvector column.
- Metadata field.
- Unique constraint.

### My Work
- Reviewed the schema.
- Adjusted naming to match the existing project.
- Verified PostgreSQL and pgvector compatibility.
- Ran and tested the migration locally.

### AI Issues / Corrections
- The suggested embedding dimension was treated as provisional because the final embedding provider/model has not been selected yet.
- The current dimension is `1536` and will be reviewed once the final provider/model is chosen.

### Verification
- Migration executed successfully.
- `document_chunks` table was verified directly in PostgreSQL using DBeaver.
- Foreign key relationship with `candidate_documents` was confirmed.
- pgvector `embedding` column was confirmed in the generated table.

*******************************************************************************
### Additional Task
Implemented the candidate document upload flow.

### AI Assistance
AI assisted with:
- Separating request validation into a Form Request.
- Moving upload logic from the controller into a service.
- Defining the upload endpoint structure.
- Adding model relationships between candidates, documents, and chunks.
- Debugging API errors during testing.

### My Work
- Created the `StoreCandidateDocumentRequest`.
- Created the `CandidateDocumentService`.
- Updated the controller to delegate upload logic to the service.
- Added the API route for candidate document upload.
- Added the required Eloquent relationships.
- Added `$fillable` fields required for model creation.
- Created test records for a job role and candidate.
- Tested the endpoint manually using Apidog.
- Reviewed the API responses and verified the stored document data.

### AI Issues / Corrections
- A later request returned `500` because the `Candidate::documents()` relationship was missing.
- The missing relationship was added and the endpoint was retested.

### Verification
- The upload endpoint was tested using Apidog.
- A PDF file was uploaded successfully.
- The API returned HTTP `201 Created`.
- The file path was stored successfully.
- A `candidate_documents` record was created with `processing_status = pending`.

*******************************************************************************
### AI Assistance
Dependency Security Review
- Composer audit was executed after adding document extraction dependencies.
- Four security advisories were detected.
- The advisories affected `laravel/framework`, not the newly added PDF/DOCX extraction packages.
- PHPWord was initially installed at version 1.1 because the Docker image was missing the `gd` PHP extension.
- The Docker configuration was reviewed to add `gd` support before continuing with document extraction.
- Ran `composer audit` after upgrading PHPWord to `1.4.0`.
- No new advisories were introduced by the extraction libraries.

## Flag (postpone)
- The remaining advisories are associated with `laravel/framework`.