"""
Simple FastAPI test server - Sin dependencias de servicios
"""
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import os
from dotenv import load_dotenv

load_dotenv()

app = FastAPI(title="Pecosol Chatbot API Test")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
async def root():
    return {
        "service": "Pecosol Chatbot API",
        "status": "running",
        "message": "Servidor funcionando correctamente"
    }

@app.get("/health")
async def health():
    return {
        "status": "healthy",
        "service": "Pecosol Chatbot API Test"
    }

@app.post("/api/chat")
async def chat(message: str = None):
    """Endpoint simple del chatbot para testing"""
    if not message:
        return {"success": False, "error": "No message provided"}
    
    # Respuesta simple
    if "ventas" in message.lower():
        response = "Hoy se han registrado 5 ventas por un total de $1,250.00. Los productos más vendidos son: Producto A (3 unidades), Producto B (2 unidades)."
    elif "stock" in message.lower():
        response = "El inventario actual muestra: Producto A (15 unidades), Producto B (8 unidades), Producto C (22 unidades)."
    else:
        response = f"Entiendo que preguntaste sobre '{message}'. Soy el asistente IA de Pecosol. Puedo ayudarte con información sobre ventas, inventario y estadísticas del negocio."
    
    return {
        "success": True,
        "response": response
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8000, reload=True)
