FROM node:18.20-alpine

RUN apk add --no-cache dumb-init

WORKDIR /app

COPY package*.json ./

RUN npm ci --only=production && npm cache clean --force

COPY . .

RUN addgroup -g 1001 -S nodejs \
    && adduser -S studentmgmt -u 1001 -G nodejs \
    && chown -R studentmgmt:nodejs /app

USER studentmgmt

EXPOSE 3001

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node healthcheck.js || exit 1

ENTRYPOINT ["dumb-init", "--"]

CMD ["node", "server.js"]
