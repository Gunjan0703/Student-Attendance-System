require('dotenv').config();
const { Sequelize } = require('sequelize');

const sequelize = new Sequelize(process.env.MYSQL_DATABASE || 'attendancemsystem', process.env.MYSQL_USER || 'attendance_user', process.env.MYSQL_PASSWORD || 'attendance_pass', {
  host: process.env.DB_HOST || 'mysql',
  port: process.env.DB_PORT || 3306,
  dialect: 'mysql',
  logging: false,
});

module.exports = sequelize;
