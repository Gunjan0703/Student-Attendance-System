const { DataTypes } = require('sequelize');
const sequelize = require('../db');

const Admin = sequelize.define('Admin', {
  Id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  firstName: { type: DataTypes.STRING(50), allowNull: false },
  lastName: { type: DataTypes.STRING(50) },
  emailAddress: { type: DataTypes.STRING(50), allowNull: false },
  password: { type: DataTypes.STRING(50), allowNull: false }
}, { tableName: 'tbladmin', timestamps: false });

module.exports = Admin;
