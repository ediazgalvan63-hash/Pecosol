"""
Servicio de conexión y consultas a la base de datos MySQL
"""
import mysql.connector
from mysql.connector import Error
import os
from typing import Dict, List, Optional, Any
import logging
import re

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class DatabaseService:
    """Maneja todas las operaciones con la base de datos"""
    
    def __init__(self):
        self.config = {
            'host': os.getenv('DB_HOST', 'localhost'),
            'database': os.getenv('DB_NAME', 'pecosol_db'),
            'user': os.getenv('DB_USER', 'root'),
            'password': os.getenv('DB_PASSWORD', ''),
            'port': int(os.getenv('DB_PORT', '3306'))
        }
        self.connection = None
    
    def get_connection(self):
        """Obtener conexión a la base de datos"""
        try:
            if self.connection is None or not self.connection.is_connected():
                self.connection = mysql.connector.connect(**self.config)
                logger.info("✅ Conexión a base de datos establecida")
            return self.connection
        except Error as e:
            logger.error(f"❌ Error conectando a MySQL: {e}")
            raise Exception(f"Error de conexión a base de datos: {e}")
    
    async def check_connection(self) -> bool:
        """Verificar si la conexión está activa"""
        try:
            conn = self.get_connection()
            return conn.is_connected()
        except:
            return False
    
    def execute_query(self, query: str, params: tuple = None) -> List[Dict[str, Any]]:
        """Ejecutar query SELECT y retornar resultados como lista de diccionarios"""
        try:
            conn = self.get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(query, params or ())
            results = cursor.fetchall()
            cursor.close()
            return results
        except Error as e:
            logger.error(f"Error ejecutando query: {e}")
            raise Exception(f"Error en consulta a base de datos: {e}")
    
    async def get_products_info(self, limit: int = 50) -> List[Dict]:
        """Obtener información de productos e inventario"""
        query = """
            SELECT
                p.id,
                p.name,
                p.description,
                p.price,
                p.stock,
                p.stock_minimum,
                CASE
                    WHEN p.stock = 0 THEN 'Sin stock'
                    WHEN p.stock <= p.stock_minimum THEN 'Stock bajo'
                    ELSE 'Disponible'
                END as stock_status
            FROM products p
            ORDER BY p.stock ASC, p.name ASC
            LIMIT %s
        """
        return self.execute_query(query, (limit,))
    
    async def get_low_stock_products(self, threshold: int = 10) -> List[Dict]:
        """Obtener productos con stock bajo"""
        query = """
            SELECT 
                id,
                name,
                stock,
                price
            FROM products
            WHERE stock < %s
            ORDER BY stock ASC
        """
        return self.execute_query(query, (threshold,))
    
    async def get_sales_statistics(self) -> Dict:
        """Obtener estadísticas de ventas"""
        # Total de ventas
        total_query = """
            SELECT 
                COUNT(*) as total_sales,
                COALESCE(SUM(total_price), 0) as total_revenue,
                COALESCE(AVG(total_price), 0) as average_sale
            FROM sales
        """
        total_data = self.execute_query(total_query)[0]
        
        # Ventas recientes (últimos 30 días)
        recent_query = """
            SELECT 
                COUNT(*) as recent_sales,
                COALESCE(SUM(total_price), 0) as recent_revenue
            FROM sales
            WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        """
        recent_data = self.execute_query(recent_query)[0]
        
        # Top productos vendidos
        top_products_query = """
            SELECT 
                p.name,
                SUM(s.quantity) as total_quantity,
                COALESCE(SUM(s.total_price), 0) as total_sales
            FROM sales s
            INNER JOIN products p ON s.product_id = p.id
            GROUP BY p.id, p.name
            ORDER BY total_quantity DESC
            LIMIT 5
        """
        top_products = self.execute_query(top_products_query)
        
        return {
            "total_sales": total_data.get('total_sales', 0),
            "total_revenue": float(total_data.get('total_revenue', 0) or 0),
            "average_sale": float(total_data.get('average_sale', 0) or 0),
            "recent_sales_30d": recent_data.get('recent_sales', 0),
            "recent_revenue_30d": float(recent_data.get('recent_revenue', 0) or 0),
            "top_products": top_products
        }
    
    async def get_today_sales(self) -> Dict:
        """Obtener ventas del día actual"""
        query = """
            SELECT 
                COUNT(*) as total_sales,
                COALESCE(SUM(total_price), 0) as total_revenue,
                COALESCE(AVG(total_price), 0) as average_sale
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
        """
        result = self.execute_query(query)[0]
        
        # Obtener detalles de ventas de hoy
        details_query = """
            SELECT 
                s.id,
                s.sale_date as date,
                s.total_price as total,
                u.full_name as employee_name,
                p.name as product_name,
                s.quantity
            FROM sales s
            INNER JOIN users u ON s.user_id = u.id
            INNER JOIN products p ON s.product_id = p.id
            WHERE DATE(s.sale_date) = CURDATE()
            ORDER BY s.sale_date DESC
        """
        sales_details = self.execute_query(details_query)
        
        return {
            "total_sales": result.get('total_sales', 0) or 0,
            "total_revenue": float(result.get('total_revenue', 0) or 0),
            "average_sale": float(result.get('average_sale', 0) or 0),
            "sales_details": sales_details
        }
    
    async def get_recent_sales(self, limit: int = 20) -> List[Dict]:
        """Obtener ventas recientes con detalles"""
        query = """
            SELECT 
                s.id,
                s.sale_date as date,
                s.total_price as total,
                u.full_name as employee_name,
                p.name as product_name,
                s.quantity
            FROM sales s
            INNER JOIN users u ON s.user_id = u.id
            INNER JOIN products p ON s.product_id = p.id
            ORDER BY s.sale_date DESC
            LIMIT %s
        """
        return self.execute_query(query, (limit,))
    
    async def get_employees_info(self) -> List[Dict]:
        """Obtener información de empleados"""
        query = """
            SELECT 
                u.id,
                u.full_name,
                u.email,
                u.role,
                COUNT(s.id) as total_sales,
                COALESCE(SUM(s.total_price), 0) as total_revenue
            FROM users u
            LEFT JOIN sales s ON u.id = s.user_id
            WHERE u.role = 'employee'
            GROUP BY u.id, u.full_name, u.email, u.role
            ORDER BY total_revenue DESC
        """
        return self.execute_query(query)
    
    async def get_employees_today_sales(self) -> List[Dict]:
        """Obtener ventas de empleados del día actual"""
        query = """
            SELECT 
                u.id,
                u.full_name,
                COUNT(s.id) as sales_today,
                COALESCE(SUM(s.total_price), 0) as revenue_today
            FROM users u
            LEFT JOIN sales s ON u.id = s.user_id AND DATE(s.sale_date) = CURDATE()
            WHERE u.role = 'employee'
            GROUP BY u.id, u.full_name
            ORDER BY revenue_today DESC
        """
        return self.execute_query(query)
    
    async def search_products(self, search_term: str) -> List[Dict]:
        """Buscar productos por nombre o descripción"""
        term = (search_term or '').strip()
        if not term:
            return []

        term_lower = term.lower()
        # 1) Exact name match (case-insensitive)
        try:
            q_exact = "SELECT id, name, description, price, stock, stock_minimum FROM products WHERE LOWER(name) = %s LIMIT 10"
            res = self.execute_query(q_exact, (term_lower,))
            if res:
                return res

            # 2) Simple LIKE on name or description
            q_like = "SELECT id, name, description, price, stock, stock_minimum FROM products WHERE LOWER(name) LIKE %s OR LOWER(description) LIKE %s LIMIT 10"
            like_pattern = f"%{term_lower}%"
            res = self.execute_query(q_like, (like_pattern, like_pattern))
            if res:
                return res

            # 3) Tokenized search: split term and search any token
            tokens = [t for t in re.split(r'\s+', term_lower) if t]
            if tokens:
                clauses = ' OR '.join(["LOWER(name) LIKE %s" for _ in tokens])
                params = tuple([f"%{t}%" for t in tokens])
                q_tokens = f"SELECT id, name, description, price, stock, stock_minimum FROM products WHERE {clauses} LIMIT 10"
                res = self.execute_query(q_tokens, params)
                if res:
                    return res

            # 4) Fallback: normalize names (remove non-alphanumeric) and match in Python
            all_q = "SELECT id, name, description, price, stock, stock_minimum FROM products"
            all_products = self.execute_query(all_q)
            normalized_term = re.sub(r'[^a-z0-9]', '', term_lower)
            matches = []
            for p in all_products:
                name = (p.get('name') or '')
                name_norm = re.sub(r'[^a-z0-9]', '', name.lower())
                if normalized_term and normalized_term in name_norm:
                    matches.append(p)
            return matches[:10]
        except Exception as e:
            logger.error(f"Error en search_products: {e}")
            return []
    
    async def get_business_stats(self) -> Dict:
        """Obtener estadísticas completas del negocio"""
        try:
            # Productos
            products_query = "SELECT COUNT(*) as total FROM products"
            products_count = self.execute_query(products_query)[0]['total']
            
            # Empleados
            employees_query = "SELECT COUNT(*) as total FROM users WHERE role = 'employee'"
            employees_count = self.execute_query(employees_query)[0]['total']
            
            # Ventas y estadísticas
            sales_stats = await self.get_sales_statistics()
            low_stock = await self.get_low_stock_products(10)
            
            return {
                "products_count": products_count,
                "employees_count": employees_count,
                "low_stock_count": len(low_stock),
                "sales_statistics": sales_stats
            }
        except Exception as e:
            logger.error(f"Error obteniendo estadísticas: {e}")
            raise
    
    def close(self):
        """Cerrar conexión a la base de datos"""
        if self.connection and self.connection.is_connected():
            self.connection.close()
            logger.info("🔒 Conexión a base de datos cerrada")
