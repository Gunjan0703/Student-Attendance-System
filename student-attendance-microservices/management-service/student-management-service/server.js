// server.js - Adapted for existing attendancemsystem database
require('dotenv').config();
const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');

const app = express();
const PORT = process.env.PORT || 3001;

// Database configuration using existing database
const dbConfig = {
    host: process.env.DB_HOST || 'mysql',
    port: process.env.DB_PORT || 3306,
    user: process.env.MYSQL_USER || 'attendance_user',
    password: process.env.MYSQL_PASSWORD || ' attendance_pass',
    database: process.env.MYSQL_NAME || 'attendancemsystem',
    charset: 'utf8mb4',
    acquireTimeout: 60000,
    timeout: 60000,
    reconnect: true
};

// Create connection pool
let pool;
try {
    pool = mysql.createPool(dbConfig);
    console.log('Database pool created successfully');
} catch (error) {
    console.error('Failed to create database pool:', error);
    process.exit(1);
}

// CORS configuration
const allowedOrigins = process.env.ALLOWED_ORIGINS ? 
    process.env.ALLOWED_ORIGINS.split(',') : 
    ['http://localhost', 'http://localhost:80', 'http://student-attendance.local'];

app.use(cors({
    origin: allowedOrigins,
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));

app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Test database connection
app.get('/api/test-db', async (req, res) => {
    try {
        const connection = await pool.getConnection();
        const [tables] = await connection.execute('SHOW TABLES');
        connection.release();
        
        res.json({
            success: true,
            message: 'Database connection successful',
            tables: tables,
            database: dbConfig.database
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Database connection failed',
            error: error.message
        });
    }
});

// Generic function to get table structure
app.get('/api/table-structure/:tableName', async (req, res) => {
    try {
        const { tableName } = req.params;
        const [structure] = await pool.execute(`DESCRIBE ${tableName}`);
        
        res.json({
            success: true,
            table: tableName,
            structure: structure
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: `Error getting structure for table ${req.params.tableName}`,
            error: error.message
        });
    }
});

// Students API - Adaptable to different table structures
// Common table names: students, tblstudents, student
// Common column names: id/StudentId, name/StudentName, email/Email, etc.

// Get all students - you'll need to adjust table and column names
app.get('/api/students', async (req, res) => {
    try {
        // Try common table names and structures
        let query;
        let students = [];
        
        // Common variations - adjust based on your actual table structure
        const possibleQueries = [
            'SELECT * FROM students ORDER BY id DESC',
            'SELECT * FROM tblstudents ORDER BY StudentId DESC',
            'SELECT * FROM student ORDER BY id DESC',
            'SELECT id, name, email, student_id, class_id, phone, address, created_at FROM students ORDER BY id DESC'
        ];
        
        for (let testQuery of possibleQueries) {
            try {
                const [rows] = await pool.execute(testQuery);
                students = rows;
                query = testQuery;
                break;
            } catch (err) {
                // Try next query variation
                continue;
            }
        }
        
        if (students.length >= 0) {
            res.json({
                success: true,
                data: students,
                message: 'Students retrieved successfully',
                query_used: query
            });
        } else {
            throw new Error('No compatible student table structure found');
        }
        
    } catch (error) {
        console.error('Students fetch error:', error);
        res.status(500).json({
            success: false,
            message: 'Error fetching students',
            error: error.message,
            suggestion: 'Check table structure with /api/test-db endpoint'
        });
    }
});

// Get student by ID - adaptable
app.get('/api/students/:id', async (req, res) => {
    try {
        const { id } = req.params;
        
        // Try different table/column combinations
        const possibleQueries = [
            'SELECT * FROM students WHERE id = ?',
            'SELECT * FROM tblstudents WHERE StudentId = ?',
            'SELECT * FROM student WHERE id = ?'
        ];
        
        let student = null;
        for (let query of possibleQueries) {
            try {
                const [rows] = await pool.execute(query, [id]);
                if (rows.length > 0) {
                    student = rows[0];
                    break;
                }
            } catch (err) {
                continue;
            }
        }
        
        if (!student) {
            return res.status(404).json({
                success: false,
                message: 'Student not found'
            });
        }
        
        res.json({
            success: true,
            data: student,
            message: 'Student retrieved successfully'
        });
        
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Error fetching student',
            error: error.message
        });
    }
});

// Create student - you'll need to adjust based on actual table structure
app.post('/api/students', async (req, res) => {
    try {
        // Extract data - adjust field names based on your frontend
        const studentData = req.body;
        
        // Common field mappings - adjust based on your table structure
        const name = studentData.name || studentData.StudentName;
        const email = studentData.email || studentData.Email;
        const student_id = studentData.student_id || studentData.StudentId || studentData.roll_no;
        const class_id = studentData.class_id || studentData.ClassId;
        const phone = studentData.phone || studentData.Phone;
        const address = studentData.address || studentData.Address;
        
        if (!name) {
            return res.status(400).json({
                success: false,
                message: 'Student name is required'
            });
        }
        
        // Try different insert variations
        const possibleQueries = [
            {
                query: 'INSERT INTO students (name, email, student_id, class_id, phone, address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())',
                params: [name, email, student_id, class_id, phone, address]
            },
            {
                query: 'INSERT INTO tblstudents (StudentName, Email, StudentId, ClassId, Phone, Address, CreatedAt) VALUES (?, ?, ?, ?, ?, ?, NOW())',
                params: [name, email, student_id, class_id, phone, address]
            },
            {
                query: 'INSERT INTO students (name, email) VALUES (?, ?)',
                params: [name, email]
            }
        ];
        
        let result = null;
        for (let queryObj of possibleQueries) {
            try {
                [result] = await pool.execute(queryObj.query, queryObj.params);
                break;
            } catch (err) {
                continue;
            }
        }
        
        if (!result) {
            throw new Error('Unable to insert with any query variation');
        }
        
        res.status(201).json({
            success: true,
            data: { 
                id: result.insertId, 
                name, 
                email, 
                student_id,
                ...studentData 
            },
            message: 'Student created successfully'
        });
        
    } catch (error) {
        console.error('Student creation error:', error);
        
        if (error.code === 'ER_DUP_ENTRY') {
            res.status(409).json({
                success: false,
                message: 'Student with this information already exists'
            });
        } else {
            res.status(500).json({
                success: false,
                message: 'Error creating student',
                error: error.message
            });
        }
    }
});

// Update student - adaptable
app.put('/api/students/:id', async (req, res) => {
    try {
        const { id } = req.params;
        const studentData = req.body;
        
        const name = studentData.name || studentData.StudentName;
        const email = studentData.email || studentData.Email;
        const student_id = studentData.student_id || studentData.StudentId;
        const class_id = studentData.class_id || studentData.ClassId;
        const phone = studentData.phone || studentData.Phone;
        const address = studentData.address || studentData.Address;
        
        const possibleQueries = [
            {
                query: 'UPDATE students SET name = ?, email = ?, student_id = ?, class_id = ?, phone = ?, address = ? WHERE id = ?',
                params: [name, email, student_id, class_id, phone, address, id]
            },
            {
                query: 'UPDATE tblstudents SET StudentName = ?, Email = ?, StudentId = ?, ClassId = ?, Phone = ?, Address = ? WHERE StudentId = ?',
                params: [name, email, student_id, class_id, phone, address, id]
            },
            {
                query: 'UPDATE students SET name = ?, email = ? WHERE id = ?',
                params: [name, email, id]
            }
        ];
        
        let result = null;
        for (let queryObj of possibleQueries) {
            try {
                [result] = await pool.execute(queryObj.query, queryObj.params);
                if (result.affectedRows > 0) break;
            } catch (err) {
                continue;
            }
        }
        
        if (!result || result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Student not found or no changes made'
            });
        }
        
        res.json({
            success: true,
            message: 'Student updated successfully'
        });
        
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Error updating student',
            error: error.message
        });
    }
});

// Delete student - adaptable
app.delete('/api/students/:id', async (req, res) => {
    try {
        const { id } = req.params;
        
        const possibleQueries = [
            'DELETE FROM students WHERE id = ?',
            'DELETE FROM tblstudents WHERE StudentId = ?',
            'UPDATE students SET status = "inactive" WHERE id = ?'
        ];
        
        let result = null;
        for (let query of possibleQueries) {
            try {
                [result] = await pool.execute(query, [id]);
                if (result.affectedRows > 0) break;
            } catch (err) {
                continue;
            }
        }
        
        if (!result || result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Student not found'
            });
        }
        
        res.json({
            success: true,
            message: 'Student deleted successfully'
        });
        
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Error deleting student',
            error: error.message
        });
    }
});

// Search students
app.get('/api/students/search/:query', async (req, res) => {
    try {
        const { query } = req.params;
        const searchTerm = `%${query}%`;
        
        const possibleQueries = [
            'SELECT * FROM students WHERE name LIKE ? OR email LIKE ? OR student_id LIKE ?',
            'SELECT * FROM tblstudents WHERE StudentName LIKE ? OR Email LIKE ? OR StudentId LIKE ?'
        ];
        
        let students = [];
        for (let searchQuery of possibleQueries) {
            try {
                const [rows] = await pool.execute(searchQuery, [searchTerm, searchTerm, searchTerm]);
                students = rows;
                break;
            } catch (err) {
                continue;
            }
        }
        
        res.json({
            success: true,
            data: students,
            message: 'Search completed successfully'
        });
        
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Error searching students',
            error: error.message
        });
    }
});

// Classes/Subjects API - if needed
app.get('/api/classes', async (req, res) => {
    try {
        const possibleQueries = [
            'SELECT * FROM classes',
            'SELECT * FROM tblclasses',
            'SELECT * FROM subjects',
            'SELECT * FROM tblsubjects'
        ];
        
        let classes = [];
        for (let query of possibleQueries) {
            try {
                const [rows] = await pool.execute(query);
                classes = rows;
                break;
            } catch (err) {
                continue;
            }
        }
        
        res.json({
            success: true,
            data: classes,
            message: 'Classes retrieved successfully'
        });
        
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Error fetching classes',
            error: error.message
        });
    }
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'OK', 
        service: 'Student Management Service',
        database: dbConfig.database,
        timestamp: new Date().toISOString()
    });
});

// Error handling middleware
app.use((error, req, res, next) => {
    console.error('Unhandled error:', error);
    res.status(500).json({
        success: false,
        message: 'Internal server error',
        error: process.env.NODE_ENV === 'development' ? error.message : 'Something went wrong'
    });
});

// 404 handler
app.use('*', (req, res) => {
    res.status(404).json({
        success: false,
        message: 'Endpoint not found',
        available_endpoints: [
            'GET /health',
            'GET /api/test-db',
            'GET /api/students',
            'GET /api/students/:id',
            'POST /api/students',
            'PUT /api/students/:id',
            'DELETE /api/students/:id',
            'GET /api/students/search/:query',
            'GET /api/classes'
        ]
    });
});

// Start server
const server = app.listen(PORT, '0.0.0.0', () => {
    console.log(`
🚀 Student Management Service Started!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 Server: http://localhost:${PORT}
🗄️  Database: ${dbConfig.database}
🌐 Environment: ${process.env.NODE_ENV || 'development'}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Available endpoints:
• GET  /health - Health check
• GET  /api/test-db - Test database connection
• GET  /api/students - Get all students
• POST /api/students - Create student
• PUT  /api/students/:id - Update student
• DEL  /api/students/:id - Delete student
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    `);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('SIGTERM received, shutting down gracefully');
    server.close(() => {
        console.log('Server closed');
        if (pool) {
            pool.end();
        }
        process.exit(0);
    });
});

module.exports = app;
