#!/bin/bash

TARGET="root@commonsroot.com"
LOCATION="/var/www/vhosts/xpressions.org/httpdocs/wp-content/themes/xpressions-theme"
#LOCATION="/var/www/vhosts/xpressions.org/subdomains/new/httpdocs/wp-content/themes/xpressions-theme"
AUTH="-i /Users/svetzal/.ssh/twm-aws.pem"

scp -r $AUTH * $TARGET:$LOCATION
ssh $AUTH $TARGET "chown -R xpressions:psacln $LOCATION"
ssh $AUTH $TARGET "rm -f $LOCATION/deploy.sh $LOCATION/push.sh"
