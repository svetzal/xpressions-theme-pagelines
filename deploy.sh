#!/bin/bash

TARGET="root@commonsroot.com"
AUTH="-i /Users/svetzal/.ssh/twm-aws.pem"

deploy()
{
  LOCATION=$1

  scp -r $AUTH * $TARGET:$LOCATION
  ssh $AUTH $TARGET "chown -R xpressions:psacln $LOCATION"
  ssh $AUTH $TARGET "rm -f $LOCATION/deploy.sh $LOCATION/push.sh"
}

echo "Deploying to production..."
deploy "/var/www/vhosts/xpressions.org/httpdocs/wp-content/themes/xpressions-theme"

echo "Deploying to staging..."
deploy "/var/www/vhosts/xpressions.org/subdomains/new/httpdocs/wp-content/themes/xpressions-theme"

