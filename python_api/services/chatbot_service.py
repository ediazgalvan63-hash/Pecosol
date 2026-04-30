"""
Servicio principal del chatbot con integración Google Gemini
"""
import os
import asyncio
import google.generativeai as genai
import openai
from typing import Dict, Optional, Tuple
import logging
import json
import re

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class ChatbotService:
    """Lógica principal del chatbot con IA de Google Gemini"""
    
    def __init__(self, db_service):
        self.db_service = db_service
        self.api_key = os.getenv("GEMINI_API_KEY")
        
        if not self.api_key:
            logger.warning("⚠️ GEMINI_API_KEY no configurada")
            self.client = None
        else:
            logger.info("✅ Google Gemini API key configurada")
            genai.configure(api_key=self.api_key)
            model_name = os.getenv("GEMINI_MODEL", "gemini-1.5-flash")
            self.client = genai.GenerativeModel(model_name)
        
        self.model = os.getenv("GEMINI_MODEL", "gemini-1.5-flash")
        # OpenAI fallback
        self.openai_api_key = os.getenv("OPENAI_API_KEY")
        self.openai_model = os.getenv("OPENAI_MODEL", "gpt-4o-mini")
        if self.openai_api_key:
            try:
                openai.api_key = self.openai_api_key
                logger.info("ℹ️ OpenAI API key configurada (fallback disponible)")
            except Exception:
                logger.warning("⚠️ No se pudo configurar OpenAI API key correctamente")
    
    async def get_context_from_db(self, message: str) -> Dict:
        """
        Analizar la pregunta y obtener contexto relevante de la base de datos
        """
        context = {}
        message_lower = message.lower()
        
        try:
            # Si pregunta sobre productos o inventario
            if any(word in message_lower for word in ['producto', 'inventario', 'stock', 'precio', 'disponible']):
                products = await self.db_service.get_products_info(limit=30)
                low_stock = await self.db_service.get_low_stock_products()
                context['products'] = products[:10]  # Limitar para no saturar el prompt
                context['low_stock_products'] = low_stock
                context['total_products'] = len(products)
            
            # Si pregunta sobre ventas
            if any(word in message_lower for word in ['venta', 'vender', 'vendido', 'ingreso', 'ganancia']):
                sales_stats = await self.db_service.get_sales_statistics()
                recent_sales = await self.db_service.get_recent_sales(limit=10)
                context['sales_statistics'] = sales_stats
                context['recent_sales'] = recent_sales[:5]
                
                # Si pregunta específicamente por ventas de hoy/día
                if any(word in message_lower for word in ['hoy', 'día', 'dia', 'actual', 'diaria', 'diario']):
                    today_sales = await self.db_service.get_today_sales()
                    context['today_sales'] = today_sales
                    logger.info(f"📅 Ventas de hoy: {today_sales['total_sales']} ventas, ${today_sales['total_revenue']:.2f}")
            
            # Si pregunta sobre empleados
            if any(word in message_lower for word in ['empleado', 'vendedor', 'personal', 'trabajador']):
                employees = await self.db_service.get_employees_info()
                context['employees'] = employees
                
                # Si pregunta por ventas de empleados hoy
                if any(word in message_lower for word in ['hoy', 'día', 'dia', 'actual']):
                    employees_today = await self.db_service.get_employees_today_sales()
                    context['employees_today'] = employees_today
                    logger.info(f"👥 Empleados con ventas hoy: {len([e for e in employees_today if e['sales_today'] > 0])}")
            
            # Si no hay contexto específico, dar overview general
            if not context:
                stats = await self.db_service.get_business_stats()
                context['business_overview'] = stats
            
            logger.info(f"📊 Contexto obtenido: {list(context.keys())}")
            return context
        
        except Exception as e:
            logger.error(f"Error obteniendo contexto: {e}")
            return {"error": str(e)}
    
    def build_system_prompt(self, context: Dict) -> str:
        """
        Construir el prompt del sistema con el contexto de la base de datos
        """
        base_prompt = """Eres un asistente IA para Pecosol, una tienda de productos. 
Tu rol es ayudar al administrador con información sobre:
- Inventario y productos
- Ventas y estadísticas (incluyendo ventas del día actual)
- Empleados y su rendimiento
- Análisis de negocio en tiempo real

IMPORTANTE: Tienes acceso COMPLETO a la base de datos en tiempo real. 
Los datos que se te proporcionan son ACTUALES y puedes responder con confianza sobre:
- Ventas realizadas HOY
- Rendimiento de empleados HOY
- Estado actual del inventario
- Cualquier estadística del negocio

ACLARACIÓN CRÍTICA sobre los datos:
- Cuando veas "employee_name" o "Vendedor:", se refiere al EMPLEADO/VENDEDOR que realizó la venta (el trabajador de la tienda)
- El sistema NO registra datos del cliente final que compró
- Ejemplo: "Venta #10: $399.98 - Vendedor: Ale Peres" significa que el EMPLEADO Ale Peres fue quien atendió y registró esa venta

Responde de manera concisa, profesional y útil. Usa los datos proporcionados para dar respuestas precisas y directas.
Cuando menciones ventas, deja claro que mencionas al VENDEDOR/EMPLEADO que la realizó, no al cliente.
"""
        
        # Agregar contexto de productos
        if 'products' in context:
            products_info = "\n".join([
                f"- {p['name']}: ${p['price']}, Stock: {p['stock']} ({p.get('stock_status', 'N/A')})"
                for p in context['products'][:10]
            ])
            base_prompt += f"\n\n📦 PRODUCTOS DISPONIBLES:\n{products_info}"
        
        if 'low_stock_products' in context and context['low_stock_products']:
            low_stock_info = ", ".join([p['name'] for p in context['low_stock_products'][:5]])
            base_prompt += f"\n\n⚠️ PRODUCTOS CON STOCK BAJO: {low_stock_info}"
        
        # Agregar estadísticas de ventas
        if 'sales_statistics' in context:
            stats = context['sales_statistics']
            base_prompt += f"""

📊 ESTADÍSTICAS DE VENTAS:
- Total de ventas: {stats.get('total_sales', 0)}
- Ingresos totales: ${stats.get('total_revenue', 0):.2f}
- Promedio por venta: ${stats.get('average_sale', 0):.2f}
- Ventas últimos 30 días: {stats.get('recent_sales_30d', 0)}
- Ingresos últimos 30 días: ${stats.get('recent_revenue_30d', 0):.2f}
"""
            
            if 'top_products' in stats and stats['top_products']:
                top_info = "\n".join([
                    f"  {i+1}. {p['name']}: {p['total_quantity']} unidades (${p['total_sales']:.2f})"
                    for i, p in enumerate(stats['top_products'][:5])
                ])
                base_prompt += f"\n🏆 TOP PRODUCTOS MÁS VENDIDOS:\n{top_info}"
        
        # Agregar información de ventas del día
        if 'today_sales' in context:
            today = context['today_sales']
            base_prompt += f"""\n\n📅 VENTAS DE HOY ({context.get('current_date', 'hoy')}):
- Total de ventas: {today['total_sales']}
- Ingresos: ${today['total_revenue']:.2f}
- Promedio por venta: ${today['average_sale']:.2f}
"""
            if today['sales_details']:
                sales_info = "\n".join([
                    f"  • Venta #{s['id']}: ${s['total']:.2f} - Vendedor: {s['employee_name']} - Producto: {s['product_name']} ({s['quantity']} unidades)"
                    for s in today['sales_details'][:10]
                ])
                base_prompt += f"\nDetalles de ventas (cada línea muestra: ID de venta, monto, VENDEDOR que realizó la venta, producto y cantidad):\n{sales_info}"
        
        # Agregar información de empleados
        if 'employees' in context:
            employees_info = "\n".join([
                f"- {e['full_name']}: {e['total_sales']} ventas totales, ${e['total_revenue']:.2f} en ingresos"
                for e in context['employees'][:10]
            ])
            base_prompt += f"\n\n👥 EMPLEADOS (rendimiento total):\n{employees_info}"
        
        # Agregar ventas de empleados del día
        if 'employees_today' in context:
            emp_today_info = "\n".join([
                f"- {e['full_name']}: {e['sales_today']} ventas hoy, ${e['revenue_today']:.2f}"
                for e in context['employees_today'][:10]
            ])
            base_prompt += f"\n\n👥 VENTAS DE EMPLEADOS HOY:\n{emp_today_info}"
        
        # Overview general
        if 'business_overview' in context:
            overview = context['business_overview']
            base_prompt += f"""

📈 RESUMEN DEL NEGOCIO:
- Productos activos: {overview.get('products_count', 0)}
- Empleados: {overview.get('employees_count', 0)}
- Productos con stock bajo: {overview.get('low_stock_count', 0)}
"""
        
        return base_prompt

    def build_report_summary(self, context: Dict) -> Optional[str]:
        parts = ["Aquí tienes un resumen rápido de reportes disponibles:"]
        if 'sales_statistics' in context:
            stats = context['sales_statistics']
            parts.append(f"Ventas totales: {stats.get('total_sales', 0)}, ingresos ${stats.get('total_revenue',0):.2f}.")
            parts.append(f"Ventas últimos 30 días: {stats.get('recent_sales_30d', 0)}, ingresos ${stats.get('recent_revenue_30d',0):.2f}.")
        if 'today_sales' in context:
            today = context['today_sales']
            parts.append(f"Ventas hoy: {today.get('total_sales',0)} ventas, ingresos ${today.get('total_revenue',0):.2f}.")
        if 'low_stock_products' in context and context['low_stock_products']:
            low = [p['name'] for p in context['low_stock_products'][:5]]
            parts.append(f"Productos con stock bajo: {', '.join(low)}.")
        if 'employees_today' in context and context['employees_today']:
            emp_today = [f"{e['full_name']}: {e['sales_today']} ventas hoy" for e in context['employees_today'][:5]]
            parts.append(f"Empleados con ventas hoy: {', '.join(emp_today)}.")
        if 'business_overview' in context:
            overview = context['business_overview']
            parts.append(f"Productos activos: {overview.get('products_count', 0)}. Empleados: {overview.get('employees_count', 0)}. Productos con stock bajo: {overview.get('low_stock_count', 0)}.")
        if len(parts) == 1:
            return None
        return ' '.join(parts)

    def build_local_fallback(self, context: Dict) -> str:
        parts = []
        if 'low_stock_products' in context and context['low_stock_products']:
            low = [p['name'] for p in context['low_stock_products'][:5]]
            parts.append(f"Productos con stock bajo: {', '.join(low)}.")
        if 'products' in context:
            parts.append(f"Total de productos registrados: {context.get('total_products', len(context.get('products', [])))}.")
        if 'today_sales' in context:
            today = context['today_sales']
            parts.append(f"Ventas hoy: {today.get('total_sales', 0)} ventas, ingresos ${today.get('total_revenue', 0):.2f}.")
        if 'sales_statistics' in context:
            stats = context['sales_statistics']
            parts.append(f"Ventas totales: {stats.get('total_sales', 0)}, ingresos totales ${stats.get('total_revenue', 0):.2f}, promedio por venta ${stats.get('average_sale', 0):.2f}.")
            if stats.get('recent_sales_30d') is not None:
                parts.append(f"Ventas últimos 30 días: {stats.get('recent_sales_30d', 0)}, ingresos ${stats.get('recent_revenue_30d', 0):.2f}.")
        if 'business_overview' in context:
            overview = context['business_overview']
            parts.append(f"Productos activos: {overview.get('products_count', 0)}. Empleados: {overview.get('employees_count', 0)}. Productos con stock bajo: {overview.get('low_stock_count', 0)}.")
        if not parts:
            return "Lo siento, el servicio de IA no está disponible en este momento. Intenta de nuevo más tarde o configura una API key válida para OpenAI/Google Gemini."
        return ' '.join(parts)

    async def call_gemini(self, messages: list) -> str:
        """
        Hacer llamada a la API de Google Gemini
        """
        if not self.client:
            raise Exception("GEMINI_API_KEY no está configurada. Obtén una gratis en https://aistudio.google.com/app/apikey")
        
        try:
            logger.info(f"🤖 Llamando a Google Gemini ({self.model})...")
            
            # Convertir formato de mensajes de OpenAI a Gemini
            prompt_parts = []
            for msg in messages:
                role = msg.get("role", "user")
                content = msg.get("content", "")
                
                if role == "system":
                    prompt_parts.append(f"INSTRUCCIONES DEL SISTEMA:\n{content}\n")
                elif role == "user":
                    prompt_parts.append(f"USUARIO: {content}")
                elif role == "assistant":
                    prompt_parts.append(f"ASISTENTE: {content}")
            
            full_prompt = "\n\n".join(prompt_parts)
            
            # Ejecutar la llamada en un thread para no bloquear
            response = await asyncio.to_thread(
                self.client.generate_content,
                full_prompt,
                generation_config={
                    "temperature": 0.7,
                    "max_output_tokens": 800,
                    "top_p": 0.9,
                }
            )
            
            answer = response.text.strip()
            logger.info(f"✅ Respuesta recibida de Gemini ({len(answer)} caracteres)")
            
            return answer
        
        except Exception as e:
            error_msg = str(e)
            logger.error(f"❌ Error llamando a Gemini: {error_msg}")
            
            # Detectar error de clave inválida
            if "API_KEY_INVALID" in error_msg or "invalid" in error_msg.lower():
                raise Exception("API Key inválida. Obtén una gratis en https://aistudio.google.com/app/apikey")

            # Si es límite/cuota, intentar fallback a OpenAI si está configurado
            if "quota" in error_msg.lower() or "limit" in error_msg.lower() or "rate" in error_msg.lower():
                logger.warning("⚠️ Gemini quota/limit detected")
                if self.openai_api_key:
                    logger.info("➡️ Intentando fallback a OpenAI...")
                    try:
                        fallback = await self.call_openai(messages)
                        logger.info("✅ Respuesta recibida de OpenAI (fallback)")
                        return fallback
                    except Exception as oe:
                        logger.error(f"❌ Fallback a OpenAI falló: {oe}")
                        raise Exception("Límite de uso alcanzado en Gemini y fallback a OpenAI falló. Intenta de nuevo más tarde.")
                else:
                    raise Exception("Límite de uso alcanzado. Espera un momento e intenta de nuevo.")

            # Otros errores
            raise Exception(f"Error de Gemini: {error_msg}")

    async def call_openai(self, messages: list) -> str:
        """
        Usar OpenAI como fallback si Gemini no está disponible o alcanza cuota.
        """
        if not self.openai_api_key:
            raise Exception("OpenAI API key no configurada")

        # Convertir mensajes al formato de OpenAI
        oa_messages = []
        for m in messages:
            role = m.get("role", "user")
            content = m.get("content", "")
            oa_messages.append({"role": role, "content": content})

        def sync_call():
            resp = openai.ChatCompletion.create(
                model=self.openai_model,
                messages=oa_messages,
                temperature=0.4,
                max_tokens=600,
                top_p=0.9,
                frequency_penalty=0.2,
                presence_penalty=0.0
            )
            if hasattr(resp, 'choices') and len(resp.choices) > 0:
                return resp.choices[0].message.content.strip()
            if 'choices' in resp and len(resp['choices']) > 0:
                return resp['choices'][0]['message']['content'].strip()
            return str(resp)

        result = await asyncio.to_thread(sync_call)
        return result
    
    async def process_message(
        self, 
        message: str, 
        user_id: Optional[int] = None,
        session_id: Optional[str] = None
    ) -> Tuple[str, Dict]:
        """
        Procesar mensaje del usuario y generar respuesta
        
        Returns:
            Tuple[str, Dict]: (respuesta, contexto_usado)
        """
        context = {}
        try:
            # Manejo rápido: si el usuario pregunta por el stock de un producto
            # intentamos resolverlo directamente consultando la base de datos
            # (evita pasar por Gemini para respuestas exactas de inventario)
            if 'stock' in message.lower():
                # Verificar si pregunta solo "stock" (todos los productos)
                if message.lower().strip() == 'stock':
                    try:
                        all_products = await self.db_service.get_products_info(limit=50)
                        if all_products:
                            reply_lines = ["📦 **INVENTARIO COMPLETO:**"]
                            for p in all_products:
                                status = "✅ OK" if p.get('stock', 0) > p.get('stock_minimum', 0) else "⚠️ BAJO STOCK"
                                reply_lines.append(f"• {p.get('name')}: {p.get('stock', 0)} unidades - ${float(p.get('price', 0)):.2f} ({status})")
                            reply = "\n".join(reply_lines)
                            return reply, {'all_products': all_products}
                        else:
                            return "No hay productos registrados en el inventario.", {}
                    except Exception as db_e:
                        logger.error(f"Error obteniendo inventario completo: {db_e}")
                        return "Error consultando el inventario. Inténtalo de nuevo.", {}

                # extraer posible nombre de producto usando heurística
                # buscamos frases como 'stock de <producto>' o 'stock <producto>' o 'stock del <producto>'
                m = re.search(r"stock(?:\s+de|\s+del|\s+la|\s+el)?\s+([\w\-\s]+)", message, re.IGNORECASE)
                product_query = None
                if m:
                    product_query = m.group(1).strip()
                else:
                    # si no se detectó con la expresión, intentar tomar la última palabra como término
                    tokens = message.strip().split()
                    if len(tokens) > 1:
                        product_query = tokens[-1]

                if product_query:
                    try:
                        # buscar productos que coincidan con el término
                        results = await self.db_service.search_products(product_query)
                        if results:
                            # si hay coincidencia exacta por nombre, devolver stock del primer resultado exacto
                            chosen = None
                            for p in results:
                                if p.get('name','').lower() == product_query.lower():
                                    chosen = p
                                    break
                            if not chosen:
                                chosen = results[0]

                            stock = chosen.get('stock', 0)
                            stock_min = chosen.get('stock_minimum', 0)
                            status = "✅ STOCK OK" if stock > stock_min else "⚠️ STOCK BAJO"
                            reply = f"📦 **{chosen.get('name').upper()}**\n"
                            reply += f"• Stock actual: {stock} unidades\n"
                            reply += f"• Stock mínimo: {stock_min} unidades\n"
                            reply += f"• Precio: ${float(chosen.get('price',0)):.2f}\n"
                            reply += f"• Estado: {status}\n"
                            reply += f"• Descripción: {chosen.get('description', 'Sin descripción')}"

                            return reply, { 'product': chosen }
                        else:
                            # no encontrado
                            return f"❌ No encontré ningún producto que coincida con '{product_query}'. Intenta con el nombre exacto.", {}
                    except Exception as db_e:
                        logger.error(f"Error consultando producto para stock: {db_e}")
                        # seguir con el flujo normal si falla la consulta

            # 1. Obtener contexto de la base de datos
            context = await self.get_context_from_db(message)
            
            # 2. Construir el prompt del sistema con el contexto
            system_prompt = self.build_system_prompt(context)
            
            # 3. Preparar mensajes para el modelo
            messages = [
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": message}
            ]

            # Respuestas locales directas para consultas específicas
            message_lower = message.lower().strip()

            # Consulta de inventario general
            if message_lower in ['inventario', 'ver inventario', 'estado inventario', 'productos']:
                try:
                    all_products = await self.db_service.get_products_info(limit=50)
                    if all_products:
                        total_products = len(all_products)
                        total_stock = sum(p.get('stock', 0) for p in all_products)
                        low_stock_count = sum(1 for p in all_products if p.get('stock', 0) <= p.get('stock_minimum', 0))

                        reply = f"📦 **RESUMEN DE INVENTARIO**\n\n"
                        reply += f"• Total de productos: {total_products}\n"
                        reply += f"• Stock total: {total_stock} unidades\n"
                        reply += f"• Productos con stock bajo: {low_stock_count}\n\n"
                        reply += "📋 **PRODUCTOS REGISTRADOS:**\n"

                        for p in all_products[:10]:  # Mostrar máximo 10 productos
                            status = "✅ OK" if p.get('stock', 0) > p.get('stock_minimum', 0) else "⚠️ BAJO STOCK"
                            reply += f"• {p.get('name')}: {p.get('stock', 0)} unidades - ${float(p.get('price', 0)):.2f} ({status})\n"

                        if total_products > 10:
                            reply += f"\n... y {total_products - 10} productos más. Di 'stock' para ver el inventario completo."

                        return reply, {'inventory_summary': all_products}
                    else:
                        return "📦 No hay productos registrados en el inventario.", {}
                except Exception as e:
                    logger.error(f"Error obteniendo resumen de inventario: {e}")
                    return "Error consultando el inventario. Inténtalo de nuevo.", {}

            # Consulta de ventas
            if any(word in message_lower for word in ['ventas', 'venta', 'ver ventas']):
                try:
                    sales_stats = await self.db_service.get_sales_statistics()
                    today_sales = await self.db_service.get_today_sales()

                    reply = f"💰 **RESUMEN DE VENTAS**\n\n"
                    reply += f"**Estadísticas Totales:**\n"
                    reply += f"• Ventas totales: {sales_stats.get('total_sales', 0)}\n"
                    reply += f"• Ingresos totales: ${float(sales_stats.get('total_revenue', 0)):.2f}\n"
                    reply += f"• Promedio por venta: ${float(sales_stats.get('average_sale', 0)):.2f}\n\n"
                    
                    reply += f"**Ventas del día:**\n"
                    reply += f"• Total de ventas: {today_sales.get('total_sales', 0)}\n"
                    reply += f"• Ingresos del día: ${float(today_sales.get('total_revenue', 0)):.2f}\n"
                    reply += f"• Promedio: ${float(today_sales.get('average_sale', 0)):.2f}\n\n"

                    # Mostrar productos más vendidos si hay datos
                    if 'top_products' in sales_stats and sales_stats['top_products']:
                        reply += "🏆 **PRODUCTOS MÁS VENDIDOS (Total):**\n"
                        for product in sales_stats['top_products'][:5]:
                            reply += f"• {product.get('name', 'N/A')}: {product.get('total_quantity', 0)} unidades (${float(product.get('total_sales', 0)):.2f})\n"

                    return reply, {'sales_stats': sales_stats, 'today_sales': today_sales}
                except Exception as e:
                    logger.error(f"Error obteniendo estadísticas de ventas: {e}")
                    return "Error consultando las ventas. Inténtalo de nuevo.", {}

            # Consulta de estadísticas generales
            if any(word in message_lower for word in ['estadísticas', 'estadisticas', 'stats', 'estadística', 'estadistica']):
                try:
                    business_stats = await self.db_service.get_business_stats()
                    sales_stats = await self.db_service.get_sales_statistics()

                    reply = f"📊 **ESTADÍSTICAS GENERALES DEL NEGOCIO**\n\n"
                    reply += f"🏪 **INVENTARIO:**\n"
                    reply += f"• Productos totales: {business_stats.get('products_count', 0)}\n"
                    reply += f"• Productos con stock bajo: {business_stats.get('low_stock_count', 0)}\n\n"

                    reply += f"👥 **PERSONAL:**\n"
                    reply += f"• Empleados activos: {business_stats.get('employees_count', 0)}\n\n"

                    reply += f"💰 **VENTAS:**\n"
                    reply += f"• Ventas totales: {sales_stats.get('total_sales', 0)}\n"
                    reply += f"• Ingresos totales: ${sales_stats.get('total_revenue', 0):.2f}\n"
                    reply += f"• Valor promedio por venta: ${sales_stats.get('avg_sale_value', 0):.2f}\n"

                    return reply, {'business_stats': business_stats, 'sales_stats': sales_stats}
                except Exception as e:
                    logger.error(f"Error obteniendo estadísticas generales: {e}")
                    return "Error consultando las estadísticas. Inténtalo de nuevo.", {}

            # Consulta de reportes disponibles
            if any(word in message_lower for word in ['reportes', 'reporte', 'informes', 'informe']):
                reply = f"📋 **REPORTES DISPONIBLES**\n\n"
                reply += f"📦 **INVENTARIO:**\n"
                reply += f"• Inventario actual (Excel) - Estado completo de productos\n"
                reply += f"• Kardex/Movimientos (Excel) - Historial de entradas y salidas\n\n"

                reply += f"💰 **VENTAS:**\n"
                reply += f"• Reporte de ventas (Excel) - Ventas por período\n\n"

                reply += f"🔧 **ACCIONES DISPONIBLES:**\n"
                reply += f"• Ve a la sección 'Reportes' en el menú para descargar archivos Excel\n"
                reply += f"• Los reportes incluyen filtros por fecha y producto\n"
                reply += f"• Formato profesional con tablas, estilos y autoajuste\n\n"

                reply += f"💡 **CONSULTAS RÁPIDAS:**\n"
                reply += f"• Di 'inventario' para ver resumen\n"
                reply += f"• Di 'ventas' para estadísticas de venta\n"
                reply += f"• Di 'stock' para inventario completo\n"
                reply += f"• Di 'estadísticas' para métricas generales"

                return reply, {'reports_info': True}

            # Respuestas locales directas para consultas frecuentes
            if any(keyword in message.lower() for keyword in [
                'generar reporte', 'reportes', 'reporte', 'informe', 'estadísticas', 'estadisticas', 'balance', 'resumen', 'ventas', 'venta', 'ingresos', 'ganancia']) and context:
                local_answer = self.build_report_summary(context)
                if local_answer:
                    return local_answer, context
            
            # 4. Obtener respuesta de Gemini u OpenAI
            if self.client:
                response = await self.call_gemini(messages)
            elif self.openai_api_key:
                response = await self.call_openai(messages)
            else:
                response = self.build_local_fallback(context)
            
            logger.info(f"✅ Respuesta generada exitosamente")
            
            return response, context
        
        except Exception as e:
            logger.error(f"❌ Error procesando mensaje: {e}")
            err_txt = str(e).lower()
            # Si el error es por límite y no hay fallback OpenAI, generar respuesta local basada en contexto
            if ("límite" in err_txt or "limite" in err_txt or "quota" in err_txt or "limit" in err_txt) and not self.openai_api_key:
                logger.info("ℹ️ Generando respuesta local de fallback (no hay OpenAI configurado)")
                return self.build_local_fallback(context), context
            raise
