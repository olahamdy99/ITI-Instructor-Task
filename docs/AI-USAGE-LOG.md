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
*******************************************************************************

### Task
Implemented PDF and DOCX document text extraction.

### AI Assistance
AI assisted with:
- Selecting suitable libraries for PDF and DOCX text extraction.
- Designing the extractor resolver based on MIME type.
- Structuring the document processing flow.
- Debugging dependency and Docker extension issues.
- Reviewing dependency security advisories using `composer audit`.

### My Work
- Installed and configured `smalot/pdfparser`.
- Installed and upgraded `phpoffice/phpword`.
- Designing the `DocumentTextExtractorInterface`.
- Creating separate PDF and DOCX extractor implementations.
- Created `DocumentTextExtractorInterface`.
- Created `PdfTextExtractor`.
- Created `DocxTextExtractor`.
- Created `DocumentTextExtractorResolver`.
- Registered the resolver in the Laravel service container.
- Implemented `DocumentProcessingService`.
- Tested PDF extraction manually using Laravel Tinker.
- Reviewed the extracted text and verified that it matched the uploaded document content.

### AI Issues / Corrections
- PHPWord was initially installed at version `1.1.0` because the Docker container did not include the `gd` extension.
- The Docker configuration was updated to install and enable `gd`.
- PHPWord was then upgraded successfully to version `1.4.0`.
- Composer security checks showed existing advisories in `laravel/framework`, while no new advisories were introduced by the PDF/DOCX extraction libraries.

### Verification
- `DocumentProcessingService` was resolved successfully through Laravel's service container.
- An existing uploaded PDF was processed using Laravel Tinker.
- The correct PDF extractor was selected based on MIME type.
- Text was extracted successfully from the document.
- The extracted text was manually reviewed.
- The document processing status changed successfully to `processed`.
- `failure_reason` remained `null`.


### My Work
Extraction Verification
- Tested `DocumentProcessingService` using Laravel Tinker.
- Loaded an existing uploaded PDF document.
- The service resolved the correct PDF extractor.
- Text was successfully extracted from the PDF.
- Extracted text was manually reviewed and confirmed to contain the document content.
- The document processing status was expected to transition from `pending` to `processed`.

*******************************************************************************
### Task
Implemented document text cleaning, chunking, and chunk persistence
### AI Assistance
AI assisted with:
- Designing the text cleaning rules.
- Designing the chunking strategy.
- Adding chunk overlap to preserve context.
- Improving chunk boundaries to avoid splitting words.
- Adding input validation for chunk size and overlap.
- Integrating chunk persistence into the document processing flow.

### My Work
- Implemented `DocumentTextCleaner`.
- Implemented `DocumentChunker`.
- Reviewed chunk boundaries against the original extracted text.
- Improved the chunking logic after detecting word splits.
- Updated `DocumentProcessingService` to clean, chunk, and persist document content.
- Updated `DocumentChunk` model fillable fields and metadata casting.
- Tested the complete flow using Laravel Tinker.

### AI Issues / Corrections
- The initial chunking implementation split some words between chunks.
- The chunk boundary logic was adjusted to prefer whitespace and line boundaries.
- The overlap start logic was also improved to avoid starting chunks in the middle of words.

### Verification
- The processed document generated 6 chunks.
- The 6 generated chunks were successfully persisted in the `document_chunks` table.
- The stored chunk count matched the generated chunk count.
- The first persisted chunk was verified directly through Laravel Tinker.
- Reprocessing the same document replaces previous chunks instead of duplicating them.