const { DataTypes } = require('sequelize');
const sequelize = require('../db');

const Attendance = sequelize.define('Attendance', {
  Id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  admissionNo: { type: DataTypes.STRING(255), allowNull: false },
  classId: { type: DataTypes.STRING(10), allowNull: false },
  classArmId: { type: DataTypes.STRING(10), allowNull: false },
  sessionTermId: { type: DataTypes.STRING(10), allowNull: false },
  status: { type: DataTypes.STRING(10), allowNull: false },
  dateTimeTaken: { type: DataTypes.STRING(20), allowNull: false }
}, { tableName: 'tblattendance', timestamps: false });

module.exports = Attendance;
