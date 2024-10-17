#!/bin/bash

echo "maintenance enable"
php bin/magento maintenance:enable

echo "Removing pub/static/adminhtml..."
rm -rf pub/static/adminhtml/

echo "Removing pub/static/adminhtml..."
rm -rf pub/static/adminhtml/

echo "Removing pub/static/frontend..."
rm -rf pub/static/frontend/

echo "removing deployed_version"
rm pub/static/deployed_version.txt

echo "Removing var/cache/ var/generation/ var/page_cache/ var/view_preprocessed/"
rm -rf var/cache/ var/generation/ var/page_cache/ var/view_preprocessed/

echo "Setup:upgrade..."
php bin/magento setup:upgrade

echo "di:compile..."
php bin/magento setup:di:compile

echo "static-content:deploy with params..."
php bin/magento setup:static-content:deploy -f

echo "maintenance disable"
php bin/magento maintenance:disable
