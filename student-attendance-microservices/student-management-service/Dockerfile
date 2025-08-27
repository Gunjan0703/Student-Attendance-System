FROM node:18-alpine

# Install dumb-init
RUN apk add --no-cache dumb-init

WORKDIR /app

# Create non-root user
RUN addgroup -g 1001 -S nodejs && adduser -S studentmgmt -u 1001

# Copy package files
COPY package*.json ./
RUN npm install --production && npm cache clean --force


# Copy source code
COPY . .

# Set proper permissions
RUN chown -R studentmgmt:nodejs /app
USER studentmgmt

EXPOSE 3002

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node healthcheck.js

ENTRYPOINT ["dumb-init", "--"]
CMD ["node", "server.js"]
