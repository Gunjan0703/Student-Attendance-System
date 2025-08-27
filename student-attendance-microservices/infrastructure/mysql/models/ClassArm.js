const { DataTypes } = require('sequelize');
const sequelize = require('../db');

const ClassArm = sequelize.define('ClassArm', {
  Id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  classId: { type: DataTypes.STRING(10), allowNull: false },
  classArmName: { type: DataTypes.STRING(255), allowNull: false },
  isAssigned: { type: DataTypes.STRING(10), allowNull: false }
}, { tableName: 'tblclassarms', timestamps: false });

module.exports = ClassArm;

