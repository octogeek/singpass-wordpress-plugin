docker image build -t jwt_parser .
docker run -p 5000:5000 -d jwt_parser

if you running docker compose 
-------------------------------------
docker-compose up