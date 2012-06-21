#!/bin/bash

SCRIPT_PATH=/home/techmi5/public_html/gospelpedia/feed_importers/meet_the_need
DELETE_SCRIPT=delete.php
INSERT_SCRIPT=insert.php

cd $SCRIPT_PATH

php $SCRIPT_PATH/$DELETE_SCRIPT
php $SCRIPT_PATH/$INSERT_SCRIPT
