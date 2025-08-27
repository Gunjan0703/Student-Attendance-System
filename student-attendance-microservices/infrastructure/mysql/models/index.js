const Admin = require('./Admin');
const Attendance = require('./Attendance');
const Class = require('./Class');
const ClassArm = require('./ClassArm');
const ClassTeacher = require('./ClassTeacher');
const SessionTerm = require('./SessionTerm');
const Student = require('./Student');
const Term = require('./Term');

// Relations (basic examples — adjust as needed)
Class.hasMany(ClassArm, { foreignKey: 'classId' });
ClassArm.belongsTo(Class, { foreignKey: 'classId' });

Class.hasMany(ClassTeacher, { foreignKey: 'classId' });
ClassTeacher.belongsTo(Class, { foreignKey: 'classId' });

Student.belongsTo(Class, { foreignKey: 'classId' });
Student.belongsTo(ClassArm, { foreignKey: 'classArmId' });

Attendance.belongsTo(Student, { foreignKey: 'admissionNo', targetKey: 'admissionNumber' });
Student.hasMany(Attendance, { foreignKey: 'admissionNo', sourceKey: 'admissionNumber' });

SessionTerm.hasMany(Attendance, { foreignKey: 'sessionTermId' });
Attendance.belongsTo(SessionTerm, { foreignKey: 'sessionTermId' });

module.exports = {
  Admin,
  Attendance,
  Class,
  ClassArm,
  ClassTeacher,
  SessionTerm,
  Student,
  Term
};

