"""
FastAPI Chatbot Service - Pecosol
Microservicio Python para el chatbot con acceso directo a la base de datos
"""
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional
import os
from dotenv import load_dotenv

# Cargar variables de entorno
load_dotenv()

# Importar servicios
from services.chatbot_service import ChatbotService
from services.database_service import DatabaseService

app = FastAPI(
    title="Pecosol Chatbot API",
    description="API de chatbot con IA y acceso a base de datos",
    version="1.0.0"
)

# Configurar CORS para permitir peticiones desde el frontend PHP
allowed_origins_raw = os.getenv("CHATBOT_ALLOWED_ORIGINS", "")
if allowed_origins_raw:
    allowed_origins = [origin.strip() for origin in allowed_origins_raw.split(",") if origin.strip()]
else:
    app_base_url = os.getenv("APP_BASE_URL", "")
    if app_base_url:
        allowed_origins = [origin.strip() for origin in app_base_url.split(",") if origin.strip()]
    else:
        allowed_origins = [
            "http://localhost",
            "http://localhost:80",
            "http://localhost:3000",
            "http://127.0.0.1",
            "http://127.0.0.1:80",
        ]
app.add_middleware(
    CORSMiddleware,
    allow_origins=allowed_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Inicializar servicios
db_service = DatabaseService()
chatbot_service = ChatbotService(db_service)

# Modelos Pydantic
class ChatRequest(BaseModel):
    message: str
    user_id: Optional[int] = None
    session_id: Optional[str] = None

class ChatResponse(BaseModel):
    success: bool
    response: Optional[str] = None
    error: Optional[str] = None
    context_used: Optional[dict] = None

@app.get("/")
async def root():
    """Endpoint de bienvenida"""
    return {
        "service": "Pecosol Chatbot API",
        "status": "running",
        "version": "1.0.0",
        "endpoints": {
            "chat": "/api/chat",
            "health": "/health",
            "docs": "/docs"
        }
    }

@app.get("/health")
async def health_check():
    """Verificar estado del servicio y conexión a DB"""
    db_status = await db_service.check_connection()
    return {
        "status": "healthy" if db_status else "unhealthy",
        "database": "connected" if db_status else "disconnected",
        "gemini_configured": bool(os.getenv("GEMINI_API_KEY"))
    }

@app.post("/api/chat", response_model=ChatResponse)
async def chat(request: ChatRequest):
    """
    Endpoint principal del chatbot
    Recibe una pregunta, consulta la DB y obtiene respuesta de OpenAI
    """
    try:
        if not request.message or len(request.message.strip()) == 0:
            raise HTTPException(status_code=400, detail="El mensaje no puede estar vacío")
        
        # Procesar la consulta con el servicio de chatbot
        response, context = await chatbot_service.process_message(
            message=request.message,
            user_id=request.user_id,
            session_id=request.session_id
        )
        
        return ChatResponse(
            success=True,
            response=response,
            context_used=context
        )
    
    except Exception as e:
        return ChatResponse(
            success=False,
            error=f"Error al procesar tu pregunta: {str(e)}"
        )

@app.get("/api/stats")
async def get_stats():
    """Obtener estadísticas del sistema desde la DB"""
    try:
        stats = await db_service.get_business_stats()
        return {"success": True, "data": stats}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    host = os.getenv("API_HOST", "127.0.0.1")
    # Railway inyecta PORT automáticamente
    port = int(os.getenv("PORT", os.getenv("API_PORT", "8000")))
    reload_enabled = os.getenv("API_RELOAD", "true").lower() == "true"
    uvicorn.run(
        "main:app",
        host=host,
        port=port,
        reload=reload_enabled,
        log_level="info"
    )
