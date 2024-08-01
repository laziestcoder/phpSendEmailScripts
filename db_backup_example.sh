#!/bin/bash

# Configuration
DB_NAME=""
DB_USER=""
DB_PASSWORD=""
BACKUP_DIR="./"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_backup_$DATE.sql.gz"

# Ensure the backup directory exists
# mkdir -p "$BACKUP_DIR"

# remove old backup files
rm -rf ${DB_NAME}_backup_*.sql.gz

# Perform the backup and compress it using gzip
mysqldump -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" | gzip > "$BACKUP_FILE"

# Verify if the backup was successful
if [ $? -eq 0 ]; then
  echo "Backup successful: $BACKUP_FILE"
else
  echo "Backup failed!"
  exit 1
fi

# Optionally, you can add cleanup logic here to remove old backups
# Example: Delete backups older than 7 days
find "$BACKUP_DIR" -type f -name "*.sql.gz" -mtime +7 -exec rm {} \;

exit 0
