# Getting started
## A] Drupal

### 1) Install Docker
Docker needs to be installed to run Drupal. If docker is already installed, skip to step 2. <br>
If docker is not installed, there are steps to install Docker in Ubuntu OS:
https://docs.docker.com/engine/install/ubuntu/

### 2) Setup .env
Get project with git clone and use 2 .env_template in PEP-CCMP and PEP-CCMP/drupal to set .env for the project
        
### 3) Setup composer

    cd composer 
    chmod o+x docker-entrypoint.sh  # set execute permission before building
    sudo docker build -t composer .
    cd ..

### 4) Setup drupal

    cd drupal
    chmod o+w ./web/sites/default     # set write permission before setup
    sudo docker run --rm --interactive --tty --volume $PWD:/app composer:latest update  
    cd .. 

### 5) Initial-run Drupal

    sudo docker compose up -d   # to run Drupal application 
    sudo docker compose up      # to run with debug prints

Starting up will take a few seconds. Eventually, connect to set URL in .env for Drupal. <br>
Note: on starting up, if there are no drupal-data folder, manually make a new folder and start again

## B] Deleting Drupal
To do a complete reset of Drupal, use commands:

    sudo chmod o+w drupal-data
    sudo rm -r drupal-data/*
    rmdir drupal-data

After removing Drupal container and volumes, you can rerun a clean container of Drupal

    sudo docker compose up -d   # to run Drupal application again
