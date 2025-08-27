const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const Joi = require('joi');
const sequelize = require('./db'); 
const multer = require('multer');
const path = require('path');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3002;



// Import models
const Student = require('./models/Student')(sequelize);

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());

// File upload configuration
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'uploads/');
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, file.fieldname + '-' + uniqueSuffix + path.extname(file.originalname));
  }
});

const upload = multer({ 
  storage: storage,
  limits: { fileSize: 5 * 1024 * 1024 }, // 5MB limit
  fileFilter: (req, file, cb) => {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (allowedTypes.includes(file.mimetype)) {
      cb(null, true);
    } else {
      cb(new Error('Invalid file type'));
    }
  }
});

// Validation schemas
const studentSchema = Joi.object({
  studentId: Joi.string().required(),
  firstName: Joi.string().min(2).required(),
  lastName: Joi.string().min(2).required(),
  email: Joi.string().email().required(),
  phone: Joi.string().optional(),
  dateOfBirth: Joi.date().optional(),
  gender: Joi.string().valid('male', 'female', 'other').optional(),
  address: Joi.string().optional(),
  parentName: Joi.string().optional(),
  parentPhone: Joi.string().optional(),
  parentEmail: Joi.string().email().optional(),
  userId: Joi.number().integer().required()
});

const updateStudentSchema = Joi.object({
  firstName: Joi.string().min(2).optional(),
  lastName: Joi.string().min(2).optional(),
  email: Joi.string().email().optional(),
  phone: Joi.string().optional(),
  dateOfBirth: Joi.date().optional(),
  gender: Joi.string().valid('male', 'female', 'other').optional(),
  address: Joi.string().optional(),
  parentName: Joi.string().optional(),
  parentPhone: Joi.string().optional(),
  parentEmail: Joi.string().email().optional(),
  isActive: Joi.boolean().optional()
});

// Routes
app.post('/api/students', async (req, res) => {
  try {
    const { error } = studentSchema.validate(req.body);
    if (error) {
      return res.status(400).json({ error: error.details[0].message });
    }

    const student = await Student.create(req.body);
    res.status(201).json({
      message: 'Student created successfully',
      student
    });
  } catch (error) {
    if (error.name === 'SequelizeUniqueConstraintError') {
      return res.status(400).json({ error: 'Student ID or email already exists' });
    }
    console.error('Create student error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

app.get('/api/students', async (req, res) => {
  try {
    const { page = 1, limit = 10, search, isActive = true } = req.query;
    const offset = (page - 1) * limit;

    const whereClause = { isActive };
    if (search) {
      whereClause[Sequelize.Op.or] = [
        { firstName: { [Sequelize.Op.iLike]: `%${search}%` } },
        { lastName: { [Sequelize.Op.iLike]: `%${search}%` } },
        { studentId: { [Sequelize.Op.iLike]: `%${search}%` } },
        { email: { [Sequelize.Op.iLike]: `%${search}%` } }
      ];
    }

    const students = await Student.findAndCountAll({
      where: whereClause,
      limit: parseInt(limit),
      offset: parseInt(offset),
      order: [['createdAt', 'DESC']]
    });

    res.json({
      students: students.rows,
      totalCount: students.count,
      currentPage: parseInt(page),
      totalPages: Math.ceil(students.count / limit)
    });
  } catch (error) {
    console.error('Get students error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

app.get('/api/students/:id', async (req, res) => {
  try {
    const student = await Student.findByPk(req.params.id);
    if (!student) {
      return res.status(404).json({ error: 'Student not found' });
    }
    res.json(student);
  } catch (error) {
    console.error('Get student error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

app.put('/api/students/:id', async (req, res) => {
  try {
    const { error } = updateStudentSchema.validate(req.body);
    if (error) {
      return res.status(400).json({ error: error.details[0].message });
    }

    const student = await Student.findByPk(req.params.id);
    if (!student) {
      return res.status(404).json({ error: 'Student not found' });
    }

    await student.update(req.body);
    res.json({
      message: 'Student updated successfully',
      student
    });
  } catch (error) {
    console.error('Update student error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

app.delete('/api/students/:id', async (req, res) => {
  try {
    const student = await Student.findByPk(req.params.id);
    if (!student) {
      return res.status(404).json({ error: 'Student not found' });
    }

    await student.update({ isActive: false });
    res.json({ message: 'Student deactivated successfully' });
  } catch (error) {
    console.error('Delete student error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

app.post('/api/students/:id/profile-picture', upload.single('profilePicture'), async (req, res) => {
  try {
    const student = await Student.findByPk(req.params.id);
    if (!student) {
      return res.status(404).json({ error: 'Student not found' });
    }

    if (!req.file) {
      return res.status(400).json({ error: 'No file uploaded' });
    }

    await student.update({ profilePicture: req.file.filename });
    res.json({
      message: 'Profile picture updated successfully',
      filename: req.file.filename
    });
  } catch (error) {
    console.error('Upload profile picture error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Health check
app.get('/health', (req, res) => {
  res.json({ 
    service: 'Student Management Service', 
    status: 'running',
    timestamp: new Date().toISOString() 
  });
});

// Initialize database and start server
const initDatabase = async () => {
  try {
    await sequelize.authenticate();
    console.log('Database connected successfully');
    await sequelize.sync({ force: false });
    console.log('Database synchronized');
  } catch (error) {
    console.error('Database initialization error:', error);
    process.exit(1);
  }
};

initDatabase().then(() => {
  app.listen(PORT, () => {
    console.log(`Student Management Service running on port ${PORT}`);
  });
});
