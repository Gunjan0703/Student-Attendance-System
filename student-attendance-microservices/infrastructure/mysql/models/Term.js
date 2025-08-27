const { DataTypes } = require('sequelize');
const sequelize = require('../db');

const Term = sequelize.define('Term', {
  Id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  termName: { type: DataTypes.STRING(20), allowNull: false }
}, { tableName: 'tblterm', timestamps: false });

module.exports = Term;

