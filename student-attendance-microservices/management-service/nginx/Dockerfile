FROM nginx:alpine

RUN addgroup -S nginx && adduser -S -G nginx nginx

COPY --chown=nginx:nginx sites-available/default.conf /etc/nginx/conf.d/default.conf

USER nginx

CMD ["nginx", "-g", "daemon off;"]
