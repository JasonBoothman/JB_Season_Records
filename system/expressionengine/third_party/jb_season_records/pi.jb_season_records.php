<?php

	$plugin_info = array(
	    'pi_name'         => 'JB Season Records',
	    'pi_version'      => '1.0',
	    'pi_author'       => 'Jason Boothman',
	    'pi_author_url'   => 'https://github.com/jdboothman/',
	    'pi_description'  => 'Return a sports season record.',
	    'pi_usage'        => Jb_season_records::usage()
	);
	
	class Jb_season_records
	{
	
	    public $return_data = "";
	
	    public function Jb_season_records() {
		    $this->EE =& get_instance();
			
			//Make sure required parameters are populated before proceeding
			if ($this->EE->TMPL->fetch_param('begin_date') != FALSE AND $this->EE->TMPL->fetch_param('end_date') != FALSE AND $this->EE->TMPL->fetch_param('my_score') != FALSE AND $this->EE->TMPL->fetch_param('opp_score') != FALSE AND $this->EE->TMPL->fetch_param('category') != FALSE)
			{
				$this->Get_Data();
			}
		}
		
		public function Get_Data() {
			
			//Get passed parameters
			$begin_date = $this->EE->TMPL->fetch_param('begin_date');
			$end_date = $this->EE->TMPL->fetch_param('end_date');
			$event_category = $this->EE->TMPL->fetch_param('category');
			$my_team = $this->EE->TMPL->fetch_param('my_score');
			$my_team_dh = $this->EE->TMPL->fetch_param('my_dh_score');
			$opp_team = $this->EE->TMPL->fetch_param('opp_score');
			$opp_team_dh = $this->EE->TMPL->fetch_param('opp_dh_score');
			$conf = $this->EE->TMPL->fetch_param('conference');
			
			//Begin creating sql
			$sql = 'SELECT DISTINCT
						cd.field_id_' . $my_team . ' AS "MyScore"
						,cd.field_id_' . $opp_team . ' AS "OppScore"';
			
			//Get double header data if the variables are set.
			
			if ($this->EE->TMPL->fetch_param('my_dh_score') != FALSE AND $this->EE->TMPL->fetch_param('opp_dh_score') != FALSE)
			{
				$sql .= ',cd.field_id_' . $my_team_dh . ' AS "MyDhScore"';
				$sql .= ',cd.field_id_' . $opp_team_dh . ' AS "OppDhScore"';
			}
			
			//Get conference game data if the variable is set.
			if ($this->EE->TMPL->fetch_param('conf_field') != FALSE)
			{
				$sql .= ',cd.field_id_' . $conf . ' AS "IsConferenceGame"';
			}
			
			//Finish building sql / conditions
			$sql .= 'FROM
						exp_calendar_events ce
					INNER JOIN
						exp_category_posts cp ON cp.entry_id = ce.entry_id
					INNER JOIN
						exp_channel_titles ct ON ct.entry_id = ce.entry_id
					INNER JOIN
						exp_channel_data cd ON cd.entry_id = ce.entry_id
					WHERE
						ce.start_date >= "' . $begin_date . '"
						AND
						ce.end_date <= "' . $end_date .'"
						AND
						cp.cat_id = "' . $event_category . '"
						AND
						ct.status = "open"
						AND
						cd.field_id_' . $my_team;
			
			//Query the db
			$query = ee()->db->query($sql);
			
			//If data is returned, lets figure out the records!
			if ($query->num_rows() > 0)
			{
				$wins = 0;
				$losses = 0;
				$ties = 0;
				$confwins = 0;
				$conflosses = 0;
				$confties = 0;
				$percent = 0;
				$confpercent = 0;
				
				foreach($query->result_array() as $row)
				{
					// ----------------------------------------
					//  STANDARD GAMES
					// ----------------------------------------
					
					//Increment a win
					if ($row['MyScore'] > $row['OppScore'])
					{
						$wins++;
						
						//Increment a conference win
						if ($this->EE->TMPL->fetch_param('conf_field') != FALSE AND $row['IsConferenceGame'] == "Yes")
						{
							$confwins++;
						}
					}
					
					//Increment a loss
					if ($row['MyScore'] < $row['OppScore'])
					{
						$losses++;
						
						//Increment a conference loss
						if ($this->EE->TMPL->fetch_param('conf_field') != FALSE AND $row['IsConferenceGame'] == "Yes")
						{
							$conflosses++;
						}
					}
					
					//Increment a tie
					if ($row['MyScore'] == $row['OppScore'])
					{
						$ties++;
						
						//Increment a conference tie
						if ($this->EE->TMPL->fetch_param('conf_field') != FALSE AND $row['IsConferenceGame'] == "Yes")
						{
							$confties++;
						}
					}
					
					// ----------------------------------------
					//  DOUBLEHEADERS
					// ----------------------------------------
					
					if ($this->EE->TMPL->fetch_param('my_dh_score') != FALSE AND $this->EE->TMPL->fetch_param('opp_dh_score') != FALSE AND ($row['MyDhScore'] != "" OR $row['OppDhScore'] != ""))
					{
						//Increment a win
						if ($row['MyDhScore'] > $row['OppDhScore'])
						{
							$wins++;
							
							//Increment a conference win
							if ($this->EE->TMPL->fetch_param('conf_field') != FALSE AND $row['IsConferenceGame'] == "Yes")
							{
								$confwins++;
							}
						}
						
						//Increment a loss
						if ($row['MyDhScore'] < $row['OppDhScore'])
						{
							$losses++;
							
							//Increment a standard conference win
							if ($this->EE->TMPL->fetch_param('conf_field') != FALSE AND $row['IsConferenceGame'] == "Yes")
							{
								$conflosses++;
							}
						}
						
						//Increment a tie
						if ($row['MyDhScore'] == $row['OppDhScore'])
						{
							$ties++;
							
							//Increment a standard conference win
							if ($this->EE->TMPL->fetch_param('conf_field') != FALSE AND $row['IsConferenceGame'] == "Yes")
							{
								$confTies++;
							}
						}
					}
				}
				
				//If there are records, calculate the win percentage
				if (($wins + $ties + $losses) > 0)
				{
					$percent = ($wins + ($ties * .5)) / ($wins + $ties + $losses);
					$percent = ltrim(round($percent, 3), '0');
				}
				
				//If there are conference records, calculate the conference win percentage
				if (($confwins + $confties + $conflosses) > 0)
				{
					$confpercent = ($confwins + ($confties * .5)) / ($confwins + $confties + $conflosses);
					$confpercent = ltrim(round($confpercent, 3), '0');
				}
				
				//Add data to the array
				$data[] = array(
					"wins" => $wins,
					"losses" =>$losses,
					"ties" => $ties,
					"confwins" => $confwins,
					"conflosses" => $conflosses,
					"confties" => $confties,
					"percent" => $percent,
					"confpercent" => $confpercent
				);
				
				//Return variables so you can access them in the template
				$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $data);
			}
	    }
	    
	    // ----------------------------------------
		//  Plugin Usage
		// ----------------------------------------
		 
		function usage()
		{
		   ob_start(); ?>
		   
		JB Season Records is a plugin that works with the Solspace Calendar add-on to make season calculations for sports.
		You specify a timeframe, category and certain field ids and it will return the season records (wins, losses, ties, etc.).
		
		------------------------------------------------
		PARAMETERS
		------------------------------------------------
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
		
		
		------------------------------------------------
		VARIABLES
		------------------------------------------------
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
		
		
		------------------------------------------------
		EXAMPLE
		------------------------------------------------
		{exp:jb_season_records begin_date="20140801" end_date="20150701" category="12" my_score="122" opp_score="123"}
		
	    Wins = {wins}
		Losses = {losses}
		Win Percentage = {percent}
		
		{/exp:jb_season_records}
		
		------------------------------------------------
		FINDING FIELD IDS
		------------------------------------------------
		If you're not sure what the field id numbers are, there is a very simple way to do so.
		
		1. Navigate to the Channel Fields administration screen.
		2. Click on the 'Events' group name (assuming you haven't changed it when setting up Solspace Calendar)
		3. Click the field label to edit it.
		4. Look at the url for "?field_id=" and the number that follows should be the field id number.
		
		<?php
		   $buffer         = ob_get_contents();
		   ob_end_clean(); 
		 
		   return $buffer;
		}
	}
/* End of file pi.jb_season_records.php */
/* Location: ./system/expressionengine/third_party/jb_season_records/pi.jb_season_records.php */
