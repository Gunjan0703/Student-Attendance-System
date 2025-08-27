const { DataTypes } = require('sequelize');
const sequelize = require('../db');

const ClassTeacher = sequelize.define('ClassTeacher', {
  Id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  firstName: { type: DataTypes.STRING(255), allowNull: false },
  lastName: { type: DataTypes.STRING(255), allowNull: false },
  emailAddress: { type: DataTypes.STRING(255), allowNull: false },
  password: { type: DataTypes.STRING(255), allowNull: false },
  phoneNo: { type: DataTypes.STRING(50), allowNull: false },
  classId: { type: DataTypes.STRING(10), allowNull: false },
  classArmId: { type: DataTypes.STRING(10), allowNull: false },
  dateCreated: { type: DataTypes.STRING(50), allowNull: false }
}, { tableName: 'tblclassteacher', timestamps: false });

module.exports = ClassTeacher;

