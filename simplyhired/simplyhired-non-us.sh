#!/bin/bash

SCRIPT_PATH=/home/techmi5/public_html/gospelpedia/simplyhired-feed
SCRIPT=cv-simplyhired-cli.php
DELETE_SCRIPT=cv-simplyhired-delete.php
GEOCODE_SCRIPT=cv-simplyhired-geocode.php

# English-speaking countries (non-US)

# Canadian provinces (http://en.wikipedia.org/wiki/Provinces_and_territories_of_Canada)
# Result distribution is at: 

php $SCRIPT_PATH/$SCRIPT AB -f:en-ca                # Alberta                          - 410, 211
php $SCRIPT_PATH/$SCRIPT BC -f:en-ca                # British Columbia                 - 337, 140
php $SCRIPT_PATH/$SCRIPT MB -f:en-ca                # Manitoba                         - 37, 37
php $SCRIPT_PATH/$SCRIPT NB -f:en-ca                # New Brunswick                    - 13, 10
php $SCRIPT_PATH/$SCRIPT NL -f:en-ca                # Newfoundland and Labrador        - 10, 7
php $SCRIPT_PATH/$SCRIPT NS -f:en-ca                # Nova Scotia                      - 21, 17
php $SCRIPT_PATH/$SCRIPT NT -f:en-ca                # Northwest Territories            - 6, 6
php $SCRIPT_PATH/$SCRIPT NU -f:en-ca                # Nunavut                          - 0, 0
php $SCRIPT_PATH/$SCRIPT ON -f:en-ca                # Ontario                          - 849, 661
php $SCRIPT_PATH/$SCRIPT PE -f:en-ca                # Prince Edward Island             - 0, 0
php $SCRIPT_PATH/$SCRIPT QC -f:en-ca                # Quebec                           - 306, 127
php $SCRIPT_PATH/$SCRIPT SK -f:en-ca                # Saskatchewan                     - 437, 233
php $SCRIPT_PATH/$SCRIPT YT -f:en-ca                # Yukon                            - 5, 5

# United Kingdom countries
# (administrative regions were too complex to use
# - cf. http://www.geonames.org/GB/administrative-division-united-kingdom.html)
# (could go by their "region names" or counties, but that is the full name
# - still complex to figure out what that should be) 
php $SCRIPT_PATH/$SCRIPT England -f:en-gb           # England                          - 991, 792
php $SCRIPT_PATH/$SCRIPT Scotland -f:en-gb          # Scotland                         - 460, 262
php $SCRIPT_PATH/$SCRIPT Craigavon -f:en-gb         # Center of Northern Ireland       - 438, 169

# Major cities of Ireland
php $SCRIPT_PATH/$SCRIPT Galway -f:en-ie            #                                  - 4, x
php $SCRIPT_PATH/$SCRIPT Dublin -f:en-ie            #                                  - 46, x
php $SCRIPT_PATH/$SCRIPT Limerick -f:en-ie          #                                  - 43, x
php $SCRIPT_PATH/$SCRIPT Cork -f:en-ie              #                                  - 5, x

# Australian provinces (http://www.citypopulation.de/Australia-UC.html)

php $SCRIPT_PATH/$SCRIPT ACT -f:en-au               # Australian Capital Territory     - 72, 72
php $SCRIPT_PATH/$SCRIPT CHR -f:en-au               # Christmas Island                 - 0, 0
php $SCRIPT_PATH/$SCRIPT COC -f:en-au               # Cocos Islands                    - 0, 0
php $SCRIPT_PATH/$SCRIPT JB -f:en-au                # Jervis Bay                       - 0, 0
php $SCRIPT_PATH/$SCRIPT NSW -f:en-au               # New South Wales                  - 351, x
php $SCRIPT_PATH/$SCRIPT NT -f:en-au                # Northern Territory               - 38, x
php $SCRIPT_PATH/$SCRIPT QLD -f:en-au               # Queensland                       - 288, x
php $SCRIPT_PATH/$SCRIPT SA -f:en-au                # South Australia                  - 46, x
php $SCRIPT_PATH/$SCRIPT TAS -f:en-au               # Tasmania                         - 18, x
php $SCRIPT_PATH/$SCRIPT VIC -f:en-au               # Victoria                         - 150, x
php $SCRIPT_PATH/$SCRIPT WA -f:en-au                # Western Australia                - 44, x

# Finally, geocode the newly-added items.
php $SCRIPT_PATH/$GEOCODE_SCRIPT