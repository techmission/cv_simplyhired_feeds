#!/bin/bash

SCRIPT_PATH = /home/techmi5/public_html/gospelpedia/simplyhired-feed
SCRIPT = cv-simplyhired-cli.php
DELETE_SCRIPT = cv-simplyhired-delete.php

# First, delete what is currently stored.
php $SCRIPT_PATH/$DELETE_SCRIPT

# Request for the same 100-mile radius zipcode points used for the AllForGood feed
# (see saved Excel spreadsheet of these)
# See distribution at http://batchgeo.com/map/589ea74668cbbf7a7d10ffa6d274d14d

php $SCRIPT_PATH/$SCRIPT 03846                       #  Jackson, NH (03846)
php $SCRIPT_PATH/$SCRIPT 06281                       #  Woodstock, CT (06281)
php $SCRIPT_PATH/$SCRIPT 13420                       #  Old Forge, NY (13420)
php $SCRIPT_PATH/$SCRIPT 14744                       #  Houghton, NY (14744)
php $SCRIPT_PATH/$SCRIPT 17228                       #  Harrisonville, PA (17228)
php $SCRIPT_PATH/$SCRIPT 18360                       #  Stroudsburg, PA (18360)
php $SCRIPT_PATH/$SCRIPT 19968                       #  Milton, DE (19968)
php $SCRIPT_PATH/$SCRIPT 23833                       #  Church Rd, VA (23833)
php $SCRIPT_PATH/$SCRIPT 25268                       #  Orma, WV (25268)
php $SCRIPT_PATH/$SCRIPT 28585                       #  Trenton, NC (28585)
php $SCRIPT_PATH/$SCRIPT 28634                       #  Harmony, NC (28634)
php $SCRIPT_PATH/$SCRIPT 29056                       #  Greeleyville, SC (29056)
php $SCRIPT_PATH/$SCRIPT 30240                       #  La Grange, GA (30240)
php $SCRIPT_PATH/$SCRIPT 30624                       #  Bowman, GA (30624)
php $SCRIPT_PATH/$SCRIPT 31563                       #  Surrency, GA (31563)
php $SCRIPT_PATH/$SCRIPT 32442                       #  Grand Ridge, FL (32442)
php $SCRIPT_PATH/$SCRIPT 33440                       #  Clewiston, FL (33440)
php $SCRIPT_PATH/$SCRIPT 34481                       #  Ocala, FL (34481)
php $SCRIPT_PATH/$SCRIPT 36524                       #  Coffeeville, AL (36524)
php $SCRIPT_PATH/$SCRIPT 37083                       #  Lafayette, TN (37083)
php $SCRIPT_PATH/$SCRIPT 38301                       #  Jackson, TN (38301)
php $SCRIPT_PATH/$SCRIPT 38774                       #  Shelby, MS (38774)
php $SCRIPT_PATH/$SCRIPT 41017                       #  Fort Mitchell, KY (41017)
php $SCRIPT_PATH/$SCRIPT 44450                       #  North Bloomfield, OH (44450)
php $SCRIPT_PATH/$SCRIPT 45840                       #  Findlay, OH (45840)
php $SCRIPT_PATH/$SCRIPT 48415                       #  Birch Run, MI (48415)
php $SCRIPT_PATH/$SCRIPT 49684                       #  Traverse City, MI (49684)
php $SCRIPT_PATH/$SCRIPT 50060                       #  Corydon, IA (50060)
php $SCRIPT_PATH/$SCRIPT 50401                       #  Mason City, IA (50401)
php $SCRIPT_PATH/$SCRIPT 52031                       #  Bellevue, IA (52031)
php $SCRIPT_PATH/$SCRIPT 54473                       #  Rosholt, WI (54473)
php $SCRIPT_PATH/$SCRIPT 54893                       #  Webster, WI (54893)
php $SCRIPT_PATH/$SCRIPT 56241                       #  Granite Falls, MN (56241)
php $SCRIPT_PATH/$SCRIPT 56639                       #  Effie, MN (56639)
php $SCRIPT_PATH/$SCRIPT 57544                       #  Kennebec, SD (57544)
php $SCRIPT_PATH/$SCRIPT 57769                       #  Piedmont, SD (57769)
php $SCRIPT_PATH/$SCRIPT 58420                       #  Buchanan, ND (58420)
php $SCRIPT_PATH/$SCRIPT 58632                       #  Little Missouri National Grassland, Golva, ND (58632)
php $SCRIPT_PATH/$SCRIPT 59064                       #  Pompeys Pillar, Mt (59064)
php $SCRIPT_PATH/$SCRIPT 59501                       #  Havre, Mt (59501)
php $SCRIPT_PATH/$SCRIPT 59635                       #  East Helena, Mt (59635)
php $SCRIPT_PATH/$SCRIPT 59864                       #  Ronan, Mt (59864)
php $SCRIPT_PATH/$SCRIPT 60091                       #  Wilmette, IL (60091)
php $SCRIPT_PATH/$SCRIPT 61944                       #  Paris, IL (61944)
php $SCRIPT_PATH/$SCRIPT 62346                       #  La Prairie, IL (62346)
php $SCRIPT_PATH/$SCRIPT 63755                       #  Jackson, MO (63755)
php $SCRIPT_PATH/$SCRIPT 64730                       #  Butler, MO (64730)
php $SCRIPT_PATH/$SCRIPT 66850                       #  Elmdale, KS (66850)
php $SCRIPT_PATH/$SCRIPT 67650                       #  Morland, KS (67650)
php $SCRIPT_PATH/$SCRIPT 68028                       #  Gretna, NE (68028)
php $SCRIPT_PATH/$SCRIPT 69120                       #  Arnold, NE (69120)
php $SCRIPT_PATH/$SCRIPT 69301                       #  Alliance, NE (69301)
php $SCRIPT_PATH/$SCRIPT 70401                       #  Hammond, LA (70401)
php $SCRIPT_PATH/$SCRIPT 70665                       #  Sulphur, LA (70665)
php $SCRIPT_PATH/$SCRIPT 71270                       #  Ruston, LA (71270)
php $SCRIPT_PATH/$SCRIPT 72459                       #  Ravenden, AR (72459)
php $SCRIPT_PATH/$SCRIPT 72827                       #  Ouachita National Forest, Bluffton,  AR (72827)
php $SCRIPT_PATH/$SCRIPT 73533                       #  Duncan, OK (73533)
php $SCRIPT_PATH/$SCRIPT 73834                       #  Buffalo, OK (73834)
php $SCRIPT_PATH/$SCRIPT 75127                       #  Fruitvale, TX (75127)
php $SCRIPT_PATH/$SCRIPT 76446                       #  Dublin, TX (76446)
php $SCRIPT_PATH/$SCRIPT 78055                       #  Medina, TX (78055)
php $SCRIPT_PATH/$SCRIPT 78332                       #  Alice, TX (78332)
php $SCRIPT_PATH/$SCRIPT 78933                       #  Cat Spring, TX (78933)
php $SCRIPT_PATH/$SCRIPT 79088                       #  Tulia, TX (79088)
php $SCRIPT_PATH/$SCRIPT 79734                       #  Fort Davis, TX (79734)
php $SCRIPT_PATH/$SCRIPT 79739                       #  Garden City, TX (79739)
php $SCRIPT_PATH/$SCRIPT 80480                       #  Walden, CO (80480)
php $SCRIPT_PATH/$SCRIPT 80743                       #  Otis, CO (80743)
php $SCRIPT_PATH/$SCRIPT 81052                       #  Lamar, CO (81052)
php $SCRIPT_PATH/$SCRIPT 81525                       #  Mack, CO (81525)
php $SCRIPT_PATH/$SCRIPT 82639                       #  Kaycee, WY (82639)
php $SCRIPT_PATH/$SCRIPT 83686                       #  Nampa, ID (83686)
php $SCRIPT_PATH/$SCRIPT 84029                       #  Grantsville, UT (84029)
php $SCRIPT_PATH/$SCRIPT 84754                       #  Monroe, UT (84754)
php $SCRIPT_PATH/$SCRIPT 85333                       #  Wellton, AZ (85333)
php $SCRIPT_PATH/$SCRIPT 85629                       #  Sahuarita, AZ (85629)
php $SCRIPT_PATH/$SCRIPT 85901                       #  Sitgreaves National Forest, Show Low,  AZ (85901)
php $SCRIPT_PATH/$SCRIPT 86305                       #  Prescott, AZ (86305)
php $SCRIPT_PATH/$SCRIPT 86535                       #  Dennehotso, AZ (86535)
php $SCRIPT_PATH/$SCRIPT 87034                       #  Acoma Pueblo, NM (87034)
php $SCRIPT_PATH/$SCRIPT 88350                       #  Lincoln National Forest,  NM (88350)
php $SCRIPT_PATH/$SCRIPT 88421                       #  Garita, NM (88421)
php $SCRIPT_PATH/$SCRIPT 89060                       #  Pahrump, NV (89060)
php $SCRIPT_PATH/$SCRIPT 89415                       #  Hawthorne, NV (89415)
php $SCRIPT_PATH/$SCRIPT 89801                       #  Elko, NV (89801)
php $SCRIPT_PATH/$SCRIPT 90011                       #  Los Angeles, CA (90011)
php $SCRIPT_PATH/$SCRIPT 93215                       #  Delano, CA (93215)
php $SCRIPT_PATH/$SCRIPT 95360                       #  Newman, CA (95360)
php $SCRIPT_PATH/$SCRIPT 95428                       #  Mendocino National Forest, Colvelo, CA (95428)
php $SCRIPT_PATH/$SCRIPT 96741                       #  Kalaheo, HI (96741)
php $SCRIPT_PATH/$SCRIPT 96778                       #  Pahoa, HI (96778)
php $SCRIPT_PATH/$SCRIPT 97051                       #  St Helens, OR (97051)
php $SCRIPT_PATH/$SCRIPT 97497                       #  Wolf Creek, OR (97497)
php $SCRIPT_PATH/$SCRIPT 97758                       #  Riley, OR (97758)
php $SCRIPT_PATH/$SCRIPT 98283                       #  Ross Lake National Recreation Area,  WA (98283)
php $SCRIPT_PATH/$SCRIPT 99337                       #  Kennewick, WA (99337)
php $SCRIPT_PATH/$SCRIPT 99506                       #  Anchorage, AK (99506)
php $SCRIPT_PATH/$SCRIPT 99901                       #  Tongass National Forest, Ketchikan, AK (99901)
php $SCRIPT_PATH/$SCRIPT 4426                        # Dover-Foxcroft, ME (4426)
php $SCRIPT_PATH/$SCRIPT 83320                       #  Carey, ID (83320)
php $SCRIPT_PATH/$SCRIPT 99180                       #  Kaniksu National Forest, Usk, WA (99180)
php $SCRIPT_PATH/$SCRIPT 99829                       #  Tongass National Forest, Hoonah-Angoon, AK (99829)
php $SCRIPT_PATH/$SCRIPT 93562                       #  Searles Valley, CA (93562)
php $SCRIPT_PATH/$SCRIPT 89301                       #  Ely, NV (89301)
php $SCRIPT_PATH/$SCRIPT 99701                       #  Fairbanks North Star, AK (99701)
php $SCRIPT_PATH/$SCRIPT 83467                       #  Salmon, ID (83467)
php $SCRIPT_PATH/$SCRIPT 99762                       #  Nome, AK (99762)
# Tried to put in the North Slope of Alaska, but there were no zipcodes up there.
# Probably not any Christian jobs either :)

# English-speaking countries (non-US)

# Canadian provinces (http://en.wikipedia.org/wiki/Provinces_and_territories_of_Canada)

php $SCRIPT_PATH/$SCRIPT AB -f:en-ca                # Alberta
php $SCRIPT_PATH/$SCRIPT BC -f:en-ca                # British Columbia
php $SCRIPT_PATH/$SCRIPT MB -f:en-ca                # Manitoba
php $SCRIPT_PATH/$SCRIPT NB -f:en-ca                # New Brunswick
php $SCRIPT_PATH/$SCRIPT NL -f:en-ca                # Newfoundland and Labrador
php $SCRIPT_PATH/$SCRIPT NS -f:en-ca                # Nova Scotia
php $SCRIPT_PATH/$SCRIPT NT -f:en-ca                # Northwest Territories
php $SCRIPT_PATH/$SCRIPT NU -f:en-ca                # Nunavut
php $SCRIPT_PATH/$SCRIPT ON -f:en-ca                # Ontario
php $SCRIPT_PATH/$SCRIPT PE -f:en-ca                # Prince Edward Island
php $SCRIPT_PATH/$SCRIPT QC -f:en-ca                # Quebec
php $SCRIPT_PATH/$SCRIPT SK -f:en-ca                # Saskatchewan
php $SCRIPT_PATH/$SCRIPT YT -f:en-ca                # Yukon

# United Kingdom countries
# (administrative regions were too complex to use
# - cf. http://www.geonames.org/GB/administrative-division-united-kingdom.html)
# (could go by their "region names" or counties, but that is the full name
# - still complex to figure out what that should be) 
php $SCRIPT_PATH/$SCRIPT England -f:en-gb           # England
php $SCRIPT_PATH/$SCRIPT Scotland -f:en-gb          # Scotland
php $SCRIPT_PATH/$SCRIPT Craigavon -f:en-gb         # Near center of Northern Ireland

# Major cities of Ireland
php $SCRIPT_PATH/$SCRIPT Galway -f:en-ie
php $SCRIPT_PATH/$SCRIPT Dublin -f:en-ie
php $SCRIPT_PATH/$SCRIPT Limerick -f:en-ie
php $SCRIPT_PATH/$SCRIPT Cork -f:en-ie

# Australian provinces (http://www.citypopulation.de/Australia-UC.html)

php $SCRIPT_PATH/$SCRIPT ACT -f:en-au               # Austrialian Capital Territory
php $SCRIPT_PATH/$SCRIPT CHR -f:en-ca               # Christmas Island
php $SCRIPT_PATH/$SCRIPT COC -f:en-ca               # Cocos Islands
php $SCRIPT_PATH/$SCRIPT JB -f:en-ca                # Jervis Bay
php $SCRIPT_PATH/$SCRIPT NSW -f:en-ca               # New South Wales
php $SCRIPT_PATH/$SCRIPT NT -f:en-ca                # Northern Territory
php $SCRIPT_PATH/$SCRIPT QLD -f:en-ca               # Queensland
php $SCRIPT_PATH/$SCRIPT SA -f:en-ca                # South Australia
php $SCRIPT_PATH/$SCRIPT TAS -f:en-ca               # Tasmania
php $SCRIPT_PATH/$SCRIPT VIC -f:en-ca               # Victoria
php $SCRIPT_PATH/$SCRIPT WA -f:en-ca                # Western Australia

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
