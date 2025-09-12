# AI Service

AI-powered microservice for EMPLOIDB job portal platform.

## Features

- **Text Rewriting**: AI-powered job description rewriting and optimization
- **Resume Parsing**: Extract structured data from resume files (PDF, DOCX)
- **Job Matching**: Match candidates with job descriptions using semantic similarity
- **Semantic Search**: Advanced search capabilities with vector embeddings
- **Multi-language Support**: French, Arabic, English language processing

## API Endpoints

### Health Check
- `GET /health` - Service health status

### Text Processing
- `POST /rewrite` - Rewrite text using AI
- `POST /parse-resume` - Parse resume files
- `POST /match-job` - Match job with candidate
- `POST /semantic-search` - Semantic search

## Environment Variables

```bash
OPENAI_API_KEY=your-openai-api-key
GOOGLE_AI_API_KEY=your-google-ai-api-key
AI_SERVICE_API_KEY=your-service-api-key
```

## Development

```bash
# Install dependencies
pip install -r requirements.txt

# Run development server
uvicorn app:app --reload --host 0.0.0.0 --port 8001
```

## Docker

```bash
# Build image
docker build -t emploidb-ai-service .

# Run container
docker run -p 8001:8001 emploidb-ai-service
```

## Testing

```bash
# Run tests
pytest tests/

# Test specific endpoint
curl -X POST "http://localhost:8001/rewrite" \
     -H "Content-Type: application/json" \
     -d '{"text": "Looking for a developer", "language": "fr"}'
```
