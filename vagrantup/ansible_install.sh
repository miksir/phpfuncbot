#!/usr/bin/env bash

sudo -n true >/dev/null 2>&1

if [ $? -ne 0 ]; then
    echo "************************************************************************"
    echo "*** SUDO required for vagrant user with NOPASSWD: permission         ***"
    echo "*** Please, install sudo and add to /etc/sudoers the following       ***"
    echo "*** $USER ALL=(ALL) NOPASSWD:ALL                                     ***"
    echo "***                                                                  ***"
    echo "*** Guest machine still running, you can fix sudo now and run        ***"
    echo "*** provision command: vagrant provision                             ***"
    echo "************************************************************************"
    exit 1
fi

if [ ! -e /usr/bin/ansible ]; then
    echo Installing ansible...
    sudo sh -c "echo deb http://ftp.debian.org/debian jessie-backports main >>/etc/apt/sources.list"
    export DEBIAN_FRONTEND=noninteractive
    sudo apt-get -qq -y update
    sudo apt-get -qq -t jessie-backports -y install ansible >/dev/null
    sudo sh -c "echo localhost ansible_connection=local >>/etc/ansible/hosts"
else
    sudo apt-get -qq -y update
    sudo apt-get -qq -t jessie-backports -y install ansible >/dev/null
fi

# temp directory for ansible must be on same mount as templates
if [ ! -d ~/project/vagrantup/ansible/tmp ]; then
    mkdir -p ~/project/vagrantup/ansible/tmp
fi

cat > ~/.ansible.cfg <<EOF
[defaults]
remote_tmp = ~/project/vagrantup/ansible/tmp
log_path = ~/project/vagrantup/ansible/ansible.log
EOF
