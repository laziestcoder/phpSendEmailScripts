#!/bin/bash


bash db_backup.sh

# Verify if the backup was successful
if [ $? -eq 0 ]; then
  php send_email.php
else
  exit 1
fi

exit 0
