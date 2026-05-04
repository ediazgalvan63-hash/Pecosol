"""
Simple HTTP server for Pecosol Chatbot - With database access
"""
import http.server
import socketserver
import json
import os
from urllib.parse import parse_qs
import mysql.connector
from datetime import datetime

# Database configuration
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'pecosol_db',
    'port': 3306
}

def get_today_sales():
    """Get today's sales data"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        # Get today's sales
        cursor.execute("""
            SELECT COUNT(*) as total_sales,
                   COALESCE(SUM(total_price), 0) as total_amount
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
        """)
        sales_data = cursor.fetchone()
        
        # Get top products today
        cursor.execute("""
            SELECT p.name, SUM(s.quantity) as total_quantity
            FROM sales s
            JOIN products p ON s.product_id = p.id
            WHERE DATE(s.sale_date) = CURDATE()
            GROUP BY p.id, p.name
            ORDER BY total_quantity DESC
            LIMIT 5
        """)
        top_products = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        return sales_data, top_products
    except Exception as e:
        print(f"Database error: {e}")
        return None, []

def get_inventory_status():
    """Get current inventory status"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT name, stock
            FROM products
            WHERE stock > 0
            ORDER BY stock DESC
            LIMIT 10
        """)
        products = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        return products
    except Exception as e:
        print(f"Database error: {e}")
        return []

class ChatbotHandler(http.server.BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            response = {
                "service": "Pecosol Chatbot API",
                "status": "running",
                "message": "Servidor funcionando correctamente"
            }
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode('utf-8'))
        elif self.path == '/health':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            db_status = "disconnected"
            try:
                conn = mysql.connector.connect(**DB_CONFIG)
                db_status = "connected" if conn.is_connected() else "disconnected"
                conn.close()
            except Exception:
                db_status = "disconnected"
            response = {
                "status": "healthy" if db_status == "connected" else "unhealthy",
                "database": db_status,
                "service": "Pecosol Chatbot API"
            }
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode('utf-8'))
        else:
            self.send_response(404)
            self.end_headers()

    def do_POST(self):
        if self.path == '/api/chat':
            content_length = int(self.headers['Content-Length'])
            post_data = self.rfile.read(content_length)
            try:
                data = json.loads(post_data.decode())
                message = data.get('message', '').lower()
                
                # Respuesta basada en consulta
                if "ventas" in message or "venta" in message:
                    sales_data, top_products = get_today_sales()
                    if sales_data:
                        response_text = "📊 **VENTAS DEL DÍA**\n\n"
                        response_text += f"💰 **Total de ventas:** {sales_data['total_sales']}\n"
                        response_text += f"💵 **Monto total:** ${sales_data['total_amount']:.2f}\n\n"
                        
                        if top_products:
                            response_text += "🏆 **Productos más vendidos hoy:**\n"
                            for i, product in enumerate(top_products, 1):
                                response_text += f"{i}. {product['name']}: {product['total_quantity']} unidades\n"
                        else:
                            response_text += "📝 No hay productos vendidos hoy."
                    else:
                        response_text = "❌ No pude acceder a los datos de ventas en este momento."
                        
                elif "stock" in message or "inventario" in message:
                    products = get_inventory_status()
                    if products:
                        response_text = "📦 **INVENTARIO ACTUAL**\n\n"
                        response_text += "📋 **Productos disponibles:**\n"
                        for product in products:
                            stock = int(product.get('stock') or 0)
                            status = "🟢" if stock > 10 else "🟡" if stock > 0 else "🔴"
                            response_text += f"{status} {product['name']}: {stock} unidades\n"
                    else:
                        response_text = "❌ No pude acceder al inventario en este momento."
                else:
                    response_text = f"🤔 No entendí tu consulta sobre '{data.get('message', '')}'.\n\n"
                    response_text += "💡 **Puedo ayudarte con:**\n"
                    response_text += "• **ventas** - Ver ventas del día\n"
                    response_text += "• **stock** o **inventario** - Ver estado del inventario\n\n"
                    response_text += "✨ Pregúntame sobre el negocio de Pecosol."
                
                response = {
                    "success": True,
                    "response": response_text
                }
            except Exception as e:
                response = {"success": False, "error": f"Error al procesar la solicitud: {str(e)}"}
            
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode('utf-8'))
        else:
            self.send_response(404)
            self.end_headers()

    def do_OPTIONS(self):
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()

if __name__ == "__main__":
    PORT = int(os.getenv("API_PORT", "8000"))
    with socketserver.TCPServer(("", PORT), ChatbotHandler) as httpd:
        print(f"Servidor corriendo en http://127.0.0.1:{PORT}")
        httpd.serve_forever()