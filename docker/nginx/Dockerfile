FROM nginx:alpine

# Copy nginx configuration
COPY docker/nginx/conf.d/app.conf /etc/nginx/conf.d/default.conf

# Copy backend public directory for static assets and index.php
COPY backend/public /var/www/public

WORKDIR /var/www
