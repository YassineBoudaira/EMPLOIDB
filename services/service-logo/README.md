# Logo Service

Logo generation and image processing microservice for EMPLOIDB.

## Features

- **Logo Generation**: Create logos for companies with customizable styles
- **Image Processing**: Transform and optimize images with various effects
- **CDN Sync**: Automatically sync processed images to CDN
- **Multi-format Support**: PNG, JPEG, WebP output formats
- **Tenant Isolation**: Separate storage for different tenants

## API Endpoints

### Health Check
- `GET /health` - Service health status

### Logo & Image Processing
- `POST /generate-logo` - Generate company logo
- `POST /process-image` - Process and transform images
- `POST /sync-cdn` - Sync file to CDN

## Environment Variables

```bash
CDN_PROVIDER=cloudflare
CLOUDFLARE_API_TOKEN=your-cloudflare-api-token
CLOUDFLARE_ZONE_ID=your-cloudflare-zone-id
```

## Development

```bash
# Install dependencies
pip install -r requirements.txt

# Run development server
uvicorn app:app --reload --host 0.0.0.0 --port 8002
```

## Docker

```bash
# Build image
docker build -t emploidb-logo-service .

# Run container
docker run -p 8002:8002 emploidb-logo-service
```

## Testing

```bash
# Test logo generation
curl -X POST "http://localhost:8002/generate-logo" \
     -H "Content-Type: application/json" \
     -d '{"company_name": "Tech Corp", "tenant_id": "1", "style": "modern"}'
```
