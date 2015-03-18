# JB Season Records
JB Season Records is a plugin that works with the Solspace Calendar add-on to make season calculations for sports.
You specify a timeframe, category and certain field ids and it will return the season records (wins, losses, ties, etc.).

**PARAMETERS**
---
begin_date - Required.
Format must be YYYYMMDD.

end_date - Required.
Format must be YYYYMMDD.

category - Required.
Provide the events category_id the sport is located in.

my_score - Required.
Provide the field id number where you enter your scores.

opp_score - Required.
Provide the field id number where you enter the opponents scores.

my_dh_score - Optional.
Provide the field id number where you enter your doubleheader scores (assuming they are not a separate calendar entry).

opp_dh_score - Optional.
Provide the field id number where you enter your opponents doubleheader scores (assuming they are not a separate calendar entry).

conference - Optional.
Provide the field id number to track conference records. Field must provide "Yes" if it's a conference game.


**VARIABLES**
{wins}
Number of wins.

{ties}
Number of ties.

{losses}
Number of losses.

{confwins}
Number of conference wins.

{confties}
Number of conference ties.

{conflosses}
Number of conference losses.

{percent}
Win percentage for all games.

{confpercent}
Win percentage for all conference games.


**EXAMPLE**
{exp:jb_season_records begin_date="20140801" end_date="20150701" category="12" my_score="122" opp_score="123"}
  Wins = {wins}
  Losses = {losses}
  Win Percentage = {percent}
{/exp:jb_season_records}


**FINDING FIELD IDS**
1. Navigate to the Channel Fields administration screen.
2. Click on the 'Events' group name (assuming you haven't changed it when setting up Solspace Calendar)
3. Click the field label to edit it.
4. Look at the url for "?field_id=" and the number that follows should be the field id number.
