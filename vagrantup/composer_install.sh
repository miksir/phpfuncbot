#!/usr/bin/env bash

mkdir ~/bin 2>/dev/null

cd ~/bin && \
  wget https://getcomposer.org/installer && \
  php installer -- --install-dir=$HOME/bin --filename=composer && \
  chmod 755 composer

rm installer 2>/dev/null
