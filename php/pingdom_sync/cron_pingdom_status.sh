#!/bin/sh

#####################################################
# Author: Aleksandar Vucetic (aleksandar.vucetic@troxo.com) #
#####################################################

# Define constants

PINGDOM_STATUS_SYNC_PATH=`dirname "$0"`

CRON_PHP_FILE=$PINGDOM_STATUS_SYNC_PATH/cron_pingdom_status_sync_all.php
LOCK_FILE=$PINGDOM_STATUS_SYNC_PATH/cron_pingdom_status_sync_state.lock

s_running()
{
	if [ -f $LOCK_FILE ]
	then
		exit 1
	else
		touch $LOCK_FILE
	fi
}

s_stopping()
{
	rm -f $LOCK_FILE
}

# Exit script if another instance running, create lock file if not
s_running

# Execute PHP script
php -d memory_limit=512M $CRON_PHP_FILE > /dev/null 2>&1

# Remove lock file
s_stopping
exit 0
