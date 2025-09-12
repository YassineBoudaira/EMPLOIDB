"""
Logo Service for EMPLOIDB
Handles logo generation, image processing, and CDN synchronization
"""

from fastapi import FastAPI, HTTPException, UploadFile, File, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import os
import logging
from typing import List, Optional, Dict, Any
import asyncio
from datetime import datetime
import aiofiles
import httpx
from PIL import Image, ImageDraw, ImageFont
import io
import base64

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="EMPLOIDB Logo Service",
    description="Logo generation and image processing service",
    version="1.0.0"
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Configure properly for production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Pydantic models
class LogoGenerateRequest(BaseModel):
    company_name: str
    tenant_id: str
    style: str = "modern"
    colors: Optional[List[str]] = None
    size: int = 256

class LogoGenerateResponse(BaseModel):
    logo_url: str
    logo_path: str
    processing_time: float
    metadata: Dict[str, Any]

class ImageProcessRequest(BaseModel):
    image_url: str
    transformations: List[str]
    output_format: str = "png"
    quality: int = 90

class ImageProcessResponse(BaseModel):
    processed_url: str
    processed_path: str
    transformations_applied: List[str]
    processing_time: float

class CDNSyncRequest(BaseModel):
    file_path: str
    tenant_id: str
    priority: str = "normal"

class CDNSyncResponse(BaseModel):
    cdn_url: str
    sync_status: str
    sync_time: float

# Health check endpoint
@app.get("/health")
async def health_check():
    """Health check endpoint for monitoring"""
    return {
        "status": "healthy",
        "service": "logo-service",
        "timestamp": datetime.utcnow().isoformat(),
        "version": "1.0.0"
    }

# Logo generation endpoint
@app.post("/generate-logo", response_model=LogoGenerateResponse)
async def generate_logo(request: LogoGenerateRequest):
    """
    Generate a logo for a company
    """
    try:
        start_time = datetime.utcnow()
        
        # Generate logo
        logo_path = await _generate_logo(
            request.company_name,
            request.tenant_id,
            request.style,
            request.colors,
            request.size
        )
        
        processing_time = (datetime.utcnow() - start_time).total_seconds()
        
        return LogoGenerateResponse(
            logo_url=f"/uploads/logos/{request.tenant_id}/{os.path.basename(logo_path)}",
            logo_path=logo_path,
            processing_time=processing_time,
            metadata={
                "company_name": request.company_name,
                "style": request.style,
                "size": request.size,
                "generated_at": datetime.utcnow().isoformat()
            }
        )
        
    except Exception as e:
        logger.error(f"Error generating logo: {str(e)}")
        raise HTTPException(status_code=500, detail="Logo generation failed")

# Image processing endpoint
@app.post("/process-image", response_model=ImageProcessResponse)
async def process_image(request: ImageProcessRequest):
    """
    Process and transform images
    """
    try:
        start_time = datetime.utcnow()
        
        # Process image
        processed_path = await _process_image(
            request.image_url,
            request.transformations,
            request.output_format,
            request.quality
        )
        
        processing_time = (datetime.utcnow() - start_time).total_seconds()
        
        return ImageProcessResponse(
            processed_url=f"/uploads/processed/{os.path.basename(processed_path)}",
            processed_path=processed_path,
            transformations_applied=request.transformations,
            processing_time=processing_time
        )
        
    except Exception as e:
        logger.error(f"Error processing image: {str(e)}")
        raise HTTPException(status_code=500, detail="Image processing failed")

# CDN sync endpoint
@app.post("/sync-cdn", response_model=CDNSyncResponse)
async def sync_cdn(request: CDNSyncRequest, background_tasks: BackgroundTasks):
    """
    Sync file to CDN
    """
    try:
        start_time = datetime.utcnow()
        
        # Add CDN sync to background tasks
        background_tasks.add_task(_sync_to_cdn, request.file_path, request.tenant_id, request.priority)
        
        processing_time = (datetime.utcnow() - start_time).total_seconds()
        
        return CDNSyncResponse(
            cdn_url=f"https://cdn.your-domain.com/{request.tenant_id}/{os.path.basename(request.file_path)}",
            sync_status="queued",
            sync_time=processing_time
        )
        
    except Exception as e:
        logger.error(f"Error syncing to CDN: {str(e)}")
        raise HTTPException(status_code=500, detail="CDN sync failed")

# Placeholder functions (to be implemented)
async def _generate_logo(company_name: str, tenant_id: str, style: str, colors: Optional[List[str]], size: int) -> str:
    """Generate a logo for the company"""
    # Create uploads directory if it doesn't exist
    upload_dir = f"uploads/logos/{tenant_id}"
    os.makedirs(upload_dir, exist_ok=True)
    
    # Generate logo filename
    logo_filename = f"{company_name.lower().replace(' ', '_')}_{style}_{size}.png"
    logo_path = os.path.join(upload_dir, logo_filename)
    
    # Create a simple logo (placeholder implementation)
    img = Image.new('RGBA', (size, size), (255, 255, 255, 0))
    draw = ImageDraw.Draw(img)
    
    # Draw a simple rectangle with company initials
    initials = ''.join([word[0].upper() for word in company_name.split()[:2]])
    
    # Set colors
    if colors:
        bg_color = colors[0] if colors else "#2563eb"
        text_color = colors[1] if len(colors) > 1 else "#ffffff"
    else:
        bg_color = "#2563eb"
        text_color = "#ffffff"
    
    # Draw background circle
    margin = size // 8
    draw.ellipse([margin, margin, size-margin, size-margin], fill=bg_color)
    
    # Draw text (simplified - in production, use proper font)
    try:
        font_size = size // 3
        font = ImageFont.truetype("arial.ttf", font_size)
    except:
        font = ImageFont.load_default()
    
    # Calculate text position
    bbox = draw.textbbox((0, 0), initials, font=font)
    text_width = bbox[2] - bbox[0]
    text_height = bbox[3] - bbox[1]
    text_x = (size - text_width) // 2
    text_y = (size - text_height) // 2
    
    draw.text((text_x, text_y), initials, fill=text_color, font=font)
    
    # Save logo
    img.save(logo_path, "PNG")
    
    return logo_path

async def _process_image(image_url: str, transformations: List[str], output_format: str, quality: int) -> str:
    """Process and transform images"""
    # Create processed directory
    processed_dir = "uploads/processed"
    os.makedirs(processed_dir, exist_ok=True)
    
    # Download image
    async with httpx.AsyncClient() as client:
        response = await client.get(image_url)
        response.raise_for_status()
        image_data = response.content
    
    # Open image
    img = Image.open(io.BytesIO(image_data))
    
    # Apply transformations
    for transformation in transformations:
        if transformation == "resize_256":
            img = img.resize((256, 256), Image.Resampling.LANCZOS)
        elif transformation == "add_overlay":
            # Add semi-transparent overlay
            overlay = Image.new('RGBA', img.size, (0, 0, 0, 50))
            img = Image.alpha_composite(img.convert('RGBA'), overlay)
        elif transformation == "adjust_contrast":
            # Adjust contrast
            from PIL import ImageEnhance
            enhancer = ImageEnhance.Contrast(img)
            img = enhancer.enhance(1.2)
        elif transformation == "add_watermark":
            # Add watermark
            draw = ImageDraw.Draw(img)
            watermark_text = "EMPLOIDB"
            try:
                font = ImageFont.truetype("arial.ttf", 20)
            except:
                font = ImageFont.load_default()
            draw.text((10, 10), watermark_text, fill=(255, 255, 255, 128), font=font)
    
    # Generate output filename
    output_filename = f"processed_{datetime.utcnow().strftime('%Y%m%d_%H%M%S')}.{output_format}"
    output_path = os.path.join(processed_dir, output_filename)
    
    # Save processed image
    if output_format.lower() == "png":
        img.save(output_path, "PNG")
    elif output_format.lower() == "jpg":
        img.convert('RGB').save(output_path, "JPEG", quality=quality)
    else:
        img.save(output_path, output_format.upper())
    
    return output_path

async def _sync_to_cdn(file_path: str, tenant_id: str, priority: str):
    """Sync file to CDN (placeholder implementation)"""
    # TODO: Implement actual CDN sync logic
    # This could integrate with Cloudflare, AWS CloudFront, etc.
    logger.info(f"Syncing {file_path} to CDN for tenant {tenant_id} with priority {priority}")
    
    # Simulate CDN sync
    await asyncio.sleep(1)
    logger.info(f"CDN sync completed for {file_path}")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8002)
