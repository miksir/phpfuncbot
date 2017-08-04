#!/usr/bin/env bash

SCRIPT=$1
TAGS=$(grep -Pv '^#' ~/project/vagrantup/ansible_tags.conf | awk -vORS=, '{print $1}' | sed -r 's/,+$//')
SKIPTAG=""

if [ "$USER" != "vagrant" ]; then
    SKIPTAG="--skip-tags=dev"
fi

PYTHONUNBUFFERED=1 /usr/bin/ansible-playbook ~/project/vagrantup/$SCRIPT.yml --tags=$TAGS $SKIPTAG
