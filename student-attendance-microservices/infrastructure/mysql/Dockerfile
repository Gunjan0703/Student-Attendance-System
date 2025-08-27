FROM mysql:8.0

# Set default env values (can be overridden in docker-compose)
ENV MYSQL_ROOT_PASSWORD=rootpassword
ENV MYSQL_DATABASE=attendancemsystem
ENV MYSQL_USER=attendance_user
ENV MYSQL_PASSWORD=attendance_pass

# Copy initial schema + data into image
COPY attendancemsystem.sql /docker-entrypoint-initdb.d/

EXPOSE 3306
