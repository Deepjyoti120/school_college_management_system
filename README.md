## Introduction
Deepjyoti APP
docker build --platform=linux/amd64 -t cement .
docker run --platform=linux/amd64 -p 8080:80 --env-file .env cement
docker run --platform=linux/amd64 -p 8080:80 cement
docker exec -it cement bash // for bash

<!-- New -->
docker build --platform=linux/amd64 -t cement .
docker run -p 8080:80 --env-file .env cement
<!-- HUb Push -->
docker tag cement deepjyoti/cement:latest
docker push deepjyoti/cement:latest
