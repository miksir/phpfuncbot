#!/usr/bin/env bash

# temp directory for ansible must be on same mount as templates
if [ ! -d ~/project/vagrantup/ansible/tmp ]; then
    mkdir -p ~/project/vagrantup/ansible/tmp
fi

if [ -e ~/project/vagrantup/ansible/tmp/test ]; then
    rm ~/project/vagrantup/ansible/tmp/test >/dev/null 2>&1
fi

ln -s ~/project/vagrantup/provisioning.xml ~/project/vagrantup/ansible/tmp/test >/dev/null 2>&1

if [ -h ~/project/vagrantup/ansible/tmp/test ]; then
    rm ~/project/vagrantup/ansible/tmp/test >/dev/null 2>&1
    exit 0
else
    echo "************************************************************************"
    echo "*** Symbol links disabled! Did you forgot run as Administrator?      ***"
    echo "***                                                                  ***"
    echo "*** Using windows? Read this:                                        ***"
    echo "*** If your Windows user are privileged (usually you are), run       ***"
    echo "*** vagrant up as administrator. Or turn off UAC at all.             ***"
    echo "*** Also, you can create non-privileged user (not belong to group    ***"
    echo "*** Administrators), allow symlinks to this user and switch to this  ***"
    echo "*** account). Ask google for more :)                                 ***"
    echo "***                                                                  ***"
    echo "*** You can remove this check in Vagrantfile                         ***"
    echo "***                                                                  ***"
    echo "*** Note: guest machine still running, use vagrant halt for stop     ***"
    echo "************************************************************************"
fi
