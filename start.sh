#!/bin/bash

if [ ! -f "docker-compose.yml" ]; then
  printf 'YAML file not found; execute script in root directory\n'
  exit 1
fi
if [ ! -x "$(command -v docker-compose)" ]; then
  printf 'Download and install Docker with docker-compose\n'
  exit 1
fi

toLower() {
	local string="${1}"
	echo "${string}" | tr '[:upper:]' '[:lower:]'
}

FORCE_BUILD=no
NUMBER_OF_ARGUMENTS=$#
LOOP_COUNTER=0
if [ ${NUMBER_OF_ARGUMENTS} -gt 0 ]
then
	for ((LOOP_COUNTER=0; LOOP_COUNTER < NUMBER_OF_ARGUMENTS; LOOP_COUNTER++))
	do
		#Get the current argument
		CURRENT_ARGUMENT=$(toLower ${1})
		shift

		if [ "${CURRENT_ARGUMENT}" = '-forcebuild' ]
		then
			FORCE_BUILD=yes
		fi
	done
fi

docker-compose down
if [ $? -ne 0 ]; then
  printf 'Unable to terminate Docker service for Expensify\n'
  exit 1
fi
if [ "${FORCE_BUILD}" = 'yes' ]; then
  docker-compose build
  if [ $? -ne 0 ]; then
    printf 'Unable to build Docker service for Expensify\n'
    exit 1
  fi
fi
docker-compose up --remove-orphans -d
if [ $? -ne 0 ]; then
  printf 'Unable to start Docker service for Expensify\n'
  exit 1
fi
docker exec php-fpm sh -c 'composer install'
if [ $? -ne 0 ]; then
  printf 'Unable to install Expensify PHP bindings and other required dependencies\n'
  exit 1
fi

printf 'Open browser and navigate to http://localhost:32765\n'
