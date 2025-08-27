const { DataTypes } = require('sequelize');
const sequelize = require('../db');

const Class = sequelize.define('Class', {
  Id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  className: { type: DataTypes.STRING(255), allowNull: false }
}, { tableName: 'tblclass', timestamps: false });

module.exports = Class;

