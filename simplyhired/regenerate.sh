#!/bin/bash

# Define shorthand names for the scripts so they can be called more efficiently.
SCRIPT_PATH=`dirname $0`
SCRIPT=insert.php
DELETE_SCRIPT=delete.php
GEOCODE_SCRIPT=geocode.php

PHP=/usr/local/bin/php

# Step 1: Delete what is currently stored.
$PHP $SCRIPT_PATH/$DELETE_SCRIPT

# Step 2: Request new jobs.

# Step 2a: US jobs -
# Request for the same 100-mile radius US zipcode points used for the AllForGood feed
# (see saved Excel spreadsheet of these)
# See distribution at http://batchgeo.com/map/589ea74668cbbf7a7d10ffa6d274d14d
# For initial results, see http://batchgeo.com/map/8ef41c18859e44e3f8944082ef8b9c67

$PHP $SCRIPT_PATH/$SCRIPT 03846                       #  Jackson, NH (03846)            - 24, 24
$PHP $SCRIPT_PATH/$SCRIPT 06281                       #  Woodstock, CT (06281)          - 360, 155
$PHP $SCRIPT_PATH/$SCRIPT 13420                       #  Old Forge, NY (13420)          - 33, 27
$PHP $SCRIPT_PATH/$SCRIPT 14744                       #  Houghton, NY (14744)           - 23, 19
$PHP $SCRIPT_PATH/$SCRIPT 17228                       #  Harrisonville, PA (17228)      - 433, 233
$PHP $SCRIPT_PATH/$SCRIPT 18360                       #  Stroudsburg, PA (18360)        - 400, 165
$PHP $SCRIPT_PATH/$SCRIPT 19968                       #  Milton, DE (19968)             - 376, 28
$PHP $SCRIPT_PATH/$SCRIPT 23832                       #  Chesterfield, VA (23833)          - 302, 101
$PHP $SCRIPT_PATH/$SCRIPT 25268                       #  Orma, WV (25268)               - 43, 40
$PHP $SCRIPT_PATH/$SCRIPT 28585                       #  Trenton, NC (28585)            - 42, 20
$PHP $SCRIPT_PATH/$SCRIPT 28634                       #  Harmony, NC (28634)            - 334, 122
$PHP $SCRIPT_PATH/$SCRIPT 29056                       #  Greeleyville, SC (29056)       - 56, 28
$PHP $SCRIPT_PATH/$SCRIPT 30240                       #  La Grange, GA (30240)          - 302, 104
$PHP $SCRIPT_PATH/$SCRIPT 30624                       #  Bowman, GA (30624)             - 304, 11
$PHP $SCRIPT_PATH/$SCRIPT 31563                       #  Surrency, GA (31563)           - 18, 16
$PHP $SCRIPT_PATH/$SCRIPT 32442                       #  Grand Ridge, FL (32442)        - 9, 8
$PHP $SCRIPT_PATH/$SCRIPT 33440                       #  Clewiston, FL (33440)          - 61, 61
$PHP $SCRIPT_PATH/$SCRIPT 34481                       #  Ocala, FL (34481)              - 302, 94
$PHP $SCRIPT_PATH/$SCRIPT 36524                       #  Coffeeville, AL (36524)        - 13, 12
$PHP $SCRIPT_PATH/$SCRIPT 37083                       #  Lafayette, TN (37083)          - 60, 60
$PHP $SCRIPT_PATH/$SCRIPT 38301                       #  Jackson, TN (38301)            - 360, 158
$PHP $SCRIPT_PATH/$SCRIPT 38774                       #  Shelby, MS (38774)             - 467, 151
$PHP $SCRIPT_PATH/$SCRIPT 41017                       #  Fort Mitchell, KY (41017)      - 322, 105
$PHP $SCRIPT_PATH/$SCRIPT 44450                       #  North Bloomfield, OH (44450)   - 78, 65
$PHP $SCRIPT_PATH/$SCRIPT 45840                       #  Findlay, OH (45840)            - 85, 46
$PHP $SCRIPT_PATH/$SCRIPT 48415                       #  Birch Run, MI (48415)          - 61, 31
$PHP $SCRIPT_PATH/$SCRIPT 49684                       #  Traverse City, MI (49684)      - 13, 8      
$PHP $SCRIPT_PATH/$SCRIPT 50060                       #  Corydon, IA (50060)            - 338, 140
$PHP $SCRIPT_PATH/$SCRIPT 50401                       #  Mason City, IA (50401)         - 82, 68
$PHP $SCRIPT_PATH/$SCRIPT 52031                       #  Bellevue, IA (52031)           - 38, 33
$PHP $SCRIPT_PATH/$SCRIPT 54473                       #  Rosholt, WI (54473)            - 70, 63
$PHP $SCRIPT_PATH/$SCRIPT 54893                       #  Webster, WI (54893)            - 343, 136
$PHP $SCRIPT_PATH/$SCRIPT 56241                       #  Granite Falls, MN (56241)      - 58, 43
$PHP $SCRIPT_PATH/$SCRIPT 56639                       #  Effie, MN (56639)              - 17, 17
$PHP $SCRIPT_PATH/$SCRIPT 57544                       #  Kennebec, SD (57544)           - 17, 17
$PHP $SCRIPT_PATH/$SCRIPT 57769                       #  Piedmont, SD (57769)           - 7, 7
$PHP $SCRIPT_PATH/$SCRIPT 58420                       #  Buchanan, ND (58420)           - 49, 49
$PHP $SCRIPT_PATH/$SCRIPT 58632                       #  Golva, ND (58632)              - 1, 1
$PHP $SCRIPT_PATH/$SCRIPT 59064                       #  Pompeys Pillar, Mt (59064)     - 14, 14
$PHP $SCRIPT_PATH/$SCRIPT 59501                       #  Havre, Mt (59501)              - 2, 2
$PHP $SCRIPT_PATH/$SCRIPT 59635                       #  East Helena, Mt (59635)        - 3, 2
$PHP $SCRIPT_PATH/$SCRIPT 59864                       #  Ronan, Mt (59864)              - 3, 3
$PHP $SCRIPT_PATH/$SCRIPT 60091                       #  Wilmette, IL (60091)           - 361, 144
$PHP $SCRIPT_PATH/$SCRIPT 61944                       #  Paris, IL (61944)              - 32, 18
$PHP $SCRIPT_PATH/$SCRIPT 62346                       #  La Prairie, IL (62346)         - 29, 8
$PHP $SCRIPT_PATH/$SCRIPT 63755                       #  Jackson, MO (63755)            - 56, 51
$PHP $SCRIPT_PATH/$SCRIPT 64730                       #  Butler, MO (64730)             - 37, 37
$PHP $SCRIPT_PATH/$SCRIPT 66850                       #  Elmdale, KS (66850)            - 27, 21
$PHP $SCRIPT_PATH/$SCRIPT 67650                       #  Morland, KS (67650)            - 7, 7
$PHP $SCRIPT_PATH/$SCRIPT 68028                       #  Gretna, NE (68028)             - 47, 44
$PHP $SCRIPT_PATH/$SCRIPT 69120                       #  Arnold, NE (69120)             - 13, 11
$PHP $SCRIPT_PATH/$SCRIPT 69301                       #  Alliance, NE (69301)           - 1, 1
$PHP $SCRIPT_PATH/$SCRIPT 70401                       #  Hammond, LA (70401)            - 18, 12
$PHP $SCRIPT_PATH/$SCRIPT 70665                       #  Sulphur, LA (70665)            - 8, 6
$PHP $SCRIPT_PATH/$SCRIPT 71270                       #  Ruston, LA (71270)             - 13, 7
$PHP $SCRIPT_PATH/$SCRIPT 72459                       #  Ravenden, AR (72459)           - 373, 40
$PHP $SCRIPT_PATH/$SCRIPT 72827                       #  Bluffton,  AR (72827)          - 354, 42
$PHP $SCRIPT_PATH/$SCRIPT 73533                       #  Duncan, OK (73533)             - 53, 53
$PHP $SCRIPT_PATH/$SCRIPT 73834                       #  Buffalo, OK (73834)            - 8, 3
$PHP $SCRIPT_PATH/$SCRIPT 75127                       #  Fruitvale, TX (75127)          - 632, 424
$PHP $SCRIPT_PATH/$SCRIPT 76446                       #  Dublin, TX (76446)             - 404, 12
$PHP $SCRIPT_PATH/$SCRIPT 78055                       #  Medina, TX (78055)             - 88, 88
$PHP $SCRIPT_PATH/$SCRIPT 78332                       #  Alice, TX (78332)              - 9, 8
$PHP $SCRIPT_PATH/$SCRIPT 78933                       #  Cat Spring, TX (78933)         - 64, 50
$PHP $SCRIPT_PATH/$SCRIPT 79088                       #  Tulia, TX (79088)              - 7, 7
$PHP $SCRIPT_PATH/$SCRIPT 79734                       #  Fort Davis, TX (79734)         - 18, 18
$PHP $SCRIPT_PATH/$SCRIPT 79739                       #  Garden City, TX (79739)        - 18, 18
$PHP $SCRIPT_PATH/$SCRIPT 80480                       #  Walden, CO (80480)             - 338, 140
$PHP $SCRIPT_PATH/$SCRIPT 80743                       #  Otis, CO (80743)               - 27, 21
$PHP $SCRIPT_PATH/$SCRIPT 81052                       #  Lamar, CO (81052)              - 7, 2
$PHP $SCRIPT_PATH/$SCRIPT 81525                       #  Mack, CO (81525)               - 1, 1
$PHP $SCRIPT_PATH/$SCRIPT 82639                       #  Kaycee, WY (82639)             - 4, 3
$PHP $SCRIPT_PATH/$SCRIPT 83686                       #  Nampa, ID (83686)              - 12, 12
$PHP $SCRIPT_PATH/$SCRIPT 84029                       #  Grantsville, UT (84029)        - 8, 8
$PHP $SCRIPT_PATH/$SCRIPT 84754                       #  Monroe, UT (84754)             - 1, 0
$PHP $SCRIPT_PATH/$SCRIPT 85333                       #  Wellton, AZ (85333)            - 16, 16
$PHP $SCRIPT_PATH/$SCRIPT 85629                       #  Sahuarita, AZ (85629)          - 12, 9
$PHP $SCRIPT_PATH/$SCRIPT 85901                       #  Show Low,  AZ (85901)          - 5, 3
$PHP $SCRIPT_PATH/$SCRIPT 86305                       #  Prescott, AZ (86305)           - 18, 1
$PHP $SCRIPT_PATH/$SCRIPT 86535                       #  Dennehotso, AZ (86535)         - 2, 1
$PHP $SCRIPT_PATH/$SCRIPT 87034                       #  Acoma Pueblo, NM (87034)       - 692, 493
$PHP $SCRIPT_PATH/$SCRIPT 88350                       #  Lincoln N.F.,  NM (88350)      - 39, 29
$PHP $SCRIPT_PATH/$SCRIPT 88421                       #  Garita, NM (88421)             - 77, 62
$PHP $SCRIPT_PATH/$SCRIPT 89060                       #  Pahrump, NV (89060)            - 10, 10
$PHP $SCRIPT_PATH/$SCRIPT 89415                       #  Hawthorne, NV (89415)          - 9, 9
$PHP $SCRIPT_PATH/$SCRIPT 89801                       #  Elko, NV (89801)               - 2, 2
$PHP $SCRIPT_PATH/$SCRIPT 90011                       #  Los Angeles, CA (90011)        - 325, 126
$PHP $SCRIPT_PATH/$SCRIPT 93215                       #  Delano, CA (93215)             - 7, 6
$PHP $SCRIPT_PATH/$SCRIPT 95360                       #  Newman, CA (95360)             - 314, 113
$PHP $SCRIPT_PATH/$SCRIPT 95428                       #  Colvelo, CA (95428)            - 19, 7
$PHP $SCRIPT_PATH/$SCRIPT 96741                       #  Kalaheo, HI (96741)            - 2, 2
$PHP $SCRIPT_PATH/$SCRIPT Honolulu                    #  Honolulu, HI                   - 69, x
$PHP $SCRIPT_PATH/$SCRIPT 97051                       #  St Helens, OR (97051)          - 342, 144
$PHP $SCRIPT_PATH/$SCRIPT 97497                       #  Wolf Creek, OR (97497)         - 9, 9
$PHP $SCRIPT_PATH/$SCRIPT 97758                       #  Riley, OR (97758)              - 5, 5
$PHP $SCRIPT_PATH/$SCRIPT 98283                       #  Ross Lake N.R.A,  WA (98283)   - 308, 34
$PHP $SCRIPT_PATH/$SCRIPT 99337                       #  Kennewick, WA (99337)          - 9, 8
$PHP $SCRIPT_PATH/$SCRIPT 99506                       #  Anchorage, AK (99506)          - 7, 7
$PHP $SCRIPT_PATH/$SCRIPT 99901                       #  Ketchikan, AK (99901)          - 21, 21
$PHP $SCRIPT_PATH/$SCRIPT 4426                        #  Dover-Foxcroft, ME (04426)     - 0, 0
$PHP $SCRIPT_PATH/$SCRIPT 83320                       #  Carey, ID (83320)              - 4, 4
$PHP $SCRIPT_PATH/$SCRIPT 99180                       #  Usk, WA (99180)                - 6, 5
$PHP $SCRIPT_PATH/$SCRIPT 99829                       #  Hoonah-Angoon, AK (99829)      - 2, 2
$PHP $SCRIPT_PATH/$SCRIPT 93562                       #  Searles Valley, CA (93562)     - 8, 5
$PHP $SCRIPT_PATH/$SCRIPT 89301                       #  Ely, NV (89301)                - 0, 0
$PHP $SCRIPT_PATH/$SCRIPT 99701                       #  Fairbanks N. Star, AK (99701)  - 2, 2
$PHP $SCRIPT_PATH/$SCRIPT 83467                       #  Salmon, ID (83467)             - 5, 4
$PHP $SCRIPT_PATH/$SCRIPT 99762                       #  Nome, AK (99762)               - 0, 0
# Tried to put in the North Slope of Alaska, but there were no zipcodes up there.
# Probably not any Christian jobs either :)

## English-speaking countries (non-US) ##

# Step 2b -
# Canadian provinces (http://en.wikipedia.org/wiki/Provinces_and_territories_of_Canada)

# $PHP $SCRIPT_PATH/$SCRIPT AB -f:en-ca                # Alberta                          - 410, 211
# $PHP $SCRIPT_PATH/$SCRIPT BC -f:en-ca                # British Columbia                 - 337, 140
# $PHP $SCRIPT_PATH/$SCRIPT MB -f:en-ca                # Manitoba                         - 37, 37
# $PHP $SCRIPT_PATH/$SCRIPT NB -f:en-ca                # New Brunswick                    - 13, 10
# $PHP $SCRIPT_PATH/$SCRIPT NL -f:en-ca                # Newfoundland and Labrador        - 10, 7
# $PHP $SCRIPT_PATH/$SCRIPT NS -f:en-ca                # Nova Scotia                      - 21, 17
# $PHP $SCRIPT_PATH/$SCRIPT NT -f:en-ca                # Northwest Territories            - 6, 6
# $PHP $SCRIPT_PATH/$SCRIPT NU -f:en-ca                # Nunavut                          - 0, 0
# $PHP $SCRIPT_PATH/$SCRIPT ON -f:en-ca                # Ontario                          - 849, 661
# $PHP $SCRIPT_PATH/$SCRIPT PE -f:en-ca                # Prince Edward Island             - 0, 0
# $PHP $SCRIPT_PATH/$SCRIPT QC -f:en-ca                # Quebec                           - 306, 127
# $PHP $SCRIPT_PATH/$SCRIPT SK -f:en-ca                # Saskatchewan                     - 437, 233
# $PHP $SCRIPT_PATH/$SCRIPT YT -f:en-ca                # Yukon                            - 5, 5

# Step 2c -
# United Kingdom countries
# (administrative regions were too complex to use
# - cf. http://www.geonames.org/GB/administrative-division-united-kingdom.html)
# (could go by their "region names" or counties, but that is the full name
# - still complex to figure out what that should be) 

$PHP $SCRIPT_PATH/$SCRIPT England -f:en-gb           # England                          - 991, 792
$PHP $SCRIPT_PATH/$SCRIPT Scotland -f:en-gb          # Scotland                         - 460, 262
# $PHP $SCRIPT_PATH/$SCRIPT Craigavon -f:en-gb         # Center of Northern Ireland       - 438, 169

# Step 2d -
# Major cities of Ireland

# $PHP $SCRIPT_PATH/$SCRIPT Galway -f:en-ie            #                                  - 4, x
# $PHP $SCRIPT_PATH/$SCRIPT Dublin -f:en-ie            #                                  - 46, x
# $PHP $SCRIPT_PATH/$SCRIPT Limerick -f:en-ie          #                                  - 43, x
# $PHP $SCRIPT_PATH/$SCRIPT Cork -f:en-ie              #                                  - 5, x

# Step 2e -
# Australian provinces (http://www.citypopulation.de/Australia-UC.html)

# $PHP $SCRIPT_PATH/$SCRIPT ACT -f:en-au               # Australian Capital Territory     - 72, 72
# $PHP $SCRIPT_PATH/$SCRIPT CHR -f:en-au               # Christmas Island                 - 0, 0
# $PHP $SCRIPT_PATH/$SCRIPT COC -f:en-au               # Cocos Islands                    - 0, 0
# $PHP $SCRIPT_PATH/$SCRIPT JB -f:en-au                # Jervis Bay                       - 0, 0
# $PHP $SCRIPT_PATH/$SCRIPT NSW -f:en-au               # New South Wales                  - 351, x
# $PHP $SCRIPT_PATH/$SCRIPT NT -f:en-au                # Northern Territory               - 38, x
# $PHP $SCRIPT_PATH/$SCRIPT QLD -f:en-au               # Queensland                       - 288, x
# $PHP $SCRIPT_PATH/$SCRIPT SA -f:en-au                # South Australia                  - 46, x
# $PHP $SCRIPT_PATH/$SCRIPT TAS -f:en-au               # Tasmania                         - 18, x
# $PHP $SCRIPT_PATH/$SCRIPT VIC -f:en-au               # Victoria                         - 150, x
# $PHP $SCRIPT_PATH/$SCRIPT WA -f:en-au                # Western Australia                - 44, x

# Step 3: Geocode the newly-added items.
# Note, this will not get through all the geocoding.
# The rest will need to be geocoded via separate calls to this script on the cron tab.
# Typically, 4 calls separated by 10 mins or so should do it.

$PHP $SCRIPT_PATH/$GEOCODE_SCRIPT

## LEGACY ##

# Non-English-speaking countries
# These are not working with the country name or ISO code, so commented out for now.
# Also, listings were not in English.

# php $SCRIPT_PATH/$SCRIPT Argentina -f:en-ar         # Argentina              
# php $SCRIPT_PATH/$SCRIPT Austria -f:en-at           # Austria                    
# php $SCRIPT_PATH/$SCRIPT Belgium -f:en-be           # Belgium                  
# php $SCRIPT_PATH/$SCRIPT Brazil -f:en-br            # Brazil
# php $SCRIPT_PATH/$SCRIPT China -f:en-cn             # China
# php $SCRIPT_PATH/$SCRIPT France -f:en-fr            # France
# php $SCRIPT_PATH/$SCRIPT Germany -f:en-de           # Germany
# php $SCRIPT_PATH/$SCRIPT India -f:en-in             # India
# php $SCRIPT_PATH/$SCRIPT Italy -f:en-it             # Italy
# php $SCRIPT_PATH/$SCRIPT Japan -f:en-jp             # Japan
# php $SCRIPT_PATH/$SCRIPT Korea -f:en-kr             # Korea
# php $SCRIPT_PATH/$SCRIPT Mexico -f:en-mx            # Mexico
# php $SCRIPT_PATH/$SCRIPT Netherlands -f:en-nl       # Netherlands
# php $SCRIPT_PATH/$SCRIPT Portugal -f:en-pt          # Portugal
# php $SCRIPT_PATH/$SCRIPT Russia -f:en-ru            # Russia
# php $SCRIPT_PATH/$SCRIPT South Africa -f:en-za      # South Africa
# php $SCRIPT_PATH/$SCRIPT Spain -f:en-es             # Spain
# php $SCRIPT_PATH/$SCRIPT Sweden -f:en-se            # Sweden
# php $SCRIPT_PATH/$SCRIPT Switzerland -f:en-ch       # Switzerland
