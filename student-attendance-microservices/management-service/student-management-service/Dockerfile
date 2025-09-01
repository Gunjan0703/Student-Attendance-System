# =========================
# Stage 1: Build
# =========================
FROM node:18.20-alpine AS build

# Set working directory
WORKDIR /app

# Copy package files first for caching
COPY package*.json ./

# Install production dependencies
# Use npm install instead of npm ci to avoid lockfile issues
RUN npm ci --only=production && npm cache clean --force

# Copy application source code
COPY . .

# =========================
# Stage 2: Production
# =========================
FROM node:18.20-alpine

# Install dumb-init for proper signal handling
RUN apk add --no-cache dumb-init

# Create non-root user and group
RUN addgroup -g 1001 -S nodejs \
    && adduser -S studentmgmt -u 1001 -G nodejs

# Set working directory
WORKDIR /app

# Copy built application from build stage
COPY --from=build /app /app

# Set permissions
RUN chown -R studentmgmt:nodejs /app

# Switch to non-root user
USER studentmgmt

# Expose application port
EXPOSE 3001

# Optional healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
  CMD node /app/healthcheck.js

# Use dumb-init to handle PID 1 correctly
ENTRYPOINT ["dumb-init", "--"]

# Start the application
CMD ["node", "server.js"]

