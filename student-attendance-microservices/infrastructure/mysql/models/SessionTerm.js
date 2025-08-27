const { DataTypes } = require('sequelize');
const sequelize = require('../db');

const SessionTerm = sequelize.define('SessionTerm', {
  Id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  sessionName: { type: DataTypes.STRING(50), allowNull: false },
  termId: { type: DataTypes.STRING(50), allowNull: false },
  isActive: { type: DataTypes.STRING(10), allowNull: false },
  dateCreated: { type: DataTypes.STRING(50), allowNull: false }
}, { tableName: 'tblsessionterm', timestamps: false });

module.exports = SessionTerm;
